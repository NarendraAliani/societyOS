<?php

declare(strict_types=1);

namespace App\Models;

final class Tenant
{
    public static function forMember(int $memberId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM tenants WHERE member_id = :member_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM tenants WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT t.*, f.flat_number, fl.floor_number, w.name AS wing_name,
                    tm.name AS tenant_name, tm.phone AS tenant_phone,
                    om.name AS owner_name, om.phone AS owner_phone
             FROM tenants t
             JOIN flats f ON f.id = t.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             JOIN members tm ON tm.id = t.member_id
             JOIN members om ON om.id = t.owner_member_id
             WHERE w.society_id = :sid
             ORDER BY t.lease_end IS NULL, t.lease_end ASC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    /** Owner-type residents sharing the same flat as the given tenant member — candidates for owner_member_id. */
    public static function ownerCandidatesForFlat(int $flatId): array
    {
        $stmt = db()->prepare(
            "SELECT id, name FROM members WHERE flat_id = :flat_id AND member_type = 'owner' AND status = 'active'"
        );
        $stmt->execute(['flat_id' => $flatId]);
        return $stmt->fetchAll();
    }

    public static function create(int $flatId, int $memberId, int $ownerMemberId, ?string $leaseStart, ?string $leaseEnd, ?string $agreementDocPath): int
    {
        $stmt = db()->prepare(
            'INSERT INTO tenants (flat_id, member_id, owner_member_id, lease_start, lease_end, agreement_doc_path)
             VALUES (:flat_id, :member_id, :owner_member_id, :lease_start, :lease_end, :agreement_doc_path)'
        );
        $stmt->execute([
            'flat_id' => $flatId,
            'member_id' => $memberId,
            'owner_member_id' => $ownerMemberId,
            'lease_start' => $leaseStart ?: null,
            'lease_end' => $leaseEnd ?: null,
            'agreement_doc_path' => $agreementDocPath,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, int $ownerMemberId, ?string $leaseStart, ?string $leaseEnd): void
    {
        $stmt = db()->prepare(
            'UPDATE tenants SET owner_member_id = :owner_member_id, lease_start = :lease_start, lease_end = :lease_end WHERE id = :id'
        );
        $stmt->execute([
            'owner_member_id' => $ownerMemberId,
            'lease_start' => $leaseStart ?: null,
            'lease_end' => $leaseEnd ?: null,
            'id' => $id,
        ]);
    }

    public static function updateAgreementDoc(int $id, string $path): void
    {
        $stmt = db()->prepare('UPDATE tenants SET agreement_doc_path = :path WHERE id = :id');
        $stmt->execute(['path' => $path, 'id' => $id]);
    }
}
