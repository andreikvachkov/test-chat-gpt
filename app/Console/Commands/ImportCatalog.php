<?php

namespace App\Console\Commands;

use App\Services\CatalogImporter;
use Illuminate\Console\Command;

class ImportCatalog extends Command
{
    protected $signature = 'shop:import-catalog {file}';

    protected $description = 'Импорт нормализованного JSON-каталога (граница адаптера 1С)';

    public function handle(CatalogImporter $importer): int
    {
        $file = $this->argument('file');
        if (! is_readable($file)) {
            $this->error('Файл недоступен.');

            return self::FAILURE;
        }
        try {
            $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($data)) {
                throw new \InvalidArgumentException('Ожидается массив товаров.');
            }$count = $importer->import($data);
            $this->info('Обновлено товаров: '.$count);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
