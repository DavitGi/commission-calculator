<?php

namespace App\Console\Commands;

use App\Services\CommissionService;
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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CommissionService $service)
    {
        $csvFile = $this->argument('file');
        $operations = $this->getAllOperationFromCsv($csvFile);

        try {
            foreach ($operations as $operation) {
                $this->line($service->calculateCommission($operation, $operations));
            };
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }

    protected function getAllOperationFromCsv($csvFile): array
    {
        $filePath = storage_path($csvFile);
        $file = fopen($filePath, 'r');

        $header = [
            'date',
            'client_id',
            'client_type',
            'operation_type',
            'amount',
            'currency',
            'operation_id',
        ];

        $operations = [];

        $operationId = 0;

        while ($row = fgetcsv($file)) {
            $row[] = $operationId;
            $operations[] = array_combine($header, $row);
            $operationId++;
        }

        fclose($file);

        return $operations;
    }
}
