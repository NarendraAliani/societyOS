<?php

declare(strict_types=1);

namespace App\Models;

use RuntimeException;

final class Wing
{
    public static function allWithCounts(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT w.*,
                    COUNT(DISTINCT fl.id) AS floor_count,
                    COUNT(DISTINCT ft.id) AS flat_count
             FROM wings w
             LEFT JOIN floors fl ON fl.wing_id = w.id
             LEFT JOIN flats ft ON ft.floor_id = fl.id
             WHERE w.society_id = :society_id
             GROUP BY w.id
             ORDER BY w.name'
        );
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM wings WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, string $name): int
    {
        $stmt = db()->prepare('INSERT INTO wings (society_id, name) VALUES (:society_id, :name)');
        $stmt->execute(['society_id' => $societyId, 'name' => $name]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name): void
    {
        $stmt = db()->prepare('UPDATE wings SET name = :name WHERE id = :id');
        $stmt->execute(['name' => $name, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM wings WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Configure a wing's floor/flat structure without deleting existing data.
     *
     * Existing floors can have their desired flat count increased, which creates
     * missing flats using the floor's conventional numeric sequence (101, 102...).
     * Existing flat records are never removed by this operation.
     *
     * If the requested floor count is higher than the current count, new floor
     * numbers are allocated from the lowest unused positive integer upward.
     * New floors receive the supplied default flat count.
     *
     * @param array<int|string, int> $flatCounts Desired flat counts keyed by floor id.
     * @return array{created_floors:int,created_flats:int}
     */
    public static function configureStructure(
        int $wingId,
        int $floorCount,
        int $defaultFlatsPerNewFloor,
        array $flatCounts
    ): array {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $floorStmt = $pdo->prepare(
                'SELECT id, floor_number
                 FROM floors
                 WHERE wing_id = :wing_id
                 ORDER BY floor_number
                 FOR UPDATE'
            );
            $floorStmt->execute(['wing_id' => $wingId]);
            $floors = $floorStmt->fetchAll();

            if ($floorCount < count($floors)) {
                throw new RuntimeException(
                    'The requested floor count is lower than the existing floor count. Existing floors are never deleted automatically.'
                );
            }

            $existingFloorNumbers = [];
            foreach ($floors as $floor) {
                $existingFloorNumbers[(int) $floor['floor_number']] = true;
            }

            $flatCountStmt = $pdo->prepare('SELECT COUNT(*) FROM flats WHERE floor_id = :floor_id');
            $flatSelectStmt = $pdo->prepare(
                'SELECT flat_number
                 FROM flats
                 WHERE floor_id = :floor_id
                 FOR UPDATE'
            );
            $flatInsertStmt = $pdo->prepare(
                'INSERT INTO flats (floor_id, flat_number, flat_type, carpet_area_sqft, occupancy_status)
                 VALUES (:floor_id, :flat_number, NULL, NULL, 'vacant')'
            );
            $floorInsertStmt = $pdo->prepare(
                'INSERT INTO floors (wing_id, floor_number) VALUES (:wing_id, :floor_number)'
            );

            $createdFloors = 0;
            $createdFlats = 0;

            foreach ($floors as $floor) {
                $floorId = (int) $floor['id'];
                $flatCountStmt->execute(['floor_id' => $floorId]);
                $currentFlatCount = (int) $flatCountStmt->fetchColumn();

                $desiredFlatCount = $flatCounts[$floorId] ?? $currentFlatCount;
                $desiredFlatCount = (int) $desiredFlatCount;

                if ($desiredFlatCount < $currentFlatCount) {
                    throw new RuntimeException(
                        sprintf(
                            'Floor %s already has %d flats. The configurator will not delete existing flats; reduce the count by deleting unused flats manually first.',
                            $floor['floor_number'],
                            $currentFlatCount
                        )
                    );
                }

                if ($desiredFlatCount === $currentFlatCount) {
                    continue;
                }

                $flatSelectStmt->execute(['floor_id' => $floorId]);
                $existingFlatNumbers = array_fill_keys(
                    array_map(
                        static fn (array $row): string => (string) $row['flat_number'],
                        $flatSelectStmt->fetchAll()
                    ),
                    true
                );

                $position = 1;
                while (count($existingFlatNumbers) < $desiredFlatCount) {
                    $flatNumber = sprintf('%d%02d', (int) $floor['floor_number'], $position);
                    $position++;

                    if (isset($existingFlatNumbers[$flatNumber])) {
                        continue;
                    }

                    $flatInsertStmt->execute([
                        'floor_id' => $floorId,
                        'flat_number' => $flatNumber,
                    ]);
                    $existingFlatNumbers[$flatNumber] = true;
                    $createdFlats++;
                }
            }

            $nextFloorNumber = 1;
            while (count($floors) + $createdFloors < $floorCount) {
                while (isset($existingFloorNumbers[$nextFloorNumber])) {
                    $nextFloorNumber++;
                }

                $floorInsertStmt->execute([
                    'wing_id' => $wingId,
                    'floor_number' => $nextFloorNumber,
                ]);
                $floorId = (int) $pdo->lastInsertId();
                $existingFloorNumbers[$nextFloorNumber] = true;
                $createdFloors++;

                $position = 1;
                while ($position <= $defaultFlatsPerNewFloor) {
                    $flatNumber = sprintf('%d%02d', $nextFloorNumber, $position);
                    $flatInsertStmt->execute([
                        'floor_id' => $floorId,
                        'flat_number' => $flatNumber,
                    ]);
                    $createdFlats++;
                    $position++;
                }

                $nextFloorNumber++;
            }

            $pdo->commit();

            return [
                'created_floors' => $createdFloors,
                'created_flats' => $createdFlats,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
