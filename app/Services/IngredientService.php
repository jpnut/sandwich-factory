<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class IngredientService
{
    private const CACHE_KEY_PREFIX = 'ingredient_availability_';
    private const CACHE_TTL = 3600; // 1 hour

    private array $allIngredients = [
        'bread' => [
            'white' => 'White Bread',
            'wheat' => 'Wheat Bread',
            'sourdough' => 'Sourdough',
            'rye' => 'Rye Bread',
            'ciabatta' => 'Ciabatta Roll',
            'baguette' => 'Baguette',
        ],
        'fillings' => [
            'turkey' => 'Turkey',
            'ham' => 'Ham',
            'roast-beef' => 'Roast Beef',
            'chicken' => 'Grilled Chicken',
            'tuna' => 'Tuna Salad',
            'salmon' => 'Smoked Salmon',
            'cheese' => 'Cheese',
            'lettuce' => 'Lettuce',
            'tomato' => 'Tomato',
            'onion' => 'Red Onion',
            'pickles' => 'Pickles',
            'cucumber' => 'Cucumber',
            'avocado' => 'Avocado',
            'bacon' => 'Bacon',
        ],
        'condiments' => [
            'mayo' => 'Mayonnaise',
            'mustard' => 'Mustard',
            'ketchup' => 'Ketchup',
            'ranch' => 'Ranch Dressing',
            'italian' => 'Italian Dressing',
            'oil-vinegar' => 'Oil & Vinegar',
            'hot-sauce' => 'Hot Sauce',
            'bbq' => 'BBQ Sauce',
            'honey-mustard' => 'Honey Mustard',
            'garlic-aioli' => 'Garlic Aioli',
        ],
    ];

    public function isIngredientAvailable(string $ingredient, string $type): bool
    {
        $cacheKey = $this->getCacheKey($ingredient, $type);
        
        // If not in cache, assume available (default behavior)
        return Cache::get($cacheKey, true);
    }

    public function setIngredientAvailability(string $ingredient, string $type, bool $available): void
    {
        $cacheKey = $this->getCacheKey($ingredient, $type);
        Cache::put($cacheKey, $available, self::CACHE_TTL);
    }

    public function getAvailableSubstitutions(string $ingredient, string $type): array
    {
        // Return all available ingredients of the same type (excluding the original)
        $availableIngredients = [];
        
        foreach ($this->allIngredients[$type] ?? [] as $id => $name) {
            if ($id !== $ingredient && $this->isIngredientAvailable($id, $type)) {
                $availableIngredients[] = $id;
            }
        }
        
        return $availableIngredients;
    }

    public function checkIngredientsAvailability(array $ingredients, string $type): array
    {
        $missingIngredients = [];
        
        foreach ($ingredients as $ingredient) {
            if (!$this->isIngredientAvailable($ingredient, $type)) {
                $missingIngredients[] = $ingredient;
            }
        }
        
        return $missingIngredients;
    }

    public function getSubstitutionSuggestion(string $ingredient, string $type): ?string
    {
        $suggestions = $this->getAvailableSubstitutions($ingredient, $type);
        
        // Return the first available suggestion
        return $suggestions[0] ?? null;
    }

    public function getAllIngredients(): array
    {
        return $this->allIngredients;
    }

    public function getIngredientAvailability(string $type): array
    {
        $availability = [];
        
        foreach ($this->allIngredients[$type] ?? [] as $id => $name) {
            $availability[$id] = [
                'id' => $id,
                'name' => $name,
                'available' => $this->isIngredientAvailable($id, $type),
            ];
        }
        
        return $availability;
    }

    public function getAllIngredientAvailability(): array
    {
        $allAvailability = [];
        
        foreach ($this->allIngredients as $type => $ingredients) {
            $allAvailability[$type] = $this->getIngredientAvailability($type);
        }
        
        return $allAvailability;
    }

    public function resetAllIngredientsToAvailable(): void
    {
        foreach ($this->allIngredients as $type => $ingredients) {
            foreach ($ingredients as $id => $name) {
                $this->setIngredientAvailability($id, $type, true);
            }
        }
    }

    private function getCacheKey(string $ingredient, string $type): string
    {
        return self::CACHE_KEY_PREFIX . $type . '_' . $ingredient;
    }
}
