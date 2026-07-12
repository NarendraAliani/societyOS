<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Flat;
use App\Models\MaintenanceBill;
use App\Models\MaintenanceHeadRate;
use App\Models\ParkingAllocation;
use App\Models\ParkingRate;
use App\Models\Payment;
use App\Models\Receipt;

final class BillingService
{
    /**
     * Generates one bill per flat for the given period: active maintenance heads (per the
     * rate effective for this period) plus a line item per currently-allocated parking slot
     * (per the parking rate effective for this period, if one's been configured for that
     * slot type). A flat with nothing to charge (no head rates, no priced parking) is
     * skipped entirely rather than getting a pointless ₹0 bill.
     * Flats that already have a bill for this exact period are skipped (no duplicates).
     *
     * @return array{created:int, skipped:int, noCharge:int}
     */
    public static function generateForPeriod(
        int $societyId,
        int $financialYearId,
        string $periodStart,
        string $periodEnd,
        string $dueDate
    ): array {
        $pdo = db();

        $headsStmt = $pdo->prepare('SELECT * FROM maintenance_heads WHERE society_id = :sid AND is_active = 1');
        $headsStmt->execute(['sid' => $societyId]);
        $heads = $headsStmt->fetchAll();

        $flats = Flat::allForSociety($societyId);

        $created = 0;
        $skipped = 0;
        $noCharge = 0;

        $pdo->beginTransaction();
        try {
            foreach ($flats as $flat) {
                if (MaintenanceBill::existsForFlatAndPeriod((int) $flat['id'], $periodStart, $periodEnd)) {
                    $skipped++;
                    continue;
                }

                $items = [];
                $total = 0.0;

                foreach ($heads as $head) {
                    // The rate effective for this bill's period, not "today" — lets a rate
                    // change be scheduled in advance and still bill correctly for past periods.
                    $rate = MaintenanceHeadRate::amountAsOf((int) $head['id'], $periodStart);
                    if ($rate === null) {
                        continue; // no rate has taken effect yet as of this period — don't bill it
                    }

                    $amount = $head['calculation_type'] === 'per_sqft'
                        ? $rate * (float) ($flat['carpet_area_sqft'] ?? 0)
                        : $rate;
                    $items[] = ['source' => 'head', 'ref_id' => $head['id'], 'description' => $head['name'], 'amount' => $amount];
                    $total += $amount;
                }

                // One line item per allocated slot — no proration, full charge if the
                // allocation overlaps the period at all (see BillingService doc comment).
                $allocations = ParkingAllocation::activeForFlatDuringPeriod((int) $flat['id'], $periodStart, $periodEnd);
                foreach ($allocations as $allocation) {
                    if (!(int) $allocation['is_chargeable']) {
                        continue; // marked free/courtesy at allocation time — never billed, regardless of rate
                    }

                    $rate = ParkingRate::amountAsOf($societyId, $allocation['slot_type'], $periodStart);
                    if ($rate === null) {
                        continue; // no rate configured for this slot type yet — not charged
                    }

                    $label = $allocation['slot_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler';
                    $items[] = [
                        'source' => 'parking',
                        'ref_id' => $allocation['parking_slot_id'],
                        'description' => "Parking - {$allocation['slot_number']} ({$label})",
                        'amount' => $rate,
                    ];
                    $total += $rate;
                }

                if (empty($items)) {
                    $noCharge++;
                    continue;
                }

                $insertBill = $pdo->prepare(
                    'INSERT INTO maintenance_bills
                        (society_id, flat_id, financial_year_id, bill_number, bill_period_start, bill_period_end, due_date, total_amount, paid_amount, status)
                     VALUES
                        (:society_id, :flat_id, :fy_id, :bill_number, :period_start, :period_end, :due_date, :total, 0, "unpaid")'
                );
                $tempNumber = 'TMP-' . uniqid();
                $insertBill->execute([
                    'society_id' => $societyId,
                    'flat_id' => $flat['id'],
                    'fy_id' => $financialYearId,
                    'bill_number' => $tempNumber,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'due_date' => $dueDate,
                    'total' => $total,
                ]);
                $billId = (int) $pdo->lastInsertId();

                $billNumber = sprintf('BILL-%s-%06d', date('Ymd', strtotime($periodStart)), $billId);
                $pdo->prepare('UPDATE maintenance_bills SET bill_number = :num WHERE id = :id')
                    ->execute(['num' => $billNumber, 'id' => $billId]);

                $insertHeadItem = $pdo->prepare(
                    'INSERT INTO bill_items (maintenance_bill_id, maintenance_head_id, description, amount)
                     VALUES (:bill_id, :ref_id, :description, :amount)'
                );
                $insertParkingItem = $pdo->prepare(
                    'INSERT INTO bill_items (maintenance_bill_id, parking_slot_id, description, amount)
                     VALUES (:bill_id, :ref_id, :description, :amount)'
                );
                foreach ($items as $item) {
                    $stmt = $item['source'] === 'head' ? $insertHeadItem : $insertParkingItem;
                    $stmt->execute([
                        'bill_id' => $billId,
                        'ref_id' => $item['ref_id'],
                        'description' => $item['description'],
                        'amount' => $item['amount'],
                    ]);
                }

                $created++;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['created' => $created, 'skipped' => $skipped, 'noCharge' => $noCharge];
    }

    /**
     * Records a payment against a bill, updates the bill's paid amount/status, and issues a receipt.
     *
     * @return array{payment_id:int, receipt_id:int, receipt_number:string}
     */
    public static function recordPayment(int $billId, float $amount, string $mode, ?string $reference, ?int $receivedBy): array
    {
        $pdo = db();

        $pdo->beginTransaction();
        try {
            $paymentId = Payment::create($billId, $amount, $mode, $reference, $receivedBy);
            $receiptNumber = sprintf('RCPT-%06d', $paymentId);
            $receiptId = Receipt::create($paymentId, $receiptNumber);
            MaintenanceBill::updatePaidAmountAndStatus($billId);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['payment_id' => $paymentId, 'receipt_id' => $receiptId, 'receipt_number' => $receiptNumber];
    }
}
