<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Import\ConsumableImporter;
use App\Services\BonEntreeService;
use App\Services\BonSortieService;

class ImportConsumablesCommand extends Command
{
    protected $signature = 'import:consumables {--file= : Excel file to import} {--dry-run : Do not write to DB} {--limit= : Limit rows per sheet}';

    protected $description = 'Import consumable inventory from Excel workbook.';

    public function handle(BonEntreeService $entreeService, BonSortieService $sortieService)
    {
        $file = $this->option('file') ?: storage_path('app/ETAT CONSOMATION MC.xlsx');
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $importer = new ConsumableImporter($file, $entreeService, $sortieService);
        $this->info("Analyzing workbook: {$file}");
    $profile = $importer->analyze();
        $this->line(number_format(count($profile))." sheets detected.");

        $this->info('Starting import...');
        $result = $importer->import($file, $dryRun, $limit);

        $this->info('Import finished. Logs written to storage/app/import_logs');
        if (isset($result['summary'])) {
            $this->info('Summary: ' . json_encode($result['summary']));
        } else {
            $this->info('Profile/Result: ' . json_encode($result));
        }

        return 0;
    }
}
