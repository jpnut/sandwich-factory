<?php

declare(strict_types=1);

namespace App\Enums;

enum ImplementationType: string
{
    case TRADITIONAL = 'traditional';
    case TEMPORAL = 'temporal';

    public function getTitle(): string
    {
        return match ($this) {
            self::TRADITIONAL => 'Traditional (Pipeline) - Synchronous',
            self::TEMPORAL => 'Temporal (Workflows)',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::TRADITIONAL => 'Process orders using a Laravel pipeline synchronously within the request.',
            self::TEMPORAL => 'Process orders using Temporal workflows and activities (durable, async).',
        };
    }
}


