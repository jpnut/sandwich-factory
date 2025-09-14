<?php

namespace App\Temporal\Activities;

use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class QualityCheckActivity
{
    public function __construct(
        private ScenarioManager $scenarioManager,
        private LoggerInterface $logger,
    )
    {
        //
    }

    #[ActivityMethod]
    public function qualityCheck(OrderState $orderState): OrderState
    {
        $this->logger->info("QualityCheckStage: Starting for order: {$orderState->orderData->orderId}");
        
        // Mark step as in progress
        $orderState->setCurrentStep('quality_check');
        $orderState->setStepStatus('quality_check', 'in_progress', 'Starting Quality Check...');
        
        // Simulate processing
        $delayMs = $this->scenarioManager->getDelayForStage('quality_check');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($this->scenarioManager->shouldFailForStage('quality_check')) {
            $this->logger->error("QualityCheckStage: Failing for order: {$orderState->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $orderState->setStepStatus('quality_check', 'error', 'Quality check failed - sandwich does not meet standards');
            

            throw new \RuntimeException('Quality check failed - sandwich does not meet standards');
        }
        
        // Mark as completed
        $orderState->setCurrentStep('quality_check');
        $orderState->setStepStatus('quality_check', 'completed', 'The sandwich has passed all quality standards and is ready for delivery');
        
        $this->logger->info("QualityCheckStage: Completed for order: {$orderState->orderData->orderId}");
        
        return $orderState;
    }

    #[ActivityMethod]
    public function compensateQualityCheck(OrderState $orderState): OrderState
    {
        $this->logger->info("Starting compensation for quality check for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'quality_check',
            'action' => 'compensation_started',
        ]);

        // First, set the status to compensation pending
        $orderState->setCompensationPending('quality_check', 'Preparing to compensate quality check...');

        // Simulate compensation
        $delayMs = $this->scenarioManager->getDelayForStage('quality_check');
        usleep($delayMs * 1000);

        $this->logger->info("Performing actual compensation for quality check for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'quality_check',
            'action' => 'compensation_executing',
        ]);

        // Reset the step status to indicate compensation completed
        $orderState->setStepStatus('quality_check', 'compensated', 'Quality check was compensated');

        // Here you would implement the actual compensation logic
        // For example:
        // - Reset quality check station allocation
        // - Update quality metrics
        // - Reset any quality flags

        $this->logger->info("Successfully compensated quality check for order {$orderState->orderData->orderId}");

        return $orderState;
    }
}
