<?php

namespace App\Temporal\Activities;

use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class PackageSandwhichActivity
{
    public function __construct(
        private ScenarioManager $scenarioManager,
        private LoggerInterface $logger,
    )
    {
        //
    }

    #[ActivityMethod]
    public function packageSandwich(OrderState $orderState): OrderState
    {
        $this->logger->info("PackageSandwichStage: Starting for order: {$orderState->orderData->orderId}");
        
        // Mark step as in progress
        $orderState->setCurrentStep('packaging');
        $orderState->setStepStatus('packaging', 'in_progress', 'Starting Sandwich Packaging...');
        
        // Simulate processing
        $delayMs = $this->scenarioManager->getDelayForStage('packaging');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($this->scenarioManager->shouldFailForStage('packaging')) {
            $this->logger->error("PackageSandwichStage: Failing for order: {$orderState->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $orderState->setStepStatus('packaging', 'error', 'Packaging failed - no packaging materials available');
            

            throw new \RuntimeException('Packaging failed - no packaging materials available');
        }
        
        // Mark as completed
        $orderState->setCurrentStep('packaging');
        $orderState->setStepStatus('packaging', 'completed', 'The sandwich has been properly packaged and is ready for quality check');
        
        $this->logger->info("PackageSandwichStage: Completed for order: {$orderState->orderData->orderId}");
        
        return $orderState;
    }

    #[ActivityMethod]
    public function compensatePackageSandwich(OrderState $orderState): OrderState
    {
        $this->logger->info("Starting compensation for package sandwich for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'package_sandwich',
            'action' => 'compensation_started',
        ]);

        // First, set the status to compensation pending
        $orderState->setCompensationPending('packaging', 'Preparing to compensate sandwich packaging...');

        // Simulate compensation
        $delayMs = $this->scenarioManager->getDelayForStage('packaging');
        usleep($delayMs * 1000);

        $this->logger->info("Performing actual compensation for package sandwich for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'package_sandwich',
            'action' => 'compensation_executing',
        ]);

        // Reset the step status to indicate compensation completed
        $orderState->setStepStatus('packaging', 'compensated', 'Sandwich packaging was compensated');

        // Here you would implement the actual compensation logic
        // For example:
        // - Unpackage the sandwich
        // - Return packaging materials to inventory
        // - Reset packaging station allocation

        $this->logger->info("Successfully compensated package sandwich for order {$orderState->orderData->orderId}");

        return $orderState;
    }
}
