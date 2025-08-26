<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily product report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating daily product report...');

        // Example: count all products
        $count = Product::count();
        $date = now()->toDateTimeString();
        $reportLine = "[$date] Total products: {$count}\n";
        $this->info($reportLine);

        // Save to a file if needed
        file_put_contents(storage_path('reports.txt'), "Total products: {$count}\n", FILE_APPEND);

        $this->info('Report generated successfully.');
    }
}
