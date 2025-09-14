<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FailureScenario;
use Illuminate\Support\Facades\Cache;

final class ScenarioManager
{
    private const CACHE_KEY = 'active-failure-scenario';
    private const DELAY_CACHE_KEY = 'scenario-delay-ms';
    private const DEFAULT_DELAY_MS = 4000; // 4 seconds

    public function getActiveScenario(): FailureScenario
    {
        $scenarioValue = Cache::get(self::CACHE_KEY, FailureScenario::NONE->value);
        
        return FailureScenario::from($scenarioValue);
    }

    public function setActiveScenario(FailureScenario $scenario): void
    {
        Cache::put(self::CACHE_KEY, $scenario->value);
    }

    public function shouldFailForStage(string $stage): bool
    {
        $scenario = $this->getActiveScenario();
        
        if ($scenario === FailureScenario::NONE) {
            return false;
        }

        $affectedStages = $scenario->getAffectedStages();
        
        if (empty($affectedStages)) {
            return false;
        }

        // For specific scenarios, check if the stage is affected
        return in_array($stage, $affectedStages);
    }

    public function getDelayForStage(string $stage): int
    {
        // Get the configured delay from cache, default to 4 seconds
        $baseDelay = $this->getDelayMs();
        
        // Add some randomness to delays for more realistic simulation
        $randomFactor = rand(80, 120) / 100; // ±20% variation
        
        return (int) ($baseDelay * $randomFactor);
    }

    public function getDelayMs(): int
    {
        return Cache::get(self::DELAY_CACHE_KEY, self::DEFAULT_DELAY_MS);
    }

    public function setDelayMs(int $delayMs): void
    {
        Cache::put(self::DELAY_CACHE_KEY, $delayMs);
    }

    public function getAllScenarios(): array
    {
        return collect(FailureScenario::cases())->map(function (FailureScenario $scenario) {
            return [
                'value' => $scenario->value,
                'title' => $scenario->getTitle(),
                'description' => $scenario->getDescription(),
                'affectedStages' => $scenario->getAffectedStages(),
                'delayMs' => $scenario->getDelayMs(),
            ];
        })->toArray();
    }

    public function reset(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::DELAY_CACHE_KEY);
    }
}
