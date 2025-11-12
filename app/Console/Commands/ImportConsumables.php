<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Import\ConsumableImporter;

class ImportConsumables extends Command
{
    protected $signature = 'import:consumables {--file=} {--dry-run} {--limit=}';
    protected $description = 'Import historical consumable inventory from Excel workbook.';

    public function handle(): int
    {
        $file = $this->option('file') ?? storage_path('app/ETAT CONSOMATION MC.xlsx');
        $dry = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;

        $this->info(sprintf('Starting import: file=%s dry-run=%s limit=%s', $file, $dry ? 'yes' : 'no', $limit ?? 'none'));

        $importer = new ConsumableImporter();
        $result = $importer->import($file, $dry, $limit);
        $this->info('Import completed. Result:');
        if (isset($result['error'])) {
            $this->error($result['error']);
        } elseif (isset($result['summary'])) {
            $this->line(json_encode($result['summary'], JSON_PRETTY_PRINT));
        } elseif (isset($result['profile'])) {
            $this->line(json_encode($result['profile'], JSON_PRETTY_PRINT));
        } else {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        }

        return 0;
    }
}
