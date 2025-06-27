<?php

namespace App\Console\Commands;

use App\Models\Income;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:incomes {dateFrom : YYYY-MM-DD} {dateTo : YYYY-MM-DD} {page=1} {--limit=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт Incomes';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom');
        $dateTo = $this->argument('dateTo');
        $page = $this->argument('page');
        $limit = $this->option('limit');
        $key = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';

        $response = Http::get('http://109.73.206.144:6969/api/incomes', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => $key,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $incomes = $data['data'];

            if (empty($incomes)) {
                $this->info("Нет данных для импорта.");
                return;
            }

            $this->processIncomes($incomes);
        } else {
            $error = $response->json();
            Log::error('Ошибка при импорте данных Incomes', ['error' => $error]);
            $this->error("Ошибка: " . $response->body());
            return;
        }
        $this->info('Импорт завершён');
    }

    protected function processIncomes(array $incomes)
    {
        foreach ($incomes as $incomeData) {
            Income::create($this->mapIncomeData($incomeData));
        }
    }

    protected function mapIncomeData(array $data): array
    {
        return [
            'income_id' => $data['income_id'],
            'number' => $data['number'],
            'date' => $data['date'],
            'last_change_date' => $data['last_change_date'],
            'supplier_article' => $data['supplier_article'],
            'tech_size' => $data['tech_size'],
            'barcode' => $data['barcode'],
            'quantity' => $data['quantity'],
            'total_price' => $data['total_price'],
            'date_close' => $data['date_close'],
            'warehouse_name' => $data['warehouse_name'],
            'nm_id' => $data['nm_id'],
        ];
    }
}
