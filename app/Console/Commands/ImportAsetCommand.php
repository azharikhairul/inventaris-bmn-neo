<?php

namespace App\Console\Commands;

use App\Services\AsetImportService;
use Illuminate\Console\Command;

class ImportAsetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aset:import {file? : Specific file path to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data aset BMN dari file Excel / CSV di folder storage';

    /**
     * Execute the console command.
     */
    public function handle(AsetImportService $importService): int
    {
        $specificFile = $this->argument('file');

        if ($specificFile) {
            if (! file_exists($specificFile)) {
                $this->error("File tidak ditemukan: {$specificFile}");

                return self::FAILURE;
            }

            $this->info("Memproses import dari {$specificFile}...");
            $res = $importService->importFile($specificFile);
            $this->info("✓ Selesai: {$res['imported']} aset berhasil diimpor, {$res['skipped']} dilewati/duplikat.");

            return self::SUCCESS;
        }

        $this->info('Memulai import seluruh data aset Excel dari storage/app/public...');
        $res = $importService->importAll();

        $this->newLine();
        $this->table(
            ['Nama File', 'Berhasil Diimpor', 'Dilewati / Duplikat'],
            array_map(fn ($f) => [$f['file'], $f['imported'], $f['skipped']], $res['files'])
        );

        $this->newLine();
        $this->info("✓ Total Aset Berhasil Diimpor: {$res['total_imported']}");
        $this->info("✓ Total Aset Dilewati: {$res['total_skipped']}");

        return self::SUCCESS;
    }
}
