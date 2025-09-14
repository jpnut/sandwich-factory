<?php

namespace App\Temporal\Workflows;

use App\Data\OrderData;
use App\Data\IngredientSubstitutionData;
use App\Exceptions\OrderDeliveryException;
use App\Services\Fulfilment\OrderState;
use App\Temporal\Activities\AssembleSandwichActivity;
use App\Temporal\Activities\DeliverOrderActivity;
use App\Temporal\Activities\PackageSandwhichActivity;
use App\Temporal\Activities\PrepareIngredientsActivity;
use App\Temporal\Activities\QualityCheckActivity;
use App\Temporal\Activities\SubstituteIngredientsActivity;
use App\Temporal\Activities\ToastBreadActivity;
use Temporal\Workflow\Saga;
use Illuminate\Support\Facades\Log;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Temporal\Common\RetryOptions;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;
use Temporal\Workflow\SignalMethod;
use Throwable;

#[WorkflowInterface]
class ProcessOrderWorkflow
{
    private OrderState $orderState;
    private ?IngredientSubstitutionData $pendingSubstitutions = null;

    public function __construct()
    {
        //
    }

    #[WorkflowMethod]
    public function handle(OrderData $orderData): \Generator
    {
        $this->orderState = new OrderState($orderData);
        
        // Initialize the saga for managing compensation
        $saga = new Saga();
        $saga->setContinueWithError(true); // Continue compensation even if one step fails

        try {
            // Handle ingredient preparation with potential substitutions
            while (true) {
                $prepareIngredients = Temporal::newActivity()
                    ->withStartToCloseTimeout(\DateInterval::createFromDateString('5 minutes'))
                    ->build(PrepareIngredientsActivity::class);
                
                // Add compensation for prepare ingredients
                $saga->addCompensation(fn () => $this->orderState = yield $prepareIngredients->compensatePrepareIngredients($this->orderState));

                $this->orderState = yield $prepareIngredients->prepareIngredients($this->orderState);

                if (! $this->orderState->isMissingIngredients()) {
                    break;
                }

                yield Workflow::await(fn () => !empty($this->pendingSubstitutions));

                // Apply the substitution
                $substituteIngredients = Temporal::newActivity()
                    ->withStartToCloseTimeout(\DateInterval::createFromDateString('5 minutes'))
                    ->build(SubstituteIngredientsActivity::class);

                $this->orderState = yield $substituteIngredients->substituteIngredients($this->pendingSubstitutions, $this->orderState);

                // Clear the pending substitution
                $this->pendingSubstitutions = null;
                
                // Continue the loop to try preparation again with new ingredients
                continue;
            }

            // Toast bread step
            $toastBread = Temporal::newActivity()
                ->withStartToCloseTimeout(\DateInterval::createFromDateString('5 minutes'))
                ->build(ToastBreadActivity::class);
            
            // Add compensation for toast bread
            $saga->addCompensation(fn () => $this->orderState = yield $toastBread->compensateToastBread($this->orderState));

            $this->orderState = yield $toastBread->toastBread($this->orderState);

            // Assemble sandwich step
            $assembleSandwich = Temporal::newActivity()
                ->withStartToCloseTimeout(\DateInterval::createFromDateString('5 minutes'))
                ->build(AssembleSandwichActivity::class);
            
            // Add compensation for assemble sandwich
            $saga->addCompensation(fn () => $this->orderState = yield $assembleSandwich->compensateAssembleSandwich($this->orderState));

            $this->orderState = yield $assembleSandwich->assembleSandwich($this->orderState);

            // Package sandwich step
            $packageSandwich = Temporal::newActivity()
                ->withStartToCloseTimeout(\DateInterval::createFromDateString('5 minutes'))
                ->build(PackageSandwhichActivity::class);
            
            // Add compensation for package sandwich
            $saga->addCompensation(fn () => $this->orderState = yield $packageSandwich->compensatePackageSandwich($this->orderState));

            $this->orderState = yield $packageSandwich->packageSandwich($this->orderState);

            // Quality check step
            $qualityCheck = Temporal::newActivity()
                ->withStartToCloseTimeout(\DateInterval::createFromDateString('5 minutes'))
                ->build(QualityCheckActivity::class);
            
            // Add compensation for quality check
            $saga->addCompensation(fn () => $this->orderState = yield $qualityCheck->compensateQualityCheck($this->orderState));

            $this->orderState = yield $qualityCheck->qualityCheck($this->orderState);

            // Deliver order step
            $deliverOrder = Temporal::newActivity()
                ->withStartToCloseTimeout(\DateInterval::createFromDateString('5 minutes'))
                ->withRetryOptions(
                    RetryOptions::new()
                        ->withNonRetryableExceptions([OrderDeliveryException::class])
                )
                ->build(DeliverOrderActivity::class);
            
            // Add compensation for deliver order
            $saga->addCompensation(fn () => $this->orderState = yield $deliverOrder->compensateDeliverOrder($this->orderState));

            $this->orderState = yield $deliverOrder->deliverOrder($this->orderState);

            // Mark the order as completed
            $this->orderState->setCompleted(true);
            
            Log::info("Order processing completed successfully for order {$orderData->orderId}");

        } catch (Throwable $e) {
            Log::error("Order processing failed for order {$orderData->orderId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Set error state
            $this->orderState->setError('Order processing failed terminally :(');
            
            // Trigger saga compensation
            yield $saga->compensate();
            
            // Re-throw the exception to ensure the workflow fails
            throw $e;
        }
    }

    #[SignalMethod]
    public function addIngredientSubstitution(IngredientSubstitutionData $substitutionData): void
    {
        // Add the substitution to our pending list
        $this->pendingSubstitutions = $substitutionData;
    }
}
