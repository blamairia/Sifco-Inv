<?php

namespace Database\Seeders;

use App\Models\BonEntree;
use App\Models\BonReintegration;
use App\Models\BonReintegrationItem;
use App\Models\BonSortie;
use App\Models\BonSortieItem;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\Roll;
use App\Models\RollAdjustment;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\BonEntreeService;
use App\Services\BonReintegrationService;
use App\Services\BonSortieService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        BonReintegrationItem::truncate();
        BonReintegration::truncate();
        BonSortieItem::truncate();
        BonSortie::truncate();
        BonEntree::truncate();
        StockMovement::truncate();
        StockQuantity::truncate();
        RollAdjustment::truncate();
        Roll::truncate();
        StockAdjustment::truncate();

        Schema::enableForeignKeyConstraints();

        $warehouse = Warehouse::query()->firstOrFail();
    $supplier = Supplier::query()->firstOrFail();
    $productionLine = ProductionLine::query()->first();

        $rollProducts = Product::query()->where('is_roll', true)->take(2)->get();
        $standardProduct = Product::query()->where('is_roll', false)->first();

        if ($rollProducts->count() < 2 || ! $standardProduct) {
            $this->command?->warn('⚠️  Impossible de créer les jeux de données de démonstration : produits insuffisants.');
            return;
        }

        /** @var BonEntreeService $bonEntreeService */
        $bonEntreeService = app(BonEntreeService::class);
        /** @var BonSortieService $bonSortieService */
        $bonSortieService = app(BonSortieService::class);
        /** @var BonReintegrationService $bonReintegrationService */
        $bonReintegrationService = app(BonReintegrationService::class);

        $bonNumber = BonEntree::generateBonNumber();

        $bonEntree = BonEntree::create([
            'bon_number' => $bonNumber,
            'sourceable_type' => Supplier::class,
            'sourceable_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'document_number' => 'FACT-' . Str::upper(Str::random(6)),
            'expected_date' => now()->addDay(),
            'status' => 'pending',
            'frais_approche' => 360.75,
            'total_amount_ht' => 0,
            'total_amount_ttc' => 0,
            'notes' => 'Données de démonstration générées automatiquement.',
        ]);

        $bonEntree->bonEntreeItems()->createMany([
            [
                'item_type' => 'bobine',
                'product_id' => $rollProducts[0]->id,
                'ean_13' => '299' . random_int(1000000000, 1999999999),
                'batch_number' => 'LOT-' . now()->format('ymd') . '-A',
                'qty_entered' => 1,
                'weight_kg' => 285.00,
                'length_m' => 1700.00,
                'price_ht' => 1580.50,
                'price_ttc' => 1580.50,
            ],
            [
                'item_type' => 'bobine',
                'product_id' => $rollProducts[1]->id,
                'ean_13' => '299' . random_int(2000000000, 2999999999),
                'batch_number' => 'LOT-' . now()->format('ymd') . '-B',
                'qty_entered' => 1,
                'weight_kg' => 342.00,
                'length_m' => 2050.00,
                'price_ht' => 1620.25,
                'price_ttc' => 1620.25,
            ],
            [
                'item_type' => 'product',
                'product_id' => $standardProduct->id,
                'qty_entered' => 280,
                'price_ht' => 18.60,
                'price_ttc' => 18.60,
            ],
        ]);

        $bonEntree->recalculateTotals();
        $bonEntreeService->receive($bonEntree->fresh());

        $roll = Roll::query()->orderBy('created_at')->firstOrFail();
        $rollWeight = (float) ($roll->weight ?? 0);

        $bonSortie = BonSortie::create([
            'bon_number' => BonSortie::generateBonNumber(),
            'warehouse_id' => $warehouse->id,
            'destination' => $productionLine?->name ?? 'Destination Libre DEMO',
            'destinationable_type' => $productionLine ? ProductionLine::class : null,
            'destinationable_id' => $productionLine?->id,
            'status' => 'draft',
            'issued_date' => now()->toDateString(),
            'notes' => 'Sortie de démonstration préparée via seeder.',
        ]);

        $rollLength = (float) ($roll->length_m ?? 0);
        
        $bonSortie->bonSortieItems()->create([
            'item_type' => 'roll',
            'product_id' => $roll->product_id,
            'roll_id' => $roll->id,
            'qty_issued' => 1,
            'weight_kg' => $rollWeight,
            'length_m' => $rollLength,
            'cump_at_issue' => StockQuantity::where('product_id', $roll->product_id)
                ->where('warehouse_id', $warehouse->id)
                ->value('cump_snapshot') ?? $roll->cump,
        ]);

        $bonSortieService->issue($bonSortie->fresh());

        $roll->refresh();
        $returnedWeight = round(max($rollWeight - 50, 80), 2);
        $returnedLength = round(max($rollLength - 300, 500), 2);

        $currentCump = StockQuantity::where('product_id', $roll->product_id)
            ->where('warehouse_id', $warehouse->id)
            ->value('cump_snapshot') ?? $roll->cump;

        $bonReintegration = BonReintegration::create([
            'bon_number' => BonReintegration::generateBonNumber(),
            'bon_sortie_id' => $bonSortie->id,
            'warehouse_id' => $warehouse->id,
            'return_date' => now()->toDateString(),
            'status' => 'draft',
            'cump_at_return' => $currentCump,
            'notes' => 'Retour partiel après tests production.',
        ]);

        $bonReintegration->bonReintegrationItems()->create([
            'item_type' => 'roll',
            'product_id' => $roll->product_id,
            'roll_id' => $roll->id,
            'qty_returned' => 1,
            'previous_weight_kg' => 0,
            'returned_weight_kg' => $returnedWeight,
            'previous_length_m' => 0,
            'returned_length_m' => $returnedLength,
            'cump_at_return' => $currentCump,
            'value_returned' => 0,
        ]);

        $bonReintegrationService->receive($bonReintegration->fresh());

        $this->command?->info('✅ Jeux de données de démonstration créés avec succès.');
    }
}
