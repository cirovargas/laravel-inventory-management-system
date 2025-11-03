<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SaleCompleted;
use App\Jobs\UpdateInventoryJob;
use Illuminate\Contracts\Queue\ShouldQueue;

final class UpdateInventoryAfterSale implements ShouldQueue
{
    public function handle(SaleCompleted $event): void
    {
        UpdateInventoryJob::dispatch($event->sale);
    }
}
