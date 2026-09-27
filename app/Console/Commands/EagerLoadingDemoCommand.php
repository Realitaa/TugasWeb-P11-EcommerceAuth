<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EagerLoadingDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:eager-loading';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate query performance difference between Lazy Loading (N+1) and Eager Loading';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('===========================================================');
        $this->info('    DEMO EAGER LOADING VS LAZY LOADING (N+1 QUERY ISSUE)   ');
        $this->info('===========================================================');

        // 1. Lazy Loading (N+1 issue)
        DB::flushQueryLog();
        DB::enableQueryLog();

        $lazyProducts = Product::take(10)->get();
        foreach ($lazyProducts as $product) {
            $categoryName = $product->category?->name;
            $tagNames = $product->tags->pluck('name')->join(', ');
        }
        $lazyQueries = count(DB::getQueryLog());
        $this->warn("1. Lazy Loading (10 Produk): {$lazyQueries} queries dieksekusi (Terjadi N+1 problem).");

        // 2. Eager Loading (Optimized)
        DB::flushQueryLog();
        $eagerProducts = Product::with(['category', 'tags'])->take(10)->get();
        foreach ($eagerProducts as $product) {
            $categoryName = $product->category?->name;
            $tagNames = $product->tags->pluck('name')->join(', ');
        }
        $eagerQueries = count(DB::getQueryLog());
        $this->info("2. Eager Loading with(['category', 'tags']): Hanya {$eagerQueries} queries dieksekusi!");

        $this->line('-----------------------------------------------------------');
        $this->line("HASIL: Eager Loading memangkas jumlah query dari {$lazyQueries} query menjadi {$eagerQueries} query.");
        $this->info('===========================================================');

        return self::SUCCESS;
    }
}
