<?php

namespace App\Data;

use Illuminate\Http\Request;

final readonly class OrderData
{
    public function __construct(
        public string $bread,
        public array $fillings,
        public array $condiments,
        public string $orderId,
    )
    {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            bread: $request->string('bread'),
            fillings: $request->array('fillings'),
            condiments: $request->array('condiments'),
            orderId: $request->string('order_id'),
        );
    }
}