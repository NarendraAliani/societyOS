<?php

declare(strict_types=1);

namespace App\Models;

final class LeaveRequest
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT lr.*, s.name AS staff_name
             FROM leave_requests lr
             JOIN staff s ON s.id = lr.staff_id
             WHERE s.society_id = :sid
             ORDER BY lr.from_date DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function create(int $staffId, string $fromDate, string $toDate, ?string $reason): int
    {
        $stmt = db()->prepare(
            'INSERT INTO leave_requests (staff_id, from_date, to_date, reason, status) VALUES (:staff_id, :from_date, :to_date, :reason, "pending")'
        );
        $stmt->execute(['staff_id' => $staffId, 'from_date' => $fromDate, 'to_date' => $toDate, 'reason' => $reason]);
        return (int) db()->lastInsertId();
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE leave_requests SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
