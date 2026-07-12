<?php

declare(strict_types=1);

namespace App\Models;

final class Attendance
{
    public static function forDate(int $societyId, string $date): array
    {
        $stmt = db()->prepare(
            'SELECT s.id AS staff_id, s.name, s.designation, a.id AS attendance_id, a.status
             FROM staff s
             LEFT JOIN attendance a ON a.staff_id = s.id AND a.attendance_date = :date
             WHERE s.society_id = :sid AND s.status = "active"
             ORDER BY s.name'
        );
        $stmt->execute(['sid' => $societyId, 'date' => $date]);
        return $stmt->fetchAll();
    }

    public static function mark(int $staffId, string $date, string $status, ?int $markedBy): void
    {
        $stmt = db()->prepare(
            'INSERT INTO attendance (staff_id, attendance_date, status, marked_by)
             VALUES (:staff_id, :date, :status, :marked_by)
             ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)'
        );
        $stmt->execute(['staff_id' => $staffId, 'date' => $date, 'status' => $status, 'marked_by' => $markedBy]);
    }
}
