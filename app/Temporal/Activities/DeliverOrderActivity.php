<?php

namespace App\Temporal\Activities;

use App\Exceptions\OrderDeliveryException;
use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class DeliverOrderActivity
{
    public function __construct(
        private ScenarioManager $scenarioManager,
        private LoggerInterface $logger,
    )
    {
        //
    }

    #[ActivityMethod]
    public function deliverOrder(OrderState $orderState): OrderState
    {
        $this->logger->info("DeliverOrderStage: Starting for order: {$orderState->orderData->orderId}");
        
        // Mark step as in progress
        $orderState->setCurrentStep('delivery');
        $orderState->setStepStatus('delivery', 'in_progress', 'Starting Order Delivery...');
        
        // Simulate processing
        $delayMs = $this->scenarioManager->getDelayForStage('delivery');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($this->scenarioManager->shouldFailForStage('delivery')) {
            $this->logger->error("DeliverOrderStage: Failing for order: {$orderState->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $orderState->setStepStatus('delivery', 'error', 'Delivery failed - network connectivity issues');
            
            throw new OrderDeliveryException('Delivery failed - network connectivity issues');
        }
        
        // Mark as completed
        $orderState->setCurrentStep('delivery');
        $orderState->setStepStatus('delivery', 'completed', 'Your delicious sandwich has been successfully delivered!');
        $orderState->setCompleted(true);
        
        $this->logger->info("DeliverOrderStage: Completed for order: {$orderState->orderData->orderId}");
        
        return $orderState;
    }

    #[ActivityMethod]
    public function compensateDeliverOrder(OrderState $orderState): OrderState
    {
        $this->logger->info("Starting compensation for deliver order for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'deliver_order',
            'action' => 'compensation_started',
        ]);

        // First, set the status to compensation pending
        $orderState->setCompensationPending('delivery', 'Preparing to compensate order delivery...');

        // Simulate compensation
        $delayMs = $this->scenarioManager->getDelayForStage('delivery');
        usleep($delayMs * 1000);

        $this->logger->info("Performing actual compensation for deliver order for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'deliver_order',
            'action' => 'compensation_executing',
        ]);

        // Reset the step status to indicate compensation completed
        $orderState->setStepStatus('delivery', 'compensated', 'Order delivery was compensated');

        // Here you would implement the actual compensation logic
        // For example:
        // - Cancel delivery driver allocation
        // - Return order to pickup location
        // - Update delivery tracking
        // - Reset delivery status

        $this->logger->info("Successfully compensated deliver order for order {$orderState->orderData->orderId}");

        return $orderState;
    }
}
