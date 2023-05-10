<?php

namespace App\Console\Commands;

use App\Services\Commission\CommissionService;
use Illuminate\Console\Command;

class CommissionCalculateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:fee {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate commission fee for given csv file';

    /**
     * Execute the console command.
     */
    public function handle(CommissionService $service)
    {
        $csvFile = $this->argument('file');
        try {
            $operations = $service->getAllOperationFromCsv($csvFile);

            foreach ($operations as $operation) {
                $this->line($service->calculateCommission($operation, $operations));
            };
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
