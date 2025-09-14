<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ImplementationType;
use Illuminate\Support\Facades\Cache;

final class ImplementationManager
{
    private const CACHE_KEY = 'active-implementation';

    public function getActiveImplementation(): ImplementationType
    {
        $value = Cache::get(self::CACHE_KEY, ImplementationType::TRADITIONAL->value);
        return ImplementationType::from($value);
    }

    public function setActiveImplementation(ImplementationType $implementation): void
    {
        Cache::put(self::CACHE_KEY, $implementation->value);
    }

    /**
     * @return array<int, array{value:string,title:string,description:string}>
     */
    public function getAllImplementations(): array
    {
        return collect(ImplementationType::cases())->map(static function (ImplementationType $impl) {
            return [
                'value' => $impl->value,
                'title' => $impl->getTitle(),
                'description' => $impl->getDescription(),
            ];
        })->toArray();
    }

    public function reset(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}


