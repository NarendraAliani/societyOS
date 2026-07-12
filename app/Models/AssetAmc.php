<?php

declare(strict_types=1);

namespace App\Models;

final class AssetAmc
{
    public static function forAsset(int $assetId): array
    {
        $stmt = db()->prepare(
            'SELECT amc.*, v.name AS vendor_name
             FROM asset_amc amc
             LEFT JOIN vendors v ON v.id = amc.vendor_id
             WHERE amc.asset_id = :asset_id
             ORDER BY amc.end_date DESC'
        );
        $stmt->execute(['asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    public static function create(int $assetId, ?int $vendorId, string $startDate, string $endDate, ?float $cost): int
    {
        $stmt = db()->prepare(
            'INSERT INTO asset_amc (asset_id, vendor_id, start_date, end_date, cost) VALUES (:asset_id, :vendor_id, :start, :end, :cost)'
        );
        $stmt->execute(['asset_id' => $assetId, 'vendor_id' => $vendorId, 'start' => $startDate, 'end' => $endDate, 'cost' => $cost]);
        return (int) db()->lastInsertId();
    }

    public static function expiringWithin(int $societyId, int $days): array
    {
        $stmt = db()->prepare(
            'SELECT amc.*, a.name AS asset_name
             FROM asset_amc amc
             JOIN assets a ON a.id = amc.asset_id
             WHERE a.society_id = :sid AND amc.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY amc.end_date'
        );
        $stmt->execute(['sid' => $societyId, 'days' => $days]);
        return $stmt->fetchAll();
    }
}
