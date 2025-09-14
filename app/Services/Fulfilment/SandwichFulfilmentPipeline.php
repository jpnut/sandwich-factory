<?php

declare(strict_types=1);

namespace App\Services\Fulfilment;

use App\Data\OrderData;
use App\Events\OrderStatusUpdate;
use App\Services\Fulfilment\PipelineStages\AssembleSandwichStage;
use App\Services\Fulfilment\PipelineStages\DeliverOrderStage;
use App\Services\Fulfilment\PipelineStages\PackageSandwichStage;
use App\Services\Fulfilment\PipelineStages\PrepareIngredientsStage;
use App\Services\Fulfilment\PipelineStages\QualityCheckStage;
use App\Services\Fulfilment\PipelineStages\ToastBreadStage;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

final class SandwichFulfilmentPipeline
{
    public function __construct(
        private readonly Pipeline $pipeline,
    ) {
    }

    public function processOrder(OrderData $orderData): OrderState
    {
        // Create initial state with the provided order ID
        $state = new OrderState($orderData);

        // Broadcast initial status
        event(new OrderStatusUpdate($state, 'initialized', 'initialized', 'Order processing initialized'));

        try {
            // Process through all pipeline stages
            Log::info("Starting pipeline processing for order: {$orderData->orderId}");
            
            $finalState = $this->pipeline
                ->send($state)
                ->through([
                    PrepareIngredientsStage::class,
                    ToastBreadStage::class,
                    AssembleSandwichStage::class,
                    PackageSandwichStage::class,
                    QualityCheckStage::class,
                    DeliverOrderStage::class,
                ])
                ->thenReturn();

            Log::info("Pipeline processing completed for order: {$orderData->orderId}");

            return $finalState;

        } catch (\Exception $e) {
            Log::error("Pipeline processing failed for order: {$orderData->orderId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Handle any errors that occur during processing
            $state->setError($e->getMessage());

            throw $e;
        }
    }

    /**
     * Get the current status of an order
     */
    public function getOrderStatus(string $orderId): ?array
    {
        // In a real implementation, this would fetch from a database
        // For now, we'll return null as this is just a demo
        return null;
    }
}
