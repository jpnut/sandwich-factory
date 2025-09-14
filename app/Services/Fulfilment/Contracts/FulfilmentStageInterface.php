<?php

declare(strict_types=1);

namespace App\Services\Fulfilment\Contracts;

use App\Services\Fulfilment\OrderState;

interface FulfilmentStageInterface
{
    /**
     * Execute the fulfillment stage
     */
    public function execute(OrderState $state): OrderState;

    /**
     * Get the stage name for logging and monitoring
     */
    public function getStageName(): string;

    /**
     * Get the stage description for UI display
     */
    public function getStageDescription(): string;
}
