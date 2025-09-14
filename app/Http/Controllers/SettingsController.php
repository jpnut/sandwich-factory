<?php

namespace App\Http\Controllers;

use App\Enums\FailureScenario;
use App\Enums\ImplementationType;
use App\Services\ScenarioManager;
use App\Services\ImplementationManager;
use App\Services\IngredientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private ScenarioManager $scenarioManager, 
        private ImplementationManager $implementationManager,
        private IngredientService $ingredientService
    ) {
        
    }

    public function show(): Response
    {
        return Inertia::render('Settings', [
            'scenarios' => $this->scenarioManager->getAllScenarios(),
            'activeScenario' => $this->scenarioManager->getActiveScenario()->value,
            'implementations' => $this->implementationManager->getAllImplementations(),
            'activeImplementation' => $this->implementationManager->getActiveImplementation()->value,
            'ingredients' => $this->ingredientService->getAllIngredientAvailability(),
            'delayMs' => $this->scenarioManager->getDelayMs(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activeScenario' => ['sometimes', 'string'],
            'activeImplementation' => ['sometimes', 'string'],
            'ingredientAvailability' => ['sometimes', 'array'],
            'delayMs' => ['sometimes', 'integer', 'min:100', 'max:30000'], // 100ms to 30 seconds
        ]);

        if (array_key_exists('activeScenario', $validated)) {
            try {
                $scenario = FailureScenario::from($validated['activeScenario']);
                $this->scenarioManager->setActiveScenario($scenario);
            } catch (\ValueError $e) {
                return redirect()->to(route('settings'))->withErrors(['activeScenario' => 'Invalid scenario selected']);
            }
        }

        if (array_key_exists('activeImplementation', $validated)) {
            try {
                $impl = ImplementationType::from($validated['activeImplementation']);
                $this->implementationManager->setActiveImplementation($impl);
            } catch (\ValueError $e) {
                return redirect()->to(route('settings'))->withErrors(['activeImplementation' => 'Invalid implementation selected']);
            }
        }

        if (array_key_exists('delayMs', $validated)) {
            $this->scenarioManager->setDelayMs($validated['delayMs']);
        }

        if (array_key_exists('ingredientAvailability', $validated)) {
            $this->updateIngredientAvailability($validated['ingredientAvailability']);
        }

        return redirect()->to(route('settings'))->with('success', 'Settings updated successfully');
    }

    public function updateIngredientAvailability(array $availabilityData): void
    {
        foreach ($availabilityData as $type => $ingredients) {
            foreach ($ingredients as $ingredientId => $data) {
                if (isset($data['available'])) {
                    $this->ingredientService->setIngredientAvailability(
                        $ingredientId,
                        $type,
                        (bool) $data['available']
                    );
                }
            }
        }
    }

    public function resetIngredients(): RedirectResponse
    {
        $this->ingredientService->resetAllIngredientsToAvailable();
        
        return redirect()->to(route('settings'))->with('success', 'All ingredients reset to available');
    }
}
