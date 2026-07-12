<?php

declare(strict_types=1);

namespace App\Models;

final class AssetService
{
    public static function forAsset(int $assetId): array
    {
        $stmt = db()->prepare('SELECT * FROM asset_services WHERE asset_id = :asset_id ORDER BY service_date DESC');
        $stmt->execute(['asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    public static function create(int $assetId, string $serviceDate, ?string $description, ?float $cost, ?string $nextDueDate): int
    {
        $stmt = db()->prepare(
            'INSERT INTO asset_services (asset_id, service_date, description, cost, next_due_date)
             VALUES (:asset_id, :service_date, :description, :cost, :next_due_date)'
        );
        $stmt->execute([
            'asset_id' => $assetId,
            'service_date' => $serviceDate,
            'description' => $description,
            'cost' => $cost,
            'next_due_date' => $nextDueDate ?: null,
        ]);
        return (int) db()->lastInsertId();
    }
}
