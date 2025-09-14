<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\OrderData;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use App\Temporal\Workflows\ProcessOrderWorkflow;
use App\Data\IngredientSubstitutionData;
use Temporal\Common\RetryOptions;

final class WorkflowService
{
    private const WORKFLOW_ID_CACHE_PREFIX = 'workflow_id_';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Start a new workflow for an order and store the workflow ID
     */
    public function startOrderWorkflow(OrderData $orderData): string
    {
        // Generate a unique workflow ID for this order
        $workflowId = "sandwich-order-{$orderData->orderId}";
        
        $workflow = Temporal::newWorkflow()
            ->withWorkflowId($workflowId)
            ->withRetryOptions(
                RetryOptions::new()
                    ->withMaximumAttempts(1)
            )
            ->build(ProcessOrderWorkflow::class);

        Temporal::workflowClient()->start($workflow, $orderData);
        
        // Store the workflow ID in cache for later signal communication
        $this->storeWorkflowId($orderData->orderId, $workflowId);
        
        return $workflowId;
    }

    /**
     * Store workflow ID in cache
     */
    private function storeWorkflowId(string $orderId, string $workflowId): void
    {
        $cacheKey = self::WORKFLOW_ID_CACHE_PREFIX . $orderId;
        cache()->put($cacheKey, $workflowId, self::CACHE_TTL);
    }

    /**
     * Get workflow ID from cache
     */
    public function getWorkflowId(string $orderId): ?string
    {
        $cacheKey = self::WORKFLOW_ID_CACHE_PREFIX . $orderId;
        return cache()->get($cacheKey);
    }

    /**
     * Remove workflow ID from cache (useful for cleanup)
     */
    public function removeWorkflowId(string $orderId): void
    {
        $cacheKey = self::WORKFLOW_ID_CACHE_PREFIX . $orderId;
        cache()->forget($cacheKey);
    }

    /**
     * Send a substitution signal to a workflow
     */
    public function sendSubstitutionSignal(string $orderId, IngredientSubstitutionData $substitutionData): void
    {
        Temporal::workflowClient()
            ->newRunningWorkflowStub(
                class: ProcessOrderWorkflow::class,
                workflowID: $this->getWorkflowId($orderId)
            )
            ->addIngredientSubstitution($substitutionData);
    }
}
