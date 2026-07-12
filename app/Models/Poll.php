<?php

declare(strict_types=1);

namespace App\Models;

final class Poll
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare('SELECT * FROM polls WHERE society_id = :sid ORDER BY created_at DESC');
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM polls WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, string $question, ?string $closesAt, ?int $createdBy, array $options): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO polls (society_id, question, closes_at, created_by) VALUES (:sid, :question, :closes_at, :created_by)');
            $stmt->execute(['sid' => $societyId, 'question' => $question, 'closes_at' => $closesAt, 'created_by' => $createdBy]);
            $pollId = (int) $pdo->lastInsertId();

            $insertOption = $pdo->prepare('INSERT INTO poll_options (poll_id, option_text) VALUES (:poll_id, :text)');
            foreach ($options as $option) {
                $option = trim($option);
                if ($option !== '') {
                    $insertOption->execute(['poll_id' => $pollId, 'text' => $option]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $pollId;
    }

    public static function optionsWithVoteCounts(int $pollId): array
    {
        $stmt = db()->prepare(
            'SELECT po.*, COUNT(pv.id) AS vote_count
             FROM poll_options po
             LEFT JOIN poll_votes pv ON pv.poll_option_id = po.id
             WHERE po.poll_id = :poll_id
             GROUP BY po.id
             ORDER BY po.id'
        );
        $stmt->execute(['poll_id' => $pollId]);
        return $stmt->fetchAll();
    }

    /**
     * Casts (or changes) a member's vote on a poll.
     *
     * The DB's unique constraint on poll_votes is (poll_option_id, member_id), which only
     * blocks re-voting the *same* option — it does not stop a member voting for several
     * different options in one poll. This method enforces "one active vote per member per
     * poll" at the application layer by clearing the member's prior vote(s) on this poll's
     * options before inserting the new one, all inside one transaction.
     */
    public static function vote(int $pollId, int $pollOptionId, int $memberId): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'DELETE pv FROM poll_votes pv
                 JOIN poll_options po ON po.id = pv.poll_option_id
                 WHERE po.poll_id = :poll_id AND pv.member_id = :member_id'
            )->execute(['poll_id' => $pollId, 'member_id' => $memberId]);

            $pdo->prepare('INSERT INTO poll_votes (poll_option_id, member_id) VALUES (:option_id, :member_id)')
                ->execute(['option_id' => $pollOptionId, 'member_id' => $memberId]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
