<?php

namespace App\Console\Commands;

use App\Domain\Sales\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkSaleAsTest extends Command
{
    protected $signature = 'sales:mark-test
        {sale_id : Internal sales.id to preserve as test data}
        {--dry-run : Show the target without changing it}
        {--force : Skip interactive confirmation}';

    protected $description = 'Preserve a manual sale as test data and release its production idempotency identity';

    public function handle(): int
    {
        $sale = Sale::query()
            ->withCount(['items', 'payments'])
            ->find($this->argument('sale_id'));

        if ($sale === null) {
            $this->error('Sale not found.');

            return self::FAILURE;
        }

        $this->table(
            ['ID', 'Source', 'Location', 'Ticket', 'Total', 'Items', 'Payments', 'Already test'],
            [[
                $sale->id,
                $sale->source,
                $sale->location_id,
                $sale->ticket,
                $sale->total,
                $sale->items_count,
                $sale->payments_count,
                $sale->is_test ? 'yes' : 'no',
            ]],
        );

        if ($sale->is_test) {
            $this->info('Sale is already marked as test data.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No changes were made.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Mark this sale as test data?')) {
            $this->warn('No changes were made.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($sale): void {
            $testSource = substr("test-{$sale->id}-{$sale->source}", 0, 50);
            $sale->update([
                'source' => $testSource,
                'is_test' => true,
            ]);
        });

        $this->info("Sale {$sale->id} is now test data. The original production identity is available.");

        return self::SUCCESS;
    }
}
