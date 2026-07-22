<?php

namespace App\Services;

use App\Models\WmsTask;
use Illuminate\Support\Collection;

class WavePickingEngine
{
    /**
     * Groups WmsTasks into picking waves by SKU and source zone locations to optimize picking passes.
     */
    public function generateWave(Collection $tasks): Collection
    {
        return $tasks->groupBy(function (WmsTask $task) {
            $locationType = $task->sourceLocation ? $task->sourceLocation->type : 'UNKNOWN';

            return "{$task->sku}-{$locationType}";
        });
    }
}
