<?php

namespace App\Console\Commands;

use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:sales {dateFrom : YYYY-MM-DD} {dateTo : YYYY-MM-DD} {page=1} {--limit=500}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт sales';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom');
        $dateTo = $this->argument('dateTo');
        $page = $this->argument('page');
        $limit = $this->option('limit');
        $key = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';

        $response = Http::get('http://109.73.206.144:6969/api/sales', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => $key,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $sales = $data['data'];

            if (empty($sales)) {
                $this->info("Нет данных для импорта.");
                return;
            }

            $this->processSales($sales);
        } else {
            $error = $response->json();
            Log::error('Ошибка при импорте данных', ['error' => $error]);
            $this->error("Ошибка: " . $response->body());
            return;
        }
        $this->info('Импорт завершён');
    }

    protected function processSales(array $sales)
    {
        foreach ($sales as $saleData) {
            Sale::updateOrCreate(
                ['sale_id' => $saleData['sale_id']],
                $this->mapSaleData($saleData)
            );
        }
    }

    protected function mapSaleData(array $data): array
    {
        return [
            'g_number' => $data['g_number'],
            'date' => $data['date'],
            'last_change_date' => $data['last_change_date'],
            'supplier_article' => $data['supplier_article'],
            'tech_size' => $data['tech_size'],
            'barcode' => $data['barcode'],
            'total_price' => $data['total_price'],
            'discount_percent' => $data['discount_percent'],
            'is_supply' => $data['is_supply'],
            'is_realization' => $data['is_realization'],
            'promo_code_discount' => $data['promo_code_discount'],
            'warehouse_name' => $data['warehouse_name'],
            'country_name' => $data['country_name'],
            'oblast_okrug_name' => $data['oblast_okrug_name'],
            'region_name' => $data['region_name'],
            'income_id' => $data['income_id'],
            'sale_id' => $data['sale_id'],
            'odid' => $data['odid'],
            'spp' => $data['spp'],
            'for_pay' => $data['for_pay'],
            'finished_price' => $data['finished_price'],
            'price_with_disc' => $data['price_with_disc'],
            'nm_id' => $data['nm_id'],
            'subject' => $data['subject'],
            'category' => $data['category'],
            'brand' => $data['brand'],
            'is_storno' => $data['is_storno'],
        ];
    }
}
