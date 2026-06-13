<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Testimonial
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?array
    {
        $row = $this->database->query(
            $this->selectBase() . ' WHERE t.id = :id AND t.deleted_at IS NULL LIMIT 1',
            ['id' => $id]
        )->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        [$whereClause, $params] = $this->searchWhere($filters);

        $rows = $this->database->query(
            sprintf(
                '%s WHERE %s ORDER BY t.featured DESC, t.sort_order ASC, t.updated_at DESC, t.id DESC LIMIT %d OFFSET %d',
                $this->selectBase(),
                $whereClause,
                max(1, $limit),
                max(0, $offset)
            ),
            $params
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function count(array $filters = []): int
    {
        [$whereClause, $params] = $this->searchWhere($filters);

        return (int) $this->database->query(
            'SELECT COUNT(*) FROM testimonials t WHERE ' . $whereClause,
            $params
        )->fetchColumn();
    }

    public function nextSortOrder(): int
    {
        return (int) $this->database->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM testimonials WHERE deleted_at IS NULL'
        )->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO testimonials (
                client_name, client_label, quote, long_form_story, rating, featured, service_id, gallery_id,
                portrait_media_id, event_date, location, status, sort_order, created_at, updated_at
            ) VALUES (
                :client_name, :client_label, :quote, :long_form_story, :rating, :featured, :service_id, :gallery_id,
                :portrait_media_id, :event_date, :location, :status, :sort_order, NOW(), NOW()
            )',
            [
                'client_name' => $data['client_name'] ?? '',
                'client_label' => $data['client_label'] ?? null,
                'quote' => $data['quote'] ?? '',
                'long_form_story' => $data['long_form_story'] ?? null,
                'rating' => $data['rating'] ?? null,
                'featured' => $data['featured'] ?? 0,
                'service_id' => $data['service_id'] ?? null,
                'gallery_id' => $data['gallery_id'] ?? null,
                'portrait_media_id' => $data['portrait_media_id'] ?? null,
                'event_date' => $data['event_date'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'sort_order' => $data['sort_order'] ?? 0,
            ]
        );

        return (int) $this->database->connection()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $this->database->query(
            'UPDATE testimonials SET
                client_name = :client_name,
                client_label = :client_label,
                quote = :quote,
                long_form_story = :long_form_story,
                rating = :rating,
                featured = :featured,
                service_id = :service_id,
                gallery_id = :gallery_id,
                portrait_media_id = :portrait_media_id,
                event_date = :event_date,
                location = :location,
                status = :status,
                sort_order = :sort_order,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL',
            [
                'id' => $id,
                'client_name' => $data['client_name'] ?? '',
                'client_label' => $data['client_label'] ?? null,
                'quote' => $data['quote'] ?? '',
                'long_form_story' => $data['long_form_story'] ?? null,
                'rating' => $data['rating'] ?? null,
                'featured' => $data['featured'] ?? 0,
                'service_id' => $data['service_id'] ?? null,
                'gallery_id' => $data['gallery_id'] ?? null,
                'portrait_media_id' => $data['portrait_media_id'] ?? null,
                'event_date' => $data['event_date'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'sort_order' => $data['sort_order'] ?? 0,
            ]
        );
    }

    public function softDelete(int $id): void
    {
        $this->database->query(
            'UPDATE testimonials SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL',
            ['id' => $id]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allPublished(int $limit = 50): array
    {
        $rows = $this->database->query(
            sprintf(
                '%s WHERE t.status = "published" AND t.deleted_at IS NULL ORDER BY t.featured DESC, t.sort_order ASC, t.id DESC LIMIT %d',
                $this->selectBase(),
                max(1, $limit)
            )
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function featuredPublished(int $limit = 3): array
    {
        $rows = $this->database->query(
            sprintf(
                '%s WHERE t.status = "published" AND t.deleted_at IS NULL AND t.featured = 1 ORDER BY t.sort_order ASC, t.id DESC LIMIT %d',
                $this->selectBase(),
                max(1, $limit)
            )
        )->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    private function selectBase(): string
    {
        return 'SELECT t.*, s.title AS service_title, s.slug AS service_slug,
                       g.title AS gallery_title, g.slug AS gallery_slug,
                       m.directory AS portrait_directory, m.stored_name AS portrait_stored_name,
                       m.alt_text AS portrait_alt_text, m.title AS portrait_title
                FROM testimonials t
                LEFT JOIN services s ON s.id = t.service_id AND s.deleted_at IS NULL
                LEFT JOIN galleries g ON g.id = t.gallery_id AND g.deleted_at IS NULL
                LEFT JOIN media m ON m.id = t.portrait_media_id AND m.deleted_at IS NULL';
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function searchWhere(array $filters): array
    {
        $whereParts = ['t.deleted_at IS NULL'];
        $params = [];

        if (isset($filters['query']) && is_string($filters['query']) && trim($filters['query']) !== '') {
            $whereParts[] = '(t.client_name LIKE :query OR t.client_label LIKE :query OR t.quote LIKE :query OR t.location LIKE :query)';
            $params['query'] = '%' . trim($filters['query']) . '%';
        }

        if (isset($filters['status']) && is_string($filters['status']) && $filters['status'] !== '') {
            $whereParts[] = 't.status = :status';
            $params['status'] = $filters['status'];
        }

        if (isset($filters['featured']) && $filters['featured'] !== '' && $filters['featured'] !== null) {
            $whereParts[] = 't.featured = :featured';
            $params['featured'] = (int) ((string) $filters['featured'] === '1');
        }

        if (isset($filters['service_id']) && is_numeric($filters['service_id']) && (int) $filters['service_id'] > 0) {
            $whereParts[] = 't.service_id = :service_id';
            $params['service_id'] = (int) $filters['service_id'];
        }

        if (isset($filters['gallery_id']) && is_numeric($filters['gallery_id']) && (int) $filters['gallery_id'] > 0) {
            $whereParts[] = 't.gallery_id = :gallery_id';
            $params['gallery_id'] = (int) $filters['gallery_id'];
        }

        return [implode(' AND ', $whereParts), $params];
    }
}