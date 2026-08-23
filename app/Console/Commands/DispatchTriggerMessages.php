<?php

namespace App\Console\Commands;

use App\Services\Messaging\TriggerMessageDispatcher;
use Illuminate\Console\Command;

class DispatchTriggerMessages extends Command
{
    protected $signature = 'messaging:dispatch-triggers {--limit=50}';

    protected $description = 'Dispatch due transactional appointment messages using configured channel priority';

    public function handle(TriggerMessageDispatcher $dispatcher): int
    {
        $count = $dispatcher->processDue(max(1, min(500, (int) $this->option('limit'))));
        $this->info("Processed {$count} trigger message(s).");

        return self::SUCCESS;
    }
}
