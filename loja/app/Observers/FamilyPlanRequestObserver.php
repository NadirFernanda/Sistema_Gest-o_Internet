<?php

namespace App\Observers;

use App\Models\FamilyPlanRequest;
use App\Models\AutovendaOrder;
use App\Models\SiteStat;

class FamilyPlanRequestObserver
{
    public function updated(FamilyPlanRequest $request)
    {
        if ($request->isDirty('status') && $request->status === FamilyPlanRequest::STATUS_ACTIVATED) {
            $this->updateActiveClientsStat();
        }
    }

    protected function updateActiveClientsStat()
    {
        $activeFamily = FamilyPlanRequest::where('status', FamilyPlanRequest::STATUS_ACTIVATED)->count();
        $activeIndividual = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)->count();
        $total = $activeFamily + $activeIndividual;
        $stat = SiteStat::where('legenda', 'Clientes activos')->first();
        if ($stat) {
            $stat->valor = number_format($total, 0, ',', '.') . '+';
            $stat->count_to = $total;
            $stat->save();
        }
    }
}
