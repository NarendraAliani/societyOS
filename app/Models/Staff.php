<?php

declare(strict_types=1);

namespace App\Models;

final class Staff
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare('SELECT * FROM staff WHERE society_id = :sid ORDER BY name');
        $stmt->execute(['sid' => $societyId]);
        return array_map([self::class, 'withDisplayAge'], $stmt->fetchAll());
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM staff WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? self::withDisplayAge($row) : null;
    }

    /** Same "never store a stale age" approach as FamilyMember — see that model's comment. */
    private static function withDisplayAge(array $row): array
    {
        $row['display_age'] = $row['date_of_birth']
            ? (new \DateTimeImmutable($row['date_of_birth']))->diff(new \DateTimeImmutable('today'))->y
            : null;
        return $row;
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO staff (society_id, name, designation, phone, address, date_of_birth, joining_date, photo_path, id_proof_path, status)
             VALUES (:sid, :name, :designation, :phone, :address, :dob, :joining_date, :photo_path, :id_proof_path, "active")'
        );
        $stmt->execute([
            'sid' => $societyId,
            'name' => $fields['name'],
            'designation' => $fields['designation'] ?: null,
            'phone' => $fields['phone'] ?: null,
            'address' => $fields['address'] ?: null,
            'dob' => $fields['date_of_birth'] ?: null,
            'joining_date' => $fields['joining_date'] ?: null,
            'photo_path' => $fields['photo_path'] ?? null,
            'id_proof_path' => $fields['id_proof_path'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $fields): void
    {
        $stmt = db()->prepare(
            'UPDATE staff SET name = :name, designation = :designation, phone = :phone, address = :address,
                date_of_birth = :dob, joining_date = :joining_date
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $fields['name'],
            'designation' => $fields['designation'] ?: null,
            'phone' => $fields['phone'] ?: null,
            'address' => $fields['address'] ?: null,
            'dob' => $fields['date_of_birth'] ?: null,
            'joining_date' => $fields['joining_date'] ?: null,
            'id' => $id,
        ]);
    }

    /** Only called when a new file was actually uploaded — an existing photo is never cleared by omission. */
    public static function updatePhoto(int $id, string $photoPath): void
    {
        $stmt = db()->prepare('UPDATE staff SET photo_path = :path WHERE id = :id');
        $stmt->execute(['path' => $photoPath, 'id' => $id]);
    }

    public static function updateIdProof(int $id, string $idProofPath): void
    {
        $stmt = db()->prepare('UPDATE staff SET id_proof_path = :path WHERE id = :id');
        $stmt->execute(['path' => $idProofPath, 'id' => $id]);
    }

    public static function updatePoliceVerification(int $id, string $status, ?string $date, ?string $docPath): void
    {
        if ($docPath !== null) {
            $stmt = db()->prepare(
                'UPDATE staff SET police_verification_status = :status, police_verification_date = :date, police_verification_doc_path = :doc WHERE id = :id'
            );
            $stmt->execute(['status' => $status, 'date' => $date, 'doc' => $docPath, 'id' => $id]);
            return;
        }

        $stmt = db()->prepare(
            'UPDATE staff SET police_verification_status = :status, police_verification_date = :date WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'date' => $date, 'id' => $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE staff SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM staff WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
