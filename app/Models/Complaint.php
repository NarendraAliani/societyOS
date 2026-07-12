<?php

declare(strict_types=1);

namespace App\Models;

final class Complaint
{
    public static function allForSociety(int $societyId, ?string $status = null): array
    {
        $sql = 'SELECT c.*, f.flat_number, w.name AS wing_name, m.name AS member_name, cc.name AS category_name
                FROM complaints c
                JOIN flats f ON f.id = c.flat_id
                JOIN floors fl ON fl.id = f.floor_id
                JOIN wings w ON w.id = fl.wing_id
                JOIN members m ON m.id = c.member_id
                JOIN complaint_categories cc ON cc.id = c.category_id
                WHERE c.society_id = :sid';
        $params = ['sid' => $societyId];

        if ($status !== null && $status !== '') {
            $sql .= ' AND c.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY FIELD(c.priority, "high","medium","low"), c.created_at DESC';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT c.*, f.flat_number, w.name AS wing_name, m.name AS member_name, cc.name AS category_name
             FROM complaints c
             JOIN flats f ON f.id = c.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             JOIN members m ON m.id = c.member_id
             JOIN complaint_categories cc ON cc.id = c.category_id
             WHERE c.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO complaints (society_id, flat_id, member_id, category_id, subject, description, priority, status)
             VALUES (:sid, :flat_id, :member_id, :category_id, :subject, :description, :priority, "open")'
        );
        $stmt->execute([
            'sid' => $societyId,
            'flat_id' => $fields['flat_id'],
            'member_id' => $fields['member_id'],
            'category_id' => $fields['category_id'],
            'subject' => $fields['subject'],
            'description' => $fields['description'] ?: null,
            'priority' => $fields['priority'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function summaryByCategory(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT cc.name AS category_name,
                    COUNT(*) AS total,
                    SUM(c.status = "open") AS open_count,
                    SUM(c.status = "in_progress") AS in_progress_count,
                    SUM(c.status = "resolved") AS resolved_count,
                    SUM(c.status = "closed") AS closed_count
             FROM complaints c
             JOIN complaint_categories cc ON cc.id = c.category_id
             WHERE c.society_id = :sid
             GROUP BY cc.id, cc.name
             ORDER BY total DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function updates(int $complaintId): array
    {
        $stmt = db()->prepare(
            'SELECT cu.*, u.name AS updated_by_name
             FROM complaint_updates cu
             LEFT JOIN users u ON u.id = cu.updated_by
             WHERE cu.complaint_id = :id
             ORDER BY cu.created_at DESC'
        );
        $stmt->execute(['id' => $complaintId]);
        return $stmt->fetchAll();
    }

    public static function addUpdate(int $complaintId, string $status, ?string $remarks, ?int $updatedBy, ?int $assignedTo): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'INSERT INTO complaint_updates (complaint_id, updated_by, status, remarks) VALUES (:id, :updated_by, :status, :remarks)'
            )->execute(['id' => $complaintId, 'updated_by' => $updatedBy, 'status' => $status, 'remarks' => $remarks]);

            $resolvedAt = in_array($status, ['resolved', 'closed'], true) ? 'NOW()' : 'NULL';
            $sql = "UPDATE complaints SET status = :status, resolved_at = {$resolvedAt}" .
                ($assignedTo !== null ? ', assigned_to = :assigned_to' : '') .
                ' WHERE id = :id';
            $update = $pdo->prepare($sql);
            $params = ['status' => $status, 'id' => $complaintId];
            if ($assignedTo !== null) {
                $params['assigned_to'] = $assignedTo;
            }
            $update->execute($params);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
