<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:orders {dateFrom : YYYY-MM-DD} {dateTo : YYYY-MM-DD} {page=1} {--limit=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт orders';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom');
        $dateTo = $this->argument('dateTo');
        $page = $this->argument('page');
        $limit = $this->option('limit');
        $key = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';

        $response = Http::get('http://109.73.206.144:6969/api/orders', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => $key,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $orders = $data['data'];

            if (empty($orders)) {
                $this->info("Нет данных для импорта.");
                return;
            }

            $this->processOrders($orders);
        } else {
            $error = $response->json();
            Log::error('Ошибка при импорте данных orders', ['error' => $error]);
            $this->error("Ошибка: " . $response->body());
            return;
        }
        $this->info('Импорт завершён');
    }

    protected function processOrders(array $orders)
    {
        foreach ($orders as $orderData) {
            Order::updateOrCreate(
                ['g_number' => $orderData['g_number']],
                $this->mapOrderData($orderData)
            );
        }
    }

    protected function mapOrderData(array $data): array
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
            'warehouse_name' => $data['warehouse_name'],
            'oblast' => $data['oblast'],
            'income_id' => $data['income_id'],
            'odid' => $data['odid'],
            'nm_id' => $data['nm_id'],
            'subject' => $data['subject'],
            'category' => $data['category'],
            'brand' => $data['brand'],
            'is_cancel' => $data['is_cancel'],
            'cancel_dt' => $data['cancel_dt'],
        ];
    }
}
