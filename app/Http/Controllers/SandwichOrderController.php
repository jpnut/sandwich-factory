<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\OrderData;
use App\Services\FulfilmentService;
use App\Services\ImplementationManager;
use App\Services\WorkflowService;
use App\Enums\ImplementationType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Keepsuit\LaravelTemporal\Facade\Temporal;

class SandwichOrderController extends Controller
{
    public function __construct(
        private readonly FulfilmentService $fulfilmentService,
        private readonly ImplementationManager $implementationManager,
        private readonly WorkflowService $workflowService,
    ) {
    }

    public function show(): Response
    {
        return Inertia::render('SandwichOrder');
    }

    public function store(Request $request): Response
    {
        // Validate the request
        $request->validate([
            'bread' => 'required|string',
            'fillings' => 'required|array',
            'fillings.*' => 'string',
            'condiments' => 'required|array',
            'condiments.*' => 'string',
            'order_id' => 'required|string',
        ]);

        try {
            // Create order data from request
            $orderData = OrderData::fromRequest($request);

            // Choose implementation based on current setting
            $active = $this->implementationManager->getActiveImplementation();

            if ($active === ImplementationType::TRADITIONAL) {
                // Traditional pipeline (synchronous)
                $this->fulfilmentService->processOrderSync($orderData);
            } else {
                // Start the workflow using the WorkflowService
                $this->workflowService->startOrderWorkflow($orderData);
            }

            return Inertia::render('SandwichOrder', [
                'success' => true,
                'message' => $active === ImplementationType::TRADITIONAL
                    ? 'Your sandwich order has been placed and is being processed!'
                    : 'Temporal workflow enqueued (placeholder).',
                'order_id' => $orderData->orderId,
            ]);

        } catch (\Exception $e) {
            return Inertia::render('SandwichOrder', [
                'error' => true,
                'message' => 'There was an error processing your order: ' . $e->getMessage(),
            ]);
        }
    }
}
