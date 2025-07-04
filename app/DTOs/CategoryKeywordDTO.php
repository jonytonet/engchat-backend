<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\CategoryKeyword;

readonly class CategoryKeywordDTO
{
    public function __construct(
        public int $id,
        public int $categoryId,
        public string $keyword,
        public int $weight,
        public bool $isExactMatch,
        public bool $isCaseSensitive,
        public bool $isActive,
        public string $language,
        public int $matchCount,
        public float $successRate,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(CategoryKeyword $keyword): self
    {
        return new self(
            id: $keyword->id,
            categoryId: $keyword->category_id,
            keyword: $keyword->keyword,
            weight: $keyword->weight,
            isExactMatch: $keyword->is_exact_match,
            isCaseSensitive: $keyword->is_case_sensitive,
            isActive: $keyword->is_active,
            language: $keyword->language,
            matchCount: $keyword->match_count,
            successRate: (float) $keyword->success_rate,
            createdAt: $keyword->created_at,
            updatedAt: $keyword->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'keyword' => $this->keyword,
            'weight' => $this->weight,
            'is_exact_match' => $this->isExactMatch,
            'is_case_sensitive' => $this->isCaseSensitive,
            'is_active' => $this->isActive,
            'language' => $this->language,
            'match_count' => $this->matchCount,
            'success_rate' => $this->successRate,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
