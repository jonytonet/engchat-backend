<?php

namespace App\Services;

use App\Models\AutoResponse;

class AutoResponseService
{
    /**
     * Process template variables in auto response text.
     */
    public function processTemplate(AutoResponse $autoResponse, array $variables = []): string
    {
        $response = $autoResponse->response_text;

        // Replace variables in response
        foreach ($variables as $key => $value) {
            $response = str_replace("{{$key}}", (string) $value, $response);
        }

        // Remove any unreplaced variables
        $response = preg_replace('/\{\{[^}]+\}\}/', '', $response);

        return trim($response);
    }

    /**
     * Increment usage count for an auto response.
     */
    public function incrementUsage(AutoResponse $autoResponse): void
    {
        $autoResponse->increment('usage_count');
    }

    /**
     * Update success rate for an auto response.
     */
    public function updateSuccessRate(AutoResponse $autoResponse, bool $successful): void
    {
        $totalUsage = $autoResponse->fresh()->usage_count;

        if ($totalUsage === 0) {
            $successRate = $successful ? 100.00 : 0.00;
        } else {
            $currentSuccessful = ($autoResponse->success_rate / 100) * $totalUsage;

            if ($successful) {
                $currentSuccessful++;
            }

            $successRate = ($currentSuccessful / $totalUsage) * 100;
        }

        $autoResponse->update(['success_rate' => $successRate]);
    }

    /**
     * Check if auto response is within working hours.
     */
    public function checkWorkingHours(AutoResponse $autoResponse): bool
    {
        if (!$autoResponse->working_hours) {
            return true; // No restriction
        }

        $now = now();
        $currentDay = strtolower($now->format('l')); // monday, tuesday, etc
        $currentTime = $now->format('H:i');

        $schedule = $autoResponse->working_hours[$currentDay] ?? null;

        if (!$schedule || !($schedule['enabled'] ?? false)) {
            return false;
        }

        return $currentTime >= ($schedule['start'] ?? '00:00') &&
               $currentTime <= ($schedule['end'] ?? '23:59');
    }

    /**
     * Get processed response with common variables.
     */
    public function getProcessedResponse(AutoResponse $autoResponse, array $context = []): string
    {
        $variables = array_merge([
            'time' => now()->format('H:i'),
            'date' => now()->format('d/m/Y'),
            'day' => now()->format('l'),
            'company_name' => config('app.name'),
        ], $context);

        return $this->processTemplate($autoResponse, $variables);
    }

    /**
     * Record auto response usage with feedback.
     */
    public function recordUsage(AutoResponse $autoResponse, bool $wasHelpful = true): void
    {
        $this->incrementUsage($autoResponse);
        $this->updateSuccessRate($autoResponse, $wasHelpful);
    }

    /**
     * Get auto response statistics.
     */
    public function getResponseStats(AutoResponse $autoResponse): array
    {
        return [
            'usage_count' => $autoResponse->usage_count,
            'success_rate' => $autoResponse->success_rate,
            'average_daily_usage' => $this->getAverageDailyUsage($autoResponse),
            'last_used' => $this->getLastUsedDate($autoResponse),
        ];
    }

    /**
     * Calculate average daily usage.
     */
    protected function getAverageDailyUsage(AutoResponse $autoResponse): float
    {
        $daysSinceCreated = max(1, $autoResponse->created_at->diffInDays(now()));
        return round($autoResponse->usage_count / $daysSinceCreated, 2);
    }

    /**
     * Get last used date (this would require a usage log table in a real implementation).
     */
    protected function getLastUsedDate(AutoResponse $autoResponse): ?string
    {
        // This would need a separate usage log table to track accurately
        // For now, return updated_at if usage_count > 0
        return $autoResponse->usage_count > 0 ? $autoResponse->updated_at?->format('d/m/Y H:i') : null;
    }

    /**
     * Validate auto response configuration.
     */
    public function validateConfiguration(AutoResponse $autoResponse): array
    {
        $errors = [];

        if (empty($autoResponse->response_text)) {
            $errors[] = 'Response text is required';
        }

        if ($autoResponse->trigger_type === 'keyword' && empty($autoResponse->trigger_keyword)) {
            $errors[] = 'Trigger keyword is required for keyword-based responses';
        }

        if ($autoResponse->delay_seconds < 0 || $autoResponse->delay_seconds > 300) {
            $errors[] = 'Delay must be between 0 and 300 seconds';
        }

        if ($autoResponse->priority < 1 || $autoResponse->priority > 10) {
            $errors[] = 'Priority must be between 1 and 10';
        }

        return $errors;
    }

    /**
     * Clone an auto response.
     */
    public function cloneResponse(AutoResponse $original, array $overrides = []): AutoResponse
    {
        $data = $original->toArray();

        // Remove unique fields
        unset($data['id'], $data['created_at'], $data['updated_at']);

        // Reset statistics
        $data['usage_count'] = 0;
        $data['success_rate'] = 0;

        // Apply overrides
        $data = array_merge($data, $overrides);

        // Ensure unique name
        if (!isset($overrides['name'])) {
            $data['name'] = $original->name . ' (Cópia)';
        }

        return AutoResponse::create($data);
    }
}
