<?php

declare(strict_types=1);

namespace App\Models;

final class MaintenanceBill
{
    public static function allForSociety(int $societyId, ?string $status = null): array
    {
        $sql = 'SELECT b.*, f.flat_number, fl.floor_number, w.name AS wing_name
                FROM maintenance_bills b
                JOIN flats f ON f.id = b.flat_id
                JOIN floors fl ON fl.id = f.floor_id
                JOIN wings w ON w.id = fl.wing_id
                WHERE b.society_id = :sid';
        $params = ['sid' => $societyId];

        if ($status !== null && $status !== '') {
            $sql .= ' AND b.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY b.due_date DESC, w.name, fl.floor_number, f.flat_number';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT b.*, f.flat_number, fl.floor_number, w.name AS wing_name
             FROM maintenance_bills b
             JOIN flats f ON f.id = b.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE b.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function existsForFlatAndPeriod(int $flatId, string $periodStart, string $periodEnd): bool
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM maintenance_bills
             WHERE flat_id = :flat_id AND bill_period_start = :start AND bill_period_end = :end'
        );
        $stmt->execute(['flat_id' => $flatId, 'start' => $periodStart, 'end' => $periodEnd]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * A bill item's source is either a maintenance head or a parking slot (never both —
     * enforced by the chk_bill_item_source CHECK constraint). `description` already holds
     * a human-readable label set at generation time, so it's used directly rather than
     * re-joining head/slot names — a deleted head or slot after billing shouldn't blank
     * out a historical bill's line item text.
     */
    public static function items(int $billId): array
    {
        $stmt = db()->prepare('SELECT * FROM bill_items WHERE maintenance_bill_id = :bill_id');
        $stmt->execute(['bill_id' => $billId]);
        return $stmt->fetchAll();
    }

    public static function payments(int $billId): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, r.receipt_number
             FROM payments p
             LEFT JOIN receipts r ON r.payment_id = p.id
             WHERE p.maintenance_bill_id = :bill_id
             ORDER BY p.paid_at DESC'
        );
        $stmt->execute(['bill_id' => $billId]);
        return $stmt->fetchAll();
    }

    public static function updatePaidAmountAndStatus(int $billId): void
    {
        $pdo = db();

        $bill = $pdo->prepare('SELECT total_amount, due_date FROM maintenance_bills WHERE id = :id');
        $bill->execute(['id' => $billId]);
        $row = $bill->fetch();
        if (!$row) {
            return;
        }

        $paidStmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE maintenance_bill_id = :id');
        $paidStmt->execute(['id' => $billId]);
        $paid = (float) $paidStmt->fetchColumn();

        $total = (float) $row['total_amount'];

        if ($paid >= $total) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        } elseif (strtotime((string) $row['due_date']) < strtotime('today')) {
            $status = 'overdue';
        } else {
            $status = 'unpaid';
        }

        $update = $pdo->prepare('UPDATE maintenance_bills SET paid_amount = :paid, status = :status WHERE id = :id');
        $update->execute(['paid' => $paid, 'status' => $status, 'id' => $billId]);
    }

    public static function collectionReport(int $societyId, string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT p.id AS payment_id, p.paid_at, p.amount, p.payment_mode, p.reference_number,
                    b.bill_number, f.flat_number, w.name AS wing_name
             FROM payments p
             JOIN maintenance_bills b ON b.id = p.maintenance_bill_id
             JOIN flats f ON f.id = b.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE b.society_id = :sid AND DATE(p.paid_at) BETWEEN :from AND :to
             ORDER BY p.paid_at DESC'
        );
        $stmt->execute(['sid' => $societyId, 'from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    /** Total unpaid balance across all bills, regardless of due date — "Accounts Receivable" for the Balance Sheet. */
    public static function totalOutstanding(int $societyId): float
    {
        $stmt = db()->prepare(
            "SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM maintenance_bills
             WHERE society_id = :sid AND status != 'paid'"
        );
        $stmt->execute(['sid' => $societyId]);
        return (float) $stmt->fetchColumn();
    }

    public static function defaulters(int $societyId): array
    {
        $stmt = db()->prepare(
            "SELECT b.*, f.flat_number, fl.floor_number, w.name AS wing_name,
                    (b.total_amount - b.paid_amount) AS outstanding,
                    DATEDIFF(CURDATE(), b.due_date) AS days_overdue
             FROM maintenance_bills b
             JOIN flats f ON f.id = b.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE b.society_id = :sid AND b.status IN ('unpaid','partially_paid','overdue') AND b.due_date < CURDATE()
             ORDER BY days_overdue DESC"
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }
}
