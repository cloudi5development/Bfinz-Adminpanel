<?php

namespace App\Jobs;

use App\Services\Alerts\AlertEvaluationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public readonly string $type) {}

    public function handle(AlertEvaluationService $service): void
    {
        $service->evaluate($this->type);
    }
}
