<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Obat;

class FixObatStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obat:fix-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix obat stock calculations by recalculating stok_sisa based on transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting obat stock fix...');
        
        $obats = Obat::all();
        $fixedCount = 0;
        
        $this->info("Found {$obats->count()} obat records to check.");
        
        foreach ($obats as $obat) {
            try {
                // Calculate total masuk and keluar from transactions
                $totalMasuk = $obat->transaksiObats()
                    ->where('tipe_transaksi', 'masuk')
                    ->sum('jumlah_masuk') ?? 0;
                    
                $totalKeluar = $obat->transaksiObats()
                    ->where('tipe_transaksi', 'keluar')
                    ->sum('jumlah_keluar') ?? 0;
                
                // Calculate correct stok_sisa
                $correctStokSisa = $obat->stok_awal + $totalMasuk - $totalKeluar;
                
                // Check if current stok_sisa is incorrect
                if ($obat->stok_sisa != $correctStokSisa) {
                    $oldStokSisa = $obat->stok_sisa;
                    
                    // Update the obat record
                    $obat->update([
                        'stok_masuk' => $totalMasuk,
                        'stok_keluar' => $totalKeluar,
                        'stok_sisa' => $correctStokSisa
                    ]);
                    
                    $this->line("Fixed {$obat->nama_obat}: {$oldStokSisa} → {$correctStokSisa}");
                    $fixedCount++;
                } else {
                    $this->line("✓ {$obat->nama_obat}: stock is correct ({$obat->stok_sisa})");
                }
                
            } catch (\Exception $e) {
                // If there's an error (like missing transaksi table), set stok_sisa = stok_awal
                if ($obat->stok_sisa != $obat->stok_awal) {
                    $oldStokSisa = $obat->stok_sisa;
                    
                    $obat->update([
                        'stok_masuk' => 0,
                        'stok_keluar' => 0,
                        'stok_sisa' => $obat->stok_awal
                    ]);
                    
                    $this->line("Reset {$obat->nama_obat}: {$oldStokSisa} → {$obat->stok_awal} (no transactions)");
                    $fixedCount++;
                }
            }
        }
        
        $this->info("Stock fix completed!");
        $this->info("Fixed {$fixedCount} obat records.");
        
        if ($fixedCount > 0) {
            $this->warn("Please refresh your browser to see the updated stock values.");
        }
        
        return Command::SUCCESS;
    }
}
