<?php

declare(strict_types=1);

namespace App\Events;

use App\Services\Fulfilment\OrderState;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly OrderState $orderState,
        public readonly string $step,
        public readonly string $status,
        public readonly ?string $message = null,
    ) {
    }

    public function broadcastAs(): string
    {
        return 'order-status-update';
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
            'step' => $this->step,
            'status' => $this->status,
            'message' => $this->message,
            'current_step' => $this->orderState->getCurrentStep(),
            'step_statuses' => $this->orderState->getAllStepStatuses(),
            'is_completed' => $this->orderState->isCompleted(),
            'error' => $this->orderState->getError(),
            'order_data' => [
                'bread' => $this->orderState->orderData->bread,
                'fillings' => $this->orderState->orderData->fillings,
                'condiments' => $this->orderState->orderData->condiments,
            ],
        ];
    }
}
