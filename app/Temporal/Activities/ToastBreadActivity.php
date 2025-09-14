<?php

namespace App\Temporal\Activities;

use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class ToastBreadActivity
{
    public function __construct(
        private ScenarioManager $scenarioManager,
        private LoggerInterface $logger,
    )
    {
        //
    }

    #[ActivityMethod]
    public function toastBread(OrderState $orderState): OrderState
    {
        $this->logger->info("ToastBreadStage: Starting for order: {$orderState->orderData->orderId}");
        
        // Mark step as in progress
        $orderState->setCurrentStep('toasting_bread');
        $orderState->setStepStatus('toasting_bread', 'in_progress', 'Starting Bread Toasting...');
        
        // Simulate processing
        $delayMs = $this->scenarioManager->getDelayForStage('toasting_bread');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($this->scenarioManager->shouldFailForStage('toasting_bread')) {
            $this->logger->error("ToastBreadStage: Failing for order: {$orderState->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $orderState->setStepStatus('toasting_bread', 'error', 'Bread toasting failed - toaster equipment malfunction');
            

            throw new \RuntimeException('Bread toasting failed - toaster equipment malfunction');
        }
        
        // Mark as completed
        $orderState->setCurrentStep('toasting_bread');
        $orderState->setStepStatus('toasting_bread', 'completed', 'The bread has been perfectly toasted and is ready for assembly');

        $this->logger->info("ToastBreadStage: Completed for order: {$orderState->orderData->orderId}");
        
        return $orderState;
    }

    #[ActivityMethod]
    public function compensateToastBread(OrderState $orderState): OrderState
    {
        $this->logger->info("Starting compensation for toast bread for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'toast_bread',
            'action' => 'compensation_started',
        ]);

        // First, set the status to compensation pending
        $orderState->setCompensationPending('toasting_bread', 'Preparing to compensate bread toasting...');

        // Simulate compensation
        $delayMs = $this->scenarioManager->getDelayForStage('toasting_bread');
        usleep($delayMs * 1000);

        $this->logger->info("Performing actual compensation for toast bread for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'toast_bread',
            'action' => 'compensation_executing',
        ]);

        // Reset the step status to indicate compensation completed
        $orderState->setStepStatus('toasting_bread', 'compensated', 'Bread toasting was compensated');

        // Here you would implement the actual compensation logic
        // For example:
        // - Reset toaster allocation
        // - Return bread to inventory
        // - Update equipment status

        $this->logger->info("Successfully compensated toast bread for order {$orderState->orderData->orderId}");

        return $orderState;
    }
}
