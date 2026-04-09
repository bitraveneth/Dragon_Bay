<?php

namespace Database\Seeders\Manufacturing;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Manufacturing → Batches & lots.
 *
 * Previously we only created one demo batch for SAF-500ML-CTN.
 * Now we create a small set of batches across multiple SKUs so
 * the manufacturing and inventory screens feel more realistic.
 */
class BatchesLotsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // Lookup finished products we want to seed batches for
        $carton500 = Product::where('sku', 'SAF-500ML-CTN')->first();
        $bottle500 = Product::where('sku', 'SAF-500ML')->first();
        $carton1L  = Product::where('sku', 'SAF-1L-CTN')->first();
        $jar20L    = Product::where('sku', 'SAF-20L-JAR')->first();

        $definitions = [];

        if ($carton500) {
            $definitions[] = [
                'product' => $carton500,
                'batches' => [
                    [
                        'suffix'          => 'A',
                        'production_date' => $today->copy()->subDays(2),
                        'expiry_date'     => $today->copy()->addMonths(6),
                        'qc_status'       => 'approved',
                        'notes'           => 'Recent production – approved by QC',
                    ],
                    [
                        'suffix'          => 'B',
                        'production_date' => $today->copy()->subDays(10),
                        'expiry_date'     => $today->copy()->addMonths(5),
                        'qc_status'       => 'pending',
                        'notes'           => 'Awaiting final microbiology results',
                    ],
                    [
                        'suffix'          => 'C',
                        'production_date' => $today->copy()->subDays(20),
                        'expiry_date'     => $today->copy()->addMonths(5),
                        'qc_status'       => 'rejected',
                        'notes'           => 'Rejected due to failed QC (demo)',
                    ],
                ],
                'prefix' => '500ML-CTN-',
            ];
        }

        if ($bottle500) {
            $definitions[] = [
                'product' => $bottle500,
                'batches' => [
                    [
                        'suffix'          => 'A',
                        'production_date' => $today->copy()->subDays(1),
                        'expiry_date'     => $today->copy()->addMonths(6),
                        'qc_status'       => 'approved',
                        'notes'           => 'Single bottle line – approved',
                    ],
                ],
                'prefix' => '500ML-BOT-',
            ];
        }

        if ($carton1L) {
            $definitions[] = [
                'product' => $carton1L,
                'batches' => [
                    [
                        'suffix'          => 'A',
                        'production_date' => $today->copy()->subDays(5),
                        'expiry_date'     => $today->copy()->addMonths(7),
                        'qc_status'       => 'pending',
                        'notes'           => '1L carton – QC pending',
                    ],
                ],
                'prefix' => '1L-CTN-',
            ];
        }

        if ($jar20L) {
            $definitions[] = [
                'product' => $jar20L,
                'batches' => [
                    [
                        'suffix'          => 'A',
                        'production_date' => $today->copy()->subDays(3),
                        'expiry_date'     => $today->copy()->addMonths(3),
                        'qc_status'       => 'approved',
                        'notes'           => '20L jar – approved for dispatch',
                    ],
                ],
                'prefix' => '20L-JAR-',
            ];
        }

        foreach ($definitions as $definition) {
            $product = $definition['product'];
            $prefix  = $definition['prefix'];

            foreach ($definition['batches'] as $batchDef) {
                $code = $prefix . $batchDef['production_date']->format('ymd') . '-' . $batchDef['suffix'];

                Batch::firstOrCreate(
                    ['batch_code' => $code],
                    [
                        'product_id'      => $product->id,
                        'production_date' => $batchDef['production_date'],
                        'expiry_date'     => $batchDef['expiry_date'],
                        'qc_status'       => $batchDef['qc_status'],
                        'notes'           => $batchDef['notes'],
                    ]
                );
            }
        }
    }
}
