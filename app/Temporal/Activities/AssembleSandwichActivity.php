<?php

namespace App\Temporal\Activities;

use App\Enums\FailureScenario;
use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class AssembleSandwichActivity
{
    public function __construct(
        private ScenarioManager $scenarioManager,
        private LoggerInterface $logger,
    )
    {
        //
    }

    #[ActivityMethod]
    public function assembleSandwich(OrderState $orderState): OrderState
    {
        $this->logger->info("AssembleSandwichStage: Starting for order: {$orderState->orderData->orderId}");
        
        // Mark step as in progress
        $orderState->setCurrentStep('assembling_sandwich');
        $orderState->setStepStatus('assembling_sandwich', 'in_progress', 'Starting Sandwich Assembly...');
        
        // Simulate processing
        $delayMs = $this->scenarioManager->getDelayForStage('assembling_sandwich');
        usleep($delayMs * 1000);
        
        // Check for code bug scenario first
        if ($this->scenarioManager->getActiveScenario() === FailureScenario::CODE_BUG) {
            $this->simulateCodeBug($orderState);
        }
        
        // Check for failure
        if ($this->scenarioManager->shouldFailForStage('assembling_sandwich')) {
            $this->logger->error("AssembleSandwichStage: Failing for order: {$orderState->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $orderState->setStepStatus('assembling_sandwich', 'error', 'Sandwich assembly failed - no workers available');
            

            throw new \RuntimeException('Sandwich assembly failed - no workers available');
        }
        
        // Mark as completed
        $orderState->setCurrentStep('assembling_sandwich');
        $orderState->setStepStatus('assembling_sandwich', 'completed', 'The sandwich has been expertly assembled with all requested ingredients');
        
        $this->logger->info("AssembleSandwichStage: Completed for order: {$orderState->orderData->orderId}");
        
        return $orderState;
    }

    /**
     * Simulates a division by zero bug in ingredient calculation logic
     * This represents a real bug that would need to be fixed in the code
     */
    private function simulateCodeBug(OrderState $orderState): void
    {
        $this->logger->error("PrepareIngredientsStage: Code bug triggered for order: {$orderState->orderData->orderId}");
        
        // Mark step as failed before throwing exception
        $orderState->setStepStatus('assembling_sandwich', 'error', 'Critical bug detected - division by zero in ingredient calculation');
        
        // Simulate the bug: trying to divide by zero
        $ingredientCount = 0; // This would normally be calculated from the order
        $portionsPerIngredient = 5;
        
        // This line will cause a division by zero error
        $ingredientsPerPortion = $portionsPerIngredient / $ingredientCount;
        
        // This line will never be reached due to the division by zero
        throw new \DivisionByZeroError('Division by zero in ingredient calculation logic');
    }

    #[ActivityMethod]
    public function compensateAssembleSandwich(OrderState $orderState): OrderState
    {
        $this->logger->info("Starting compensation for assemble sandwich for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'assemble_sandwich',
            'action' => 'compensation_started',
        ]);

        // First, set the status to compensation pending
        $orderState->setCompensationPending('assembling_sandwich', 'Preparing to compensate sandwich assembly...');

        // Simulate compensation
        $delayMs = $this->scenarioManager->getDelayForStage('assembling_sandwich');
        usleep($delayMs * 1000);

        $this->logger->info("Performing actual compensation for assemble sandwich for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'assemble_sandwich',
            'action' => 'compensation_executing',
        ]);

        // Reset the step status to indicate compensation completed
        $orderState->setStepStatus('assembling_sandwich', 'compensated', 'Sandwich assembly was compensated');

        // Here you would implement the actual compensation logic
        // For example:
        // - Disassemble the sandwich
        // - Return ingredients to inventory
        // - Reset assembly station allocation
        // - Update ingredient tracking

        $this->logger->info("Successfully compensated assemble sandwich for order {$orderState->orderData->orderId}");

        return $orderState;
    }
}
