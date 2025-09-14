<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\Request;

final readonly class IngredientSubstitutionData
{
    public function __construct(
        public string $orderId,
        public string $originalIngredient,
        public string $substitutedIngredient,
        public string $ingredientType, // 'bread', 'filling', 'condiment'
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            orderId: (string) $request->string('order_id'),
            originalIngredient: (string) $request->string('original_ingredient'),
            substitutedIngredient: (string) $request->string('substituted_ingredient'),
            ingredientType: (string) $request->string('ingredient_type'),
        );
    }
}
