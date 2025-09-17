<?php

namespace App\Console\Commands;

use App\Models\Promotion;
use Illuminate\Console\Command;

class UpdatePromotionStatus extends Command
{
    protected $signature = 'promotions:update-status';

    protected $description = 'Update promotion status based on start/end dates';

    public function handle()
    {
        // Deactivate expired promotions
        $expiredCount = Promotion::where('is_active', true)
            ->where('end_date', '<', now())
            ->update(['is_active' => false]);

        // Activate promotions that should start
        $activatedCount = Promotion::where('is_active', false)
            ->where('start_date', '<=', now())
            ->where('end_date', '>', now())
            ->update(['is_active' => true]);

        $this->info("Deactivated {$expiredCount} expired promotions");
        $this->info("Activated {$activatedCount} promotions");

        return 0;
    }
}
