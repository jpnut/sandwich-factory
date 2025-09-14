<?php

namespace App\Temporal\Activities;

use App\Data\IngredientSubstitutionData;
use App\Data\OrderData;
use App\Services\Fulfilment\OrderState;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class SubstituteIngredientsActivity
{
    #[ActivityMethod]
    public function substituteIngredients(IngredientSubstitutionData $substitutionData, OrderState $orderState): OrderState
    {
        $bread = $substitutionData->ingredientType === 'bread'
            ? $substitutionData->substitutedIngredient
            : $orderState->orderData->bread;

        $fillings = $substitutionData->ingredientType === 'fillings'
            ? array_replace($orderState->orderData->fillings,
                array_fill_keys(
                    array_keys($orderState->orderData->fillings, $substitutionData->originalIngredient),
                    $substitutionData->substitutedIngredient
                )
            )
            : $orderState->orderData->fillings;

        $condiments = $substitutionData->ingredientType === 'condiments'
            ? array_replace($orderState->orderData->condiments,
                array_fill_keys(
                    array_keys($orderState->orderData->condiments, $substitutionData->originalIngredient),
                    $substitutionData->substitutedIngredient
                )
            )
            : $orderState->orderData->condiments;

        $orderData = new OrderData(
            bread: $bread,
            fillings: $fillings,
            condiments: $condiments,
            orderId: $orderState->orderData->orderId,
        );

        $orderState->orderData = $orderData;

        return $orderState;
    }
}