<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\OrderData;
use App\Services\Fulfilment\OrderState;
use App\Services\Fulfilment\SandwichFulfilmentPipeline;

final class FulfilmentService
{
    public function __construct(
        private readonly SandwichFulfilmentPipeline $pipeline,
    ) {
    }

    public function processOrderSync(OrderData $order): OrderState
    {
        // Use the new pipeline-based fulfillment process with the provided order ID
        return $this->pipeline->processOrder($order);
    }
}