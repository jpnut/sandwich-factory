<?php

declare(strict_types=1);

namespace App\Events;

use App\Services\Fulfilment\OrderState;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IngredientSubstitutionRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly OrderState $orderState,
        public readonly string $missingIngredient,
        public readonly string $ingredientType, // 'bread', 'filling', 'condiment'
        public readonly array $availableSubstitutions = [],
    ) {
    }

    public function broadcastAs(): string
    {
        return 'ingredient-substitution-request';
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("order.{$this->orderState->orderData->orderId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderState->orderData->orderId,
            'missing_ingredient' => $this->missingIngredient,
            'ingredient_type' => $this->ingredientType,
            'available_substitutions' => $this->availableSubstitutions,
            'current_step' => $this->orderState->getCurrentStep(),
            'step_statuses' => $this->orderState->getAllStepStatuses(),
            'order_data' => [
                'bread' => $this->orderState->orderData->bread,
                'fillings' => $this->orderState->orderData->fillings,
                'condiments' => $this->orderState->orderData->condiments,
            ],
        ];
    }
}
