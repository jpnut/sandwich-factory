<?php

declare(strict_types=1);

namespace App\Enums;

enum FailureScenario: string
{
    case NONE = 'none';
    case EQUIPMENT_FAILURE = 'equipment_failure';
    case CODE_BUG = 'code_bug';
    case DELIVERY_FAILURE = 'delivery_failure';

    public function getTitle(): string
    {
        return match ($this) {
            self::NONE => 'Normal Operation',
            self::EQUIPMENT_FAILURE => 'Equipment Failure',
            self::CODE_BUG => 'Code Bug',
            self::DELIVERY_FAILURE => 'Delivery Failure',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::NONE => 'All services operate normally without any failures or delays',
            self::EQUIPMENT_FAILURE => 'Simulates toaster malfunction during bread toasting',
            self::CODE_BUG => 'Simulates a division by zero bug in the sandwich assembly logic',
            self::DELIVERY_FAILURE => 'Simulates delivery failure due to network connectivity issues',
        };
    }

    public function getAffectedStages(): array
    {
        return match ($this) {
            self::NONE => [],
            self::EQUIPMENT_FAILURE => ['toasting_bread'],
            self::CODE_BUG => ['assembling_sandwich'],
            self::DELIVERY_FAILURE => ['delivery'],
        };
    }

    public function getDelayMs(): int
    {
        return match ($this) {
            default => 5_000,
        };
    }
}
