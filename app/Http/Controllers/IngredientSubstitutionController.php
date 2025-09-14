<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\IngredientSubstitutionData;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class IngredientSubstitutionController extends Controller
{
    public function __construct(
        private readonly WorkflowService $workflowService,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $substitutionData = IngredientSubstitutionData::fromRequest($request);
        
        // Validate the substitution
        if (!$this->validateSubstitution($substitutionData)) {
            return redirect()->back()->withErrors([
                'message' => 'Invalid substitution request'
            ]);
        }
        
        try {
            // Send the substitution signal using the WorkflowService
            $this->workflowService->sendSubstitutionSignal($substitutionData->orderId, $substitutionData);
            
            return redirect()->back()->with('success', 'Substitution request processed successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'message' => 'Failed to process substitution: ' . $e->getMessage()
            ]);
        }
    }
    
    private function validateSubstitution(IngredientSubstitutionData $substitutionData): bool
    {
        // Basic validation - in a real app, you'd check against available ingredients
        $validTypes = ['bread', 'fillings', 'condiments'];
        
        if (!in_array($substitutionData->ingredientType, $validTypes)) {
            return false;
        }
        
        if (empty($substitutionData->substitutedIngredient)) {
            return false;
        }
        
        return true;
    }
}
