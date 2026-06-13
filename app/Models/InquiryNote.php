<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class InquiryNote
{
    public function __construct(private readonly Database $database)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byInquiryId(int $inquiryId): array
    {
        $rows = $this->database->query(
            'SELECT notes.*, users.first_name, users.last_name
             FROM inquiry_notes notes
             LEFT JOIN users ON users.id = notes.user_id
             WHERE notes.inquiry_id = :inquiry_id
             ORDER BY notes.created_at DESC, notes.id DESC',
            ['inquiry_id' => $inquiryId]
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function create(int $inquiryId, ?int $userId, string $note): int
    {
        $this->database->query(
            'INSERT INTO inquiry_notes (inquiry_id, user_id, note, created_at, updated_at)
             VALUES (:inquiry_id, :user_id, :note, NOW(), NOW())',
            [
                'inquiry_id' => $inquiryId,
                'user_id' => $userId,
                'note' => $note,
            ]
        );

        return (int) $this->database->connection()->lastInsertId();
    }
}