<?php
namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InventoryActivationTest extends TestCase
{
    use RefreshDatabase;

    private function makeFile(array $lines): string
    {
        $file = tempnam(sys_get_temp_dir(), 'carfolk-stock-');
        $out = fopen($file, 'wb');
        fputcsv($out, \App\Support\InventoryCsvImport::COLUMNS, ',', '"', '');
        foreach ($lines as $line) fputcsv($out, $line, ',', '"', '');
        fclose($out);
        return $file;
    }

    private function row(string $ref = 'OWNER-STOCK-01'): array
    {
        return [
            $ref, 'Toyota', 'RAV4', '2021', '3450000', 'available', '43000',
            'Petrol', 'Automatic', 'SUV', 'Locally-used', 'Blue',
            'Verified showroom', 'Owner-approved inventory row for testing', 'ABS|Air conditioning',
        ];
    }

    public function test_import_is_preview_only_without_both_explicit_write_switches(): void
    {
        $file = $this->makeFile([$this->row()]);
        try {
            $this->assertSame(0, Artisan::call('carfolk:inventory-import', ['file' => $file]));
            $this->assertDatabaseCount('vehicles', 0);
            $this->assertSame(1, Artisan::call('carfolk:inventory-import', ['file' => $file, '--apply' => true]));
            $this->assertDatabaseCount('vehicles', 0);
            $this->assertSame(0, Artisan::call('carfolk:inventory-import', [
                'file' => $file, '--apply' => true, '--attested' => true,
            ]));
            $this->assertDatabaseCount('vehicles', 1);
            $vehicle = Vehicle::firstOrFail();
            $this->assertFalse($vehicle->is_published);
            $this->assertFalse($vehicle->is_featured);
            $this->assertSame('OWNER-STOCK-01', $vehicle->stock_reference);
            $this->assertSame(['ABS', 'Air conditioning'], $vehicle->features);
        } finally {
            unlink($file);
        }
    }

    public function test_import_rejects_bad_row_before_any_database_writes(): void
    {
        $bad = $this->row('OWNER-STOCK-02');
        $bad[4] = '-250';
        $file = $this->makeFile([$this->row(), $bad]);
        try {
            $this->assertSame(1, Artisan::call('carfolk:inventory-import', [
                'file' => $file, '--apply' => true, '--attested' => true,
            ]));
            $this->assertDatabaseCount('vehicles', 0);
        } finally {
            unlink($file);
        }
    }

    public function test_duplicate_references_are_rejected_and_existing_stock_is_not_overwritten(): void
    {
        $file = $this->makeFile([$this->row('DUPLICATE'), $this->row('duplicate')]);
        try {
            $this->assertSame(1, Artisan::call('carfolk:inventory-import', ['file' => $file]));
            $this->assertDatabaseCount('vehicles', 0);
        } finally { unlink($file); }

        $file = $this->makeFile([$this->row('EXISTING-01')]);
        try {
            $this->assertSame(0, Artisan::call('carfolk:inventory-import', [
                'file' => $file, '--apply' => true, '--attested' => true,
            ]));
            $this->assertSame(1, Artisan::call('carfolk:inventory-import', ['file' => $file]));
            $this->assertDatabaseCount('vehicles', 1);
        } finally { unlink($file); }
    }

    public function test_runtime_launch_audit_is_no_go_in_ci_testing_environment(): void
    {
        $this->assertSame(1, Artisan::call('carfolk:launch-audit'));
        $this->assertStringContainsString('NO GO', Artisan::output());
    }
}
