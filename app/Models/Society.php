<?php

declare(strict_types=1);

namespace App\Models;

final class Society
{
    private static ?array $cached = null;

    public static function current(): array
    {
        if (self::$cached === null) {
            $stmt = db()->query('SELECT * FROM society ORDER BY id ASC LIMIT 1');
            self::$cached = $stmt->fetch() ?: [];
        }
        return self::$cached;
    }

    public static function currentId(): int
    {
        return (int) (self::current()['id'] ?? 0);
    }

    public static function update(int $id, array $fields): void
    {
        $stmt = db()->prepare(
            'UPDATE society SET name = :name, registration_no = :registration_no, address = :address,
                city = :city, state = :state, pincode = :pincode, phone = :phone, email = :email,
                gstin = :gstin, pan = :pan
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $fields['name'],
            'registration_no' => $fields['registration_no'] ?: null,
            'address' => $fields['address'] ?: null,
            'city' => $fields['city'] ?: null,
            'state' => $fields['state'] ?: null,
            'pincode' => $fields['pincode'] ?: null,
            'phone' => $fields['phone'] ?: null,
            'email' => $fields['email'] ?: null,
            'gstin' => $fields['gstin'] ?: null,
            'pan' => $fields['pan'] ?: null,
            'id' => $id,
        ]);
        self::$cached = null;
    }

    public static function dashboardStats(int $societyId): array
    {
        $pdo = db();

        $flats = $pdo->prepare('SELECT COUNT(*) FROM flats f JOIN floors fl ON fl.id = f.floor_id JOIN wings w ON w.id = fl.wing_id WHERE w.society_id = :sid');
        $flats->execute(['sid' => $societyId]);

        $members = $pdo->prepare("SELECT COUNT(*) FROM members WHERE society_id = :sid AND status = 'active'");
        $members->execute(['sid' => $societyId]);

        $visitorsToday = $pdo->prepare('SELECT COUNT(*) FROM visitors WHERE society_id = :sid AND DATE(check_in_at) = CURDATE()');
        $visitorsToday->execute(['sid' => $societyId]);

        $pendingComplaints = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE society_id = :sid AND status IN ('open','in_progress')");
        $pendingComplaints->execute(['sid' => $societyId]);

        $outstandingBills = $pdo->prepare("SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM maintenance_bills WHERE society_id = :sid AND status != 'paid'");
        $outstandingBills->execute(['sid' => $societyId]);

        $income = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM income WHERE society_id = :sid AND MONTH(income_date) = MONTH(CURDATE()) AND YEAR(income_date) = YEAR(CURDATE())');
        $income->execute(['sid' => $societyId]);

        $expenses = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE society_id = :sid AND MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())');
        $expenses->execute(['sid' => $societyId]);

        return [
            'flats' => (int) $flats->fetchColumn(),
            'active_members' => (int) $members->fetchColumn(),
            'visitors_today' => (int) $visitorsToday->fetchColumn(),
            'pending_complaints' => (int) $pendingComplaints->fetchColumn(),
            'outstanding_amount' => (float) $outstandingBills->fetchColumn(),
            'income_this_month' => (float) $income->fetchColumn(),
            'expenses_this_month' => (float) $expenses->fetchColumn(),
        ];
    }
}
