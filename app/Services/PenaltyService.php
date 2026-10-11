<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MaintenanceBill;
use App\Models\Penalty;
use App\Models\Settings;

/**
 * Recomputes late-payment interest as a running estimate against the bill's *current*
 * outstanding balance — not a day-by-day ledger across partial payments. Once the
 * outstanding balance reaches zero, recalculation stops and the last computed penalty is
 * left in place (still owed, not erased) rather than reset to zero.
 */
final class PenaltyService
{
    public static function recalculate(int $billId): ?array
    {
        $bill = MaintenanceBill::find($billId);
        if (!$bill) {
            return null;
        }

        $outstanding = (float) $bill['total_amount'] - (float) $bill['paid_amount'];
        $daysOverdue = (int) floor((strtotime('today') - strtotime((string) $bill['due_date'])) / 86400);

        if ($daysOverdue <= 0 || $outstanding <= 0) {
            return Penalty::forBill($billId);
        }

        $ratePercent = (float) Settings::get(
            (int) $bill['society_id'],
            'penalty_interest_rate_percent',
            config()['penalty_interest_rate_percent']
        );
        $dailyRate = $ratePercent / 100 / 365;
        $penaltyAmount = round($daysOverdue * $dailyRate * $outstanding, 2);

        Penalty::upsert($billId, $ratePercent, $daysOverdue, $penaltyAmount);

        return Penalty::forBill($billId);
    }
}
