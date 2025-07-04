<?php

namespace App\Services;

use App\Models\CategoryKeyword;
use App\Models\AutoResponse;
use App\Models\Category;
use Illuminate\Support\Collection;

class BotClassificationService
{
    /**
     * Classify a message based on keywords and return the best matching category.
     */
    public function classifyMessage(string $text, array $context = []): ?Category
    {
        $keywords = $this->findMatchingKeywords($text);

        if ($keywords->isEmpty()) {
            return null;
        }

        // Calculate scores for each category
        $categoryScores = [];

        foreach ($keywords as $keyword) {
            $categoryId = $keyword->category_id;
            $categoryScores[$categoryId] = ($categoryScores[$categoryId] ?? 0) + $keyword->weight;
        }

        // Get category with highest score
        $topCategoryId = array_key_first(
            array_slice(
                array_flip(
                    array_reverse(
                        arsort($categoryScores) ? $categoryScores : [], true
                    )
                ), 0, 1, true
            )
        );

        return Category::find($topCategoryId);
    }

    /**
     * Find keywords that match the given text.
     */
    public function findMatchingKeywords(string $text): Collection
    {
        $keywords = CategoryKeyword::active()->get();
        $matches = collect();

        foreach ($keywords as $keyword) {
            if ($this->keywordMatches($keyword, $text)) {
                $matches->push($keyword);
                $this->updateKeywordStats($keyword, true);
            }
        }

        return $matches;
    }

    /**
     * Find auto responses that match the given text and context.
     */
    public function findMatchingAutoResponses(string $text, array $context = []): Collection
    {
        $responses = AutoResponse::active()->orderBy('priority', 'desc')->get();
        $matches = collect();

        foreach ($responses as $response) {
            if ($this->autoResponseMatches($response, $text, $context)) {
                $matches->push($response);
            }
        }

        return $matches;
    }

    /**
     * Check if a keyword matches the given text.
     */
    protected function keywordMatches(CategoryKeyword $keyword, string $text): bool
    {
        $searchText = $keyword->is_case_sensitive ? $text : strtolower($text);
        $keywordText = $keyword->is_case_sensitive ? $keyword->keyword : strtolower($keyword->keyword);

        if ($keyword->is_exact_match) {
            return $searchText === $keywordText;
        }

        return str_contains($searchText, $keywordText);
    }

    /**
     * Check if an auto response matches the given text and context.
     */
    protected function autoResponseMatches(AutoResponse $autoResponse, string $text, array $context): bool
    {
        if (!$autoResponse->is_active) {
            return false;
        }

        if (!$this->isWithinWorkingHours($autoResponse)) {
            return false;
        }

        return match($autoResponse->trigger_type) {
            'keyword' => $this->matchesKeyword($autoResponse, $text),
            'welcome' => $context['is_welcome'] ?? false,
            'offline' => $context['is_offline'] ?? false,
            'category' => $context['category_id'] === $autoResponse->category_id,
            default => false,
        };
    }

    /**
     * Check if auto response keyword matches the text.
     */
    protected function matchesKeyword(AutoResponse $autoResponse, string $text): bool
    {
        if (!$autoResponse->trigger_keyword) {
            return false;
        }

        $text = strtolower(trim($text));
        $keyword = strtolower(trim($autoResponse->trigger_keyword));

        return str_contains($text, $keyword);
    }

    /**
     * Check if auto response is within working hours.
     */
    protected function isWithinWorkingHours(AutoResponse $autoResponse): bool
    {
        if (!$autoResponse->working_hours) {
            return true;
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        $schedule = $autoResponse->working_hours[$currentDay] ?? null;

        if (!$schedule || !$schedule['enabled']) {
            return false;
        }

        return $currentTime >= $schedule['start'] && $currentTime <= $schedule['end'];
    }

    /**
     * Update keyword statistics after a match.
     */
    public function updateKeywordStats(CategoryKeyword $keyword, bool $successful): void
    {
        $keyword->increment('match_count');

        $totalMatches = $keyword->fresh()->match_count;

        if ($totalMatches === 1) {
            $successRate = $successful ? 100.00 : 0.00;
        } else {
            $currentSuccessful = ($keyword->success_rate / 100) * ($totalMatches - 1);

            if ($successful) {
                $currentSuccessful++;
            }

            $successRate = ($currentSuccessful / $totalMatches) * 100;
        }

        $keyword->update(['success_rate' => $successRate]);
    }

    /**
     * Get the best auto response for a given context.
     */
    public function getBestAutoResponse(string $text, array $context = []): ?AutoResponse
    {
        $matches = $this->findMatchingAutoResponses($text, $context);

        if ($matches->isEmpty()) {
            return null;
        }

        // Return the highest priority match
        return $matches->sortByDesc('priority')->first();
    }

    /**
     * Update category classification confidence based on feedback.
     */
    public function updateClassificationFeedback(string $text, ?Category $predictedCategory, ?Category $actualCategory): void
    {
        $keywords = $this->findMatchingKeywords($text);

        foreach ($keywords as $keyword) {
            $successful = $predictedCategory && $actualCategory &&
                         $predictedCategory->id === $actualCategory->id &&
                         $keyword->category_id === $actualCategory->id;

            $this->updateKeywordStats($keyword, $successful);
        }
    }
}
