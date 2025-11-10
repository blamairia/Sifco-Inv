<?php

namespace App\Console\Commands;

use App\Models\LowStockAlert;
use App\Models\Product;
use App\Models\StockQuantity;
use App\Models\User;
use Filament\Actions\Action as FilamentAction;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-low {--force : Force check regardless of existing alerts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products with low stock levels and create alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for low stock levels...');

        $alertsCreated = 0;
        $alertsUpdated = 0;

        // Get all stock quantities
        $stockQuantities = StockQuantity::with(['product', 'warehouse'])->get();

        foreach ($stockQuantities as $stockQty) {
            $product = $stockQty->product;
            
            // Skip if product has no min_stock or safety_stock set
            if ($product->min_stock <= 0 && $product->safety_stock <= 0) {
                continue;
            }

            // Determine severity based on stock level
            $severity = $this->calculateSeverity($stockQty->total_qty, $product->min_stock, $product->safety_stock);

            // If stock is not low, skip
            if ($severity === null) {
                // Resolve any existing active alerts for this product/warehouse
                LowStockAlert::where('product_id', $product->id)
                    ->where('warehouse_id', $stockQty->warehouse_id)
                    ->where('status', 'ACTIVE')
                    ->update([
                        'status' => 'RESOLVED',
                        'resolved_at' => now(),
                        'notes' => 'Stock level restored',
                    ]);
                continue;
            }

            // Check if alert already exists
            $existingAlert = LowStockAlert::where('product_id', $product->id)
                ->where('warehouse_id', $stockQty->warehouse_id)
                ->where('status', 'ACTIVE')
                ->first();

            if ($existingAlert) {
                // Update existing alert if severity changed
                if ($existingAlert->severity !== $severity) {
                    $existingAlert->update([
                        'current_qty' => $stockQty->total_qty,
                        'severity' => $severity,
                    ]);
                    $alertsUpdated++;
                    $this->warn("Updated alert for {$product->name} at {$stockQty->warehouse->name} - Severity: {$severity}");
                }
            } else {
                // Create new alert
                $alert = LowStockAlert::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $stockQty->warehouse_id,
                    'current_qty' => $stockQty->total_qty,
                    'min_stock' => $product->min_stock,
                    'safety_stock' => $product->safety_stock,
                    'severity' => $severity,
                    'status' => 'ACTIVE',
                ]);

                $alertsCreated++;
                $this->error("LOW STOCK: {$product->name} at {$stockQty->warehouse->name} - Qty: {$stockQty->total_qty}, Min: {$product->min_stock}");

                // Send notification to admins
                $this->sendNotification($alert);
            }
        }

        $this->info("Check complete. Created: {$alertsCreated}, Updated: {$alertsUpdated}");
        
        Log::info("Low stock check completed", [
            'alerts_created' => $alertsCreated,
            'alerts_updated' => $alertsUpdated,
        ]);

        return Command::SUCCESS;
    }

    /**
     * Calculate severity based on stock levels
     */
    protected function calculateSeverity(float $currentQty, float $minStock, float $safetyStock): ?string
    {
        // CRITICAL: Stock at or below 0
        if ($currentQty <= 0) {
            return 'CRITICAL';
        }

        // HIGH: Stock below safety stock (if set)
        if ($safetyStock > 0 && $currentQty <= $safetyStock) {
            return 'HIGH';
        }

        // MEDIUM: Stock below min stock
        if ($minStock > 0 && $currentQty <= $minStock) {
            return 'MEDIUM';
        }

        // LOW: Stock at 110% of min stock (early warning)
        if ($minStock > 0 && $currentQty <= $minStock * 1.1) {
            return 'LOW';
        }

        // No alert needed
        return null;
    }

    /**
     * Send notification to admins
     */
    protected function sendNotification(LowStockAlert $alert): void
    {
        // Get admin users (you can customize this query)
        $admins = User::all();

        foreach ($admins as $admin) {
            Notification::make()
                ->warning()
                ->title('Alerte Stock Faible')
                ->body("Le produit {$alert->product->name} dans l'entrepôt {$alert->warehouse->name} a un niveau de stock faible: {$alert->current_qty} unités (Min: {$alert->min_stock})")
                ->actions([
                    FilamentAction::make('view')
                        ->label("Voir l'alerte")
                        ->url(route('filament.admin.resources.low-stock-alerts.index')),
                ])
                ->sendToDatabase($admin);
        }
    }
}
