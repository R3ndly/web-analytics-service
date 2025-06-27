<?php

namespace App\Console\Commands;

use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:stocks {dateFrom : YYYY-MM-DD} {dateTo : YYYY-MM-DD} {page=1} {--limit=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'импорт stocks';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom');
        $dateTo = $this->argument('dateTo');
        $page = $this->argument('page');
        $limit = $this->option('limit');
        $key = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';

        $response = Http::get('http://109.73.206.144:6969/api/stocks', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => $key,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $stocks = $data['data'];

            if (empty($stocks)) {
                $this->info("Нет данных для импорта.");
                return;
            }

            $this->processStocks($stocks);
        } else {
            $error = $response->json();
            Log::error('Ошибка при импорте данных в stock', ['error' => $error]);
            $this->error("Ошибка: " . $response->body());
            return;
        }
        $this->info('Импорт завершён');
    }

    protected function processStocks(array $stocks)
    {
        foreach ($stocks as $stockData) {
            Stock::create($this->mapStockData($stockData));
        }
    }

    protected function mapStockData(array $data): array
    {
        return [
            'date' => $data['date'],
            'last_change_date' => $data['last_change_date'],
            'supplier_article' => $data['supplier_article'],
            'tech_size' => $data['tech_size'],
            'barcode' => $data['barcode'],
            'quantity' => $data['quantity'],
            'is_supply' => $data['is_supply'],
            'is_realization' => $data['is_realization'],
            'quantity_full' => $data['quantity_full'],
            'warehouse_name' => $data['warehouse_name'],
            'in_way_to_client' => $data['in_way_to_client'],
            'in_way_from_client' => $data['in_way_from_client'],
            'nm_id' => $data['nm_id'],
            'subject' => $data['subject'],
            'category' => $data['category'],
            'brand' => $data['brand'],
            'sc_code' => $data['sc_code'],
            'price' => $data['price'],
            'discount' => $data['discount']
        ];
    }
}
