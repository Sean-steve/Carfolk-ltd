<?php
namespace App\Support;

use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Import operator-approved dealership stock as unpublished drafts.
 * Does not infer authenticity, import photos, overwrite stock or publish listings.
 */
final class InventoryCsvImport
{
    public const COLUMNS = [
        'stock_reference', 'make', 'model', 'year', 'price_kes', 'status',
        'mileage_km', 'fuel_type', 'transmission', 'body_type', 'condition',
        'colour', 'location', 'description', 'features',
    ];

    public static function parse(string $path): array
    {
        if (!str_starts_with($path, '/') || !is_file($path) || !is_readable($path)
            || filesize($path) > 2097152 || filesize($path) === 0) {
            throw new RuntimeException('Provide a readable absolute path to a CSV file of at most 2 MiB.');
        }
        $real = realpath($path);
        $public = realpath(public_path());
        if (!$real || ($public && str_starts_with($real, $public . DIRECTORY_SEPARATOR))) {
            throw new RuntimeException('Inventory CSV must never be stored in the public web directory.');
        }

        $handle = fopen($real, 'rb');
        if (!$handle) throw new RuntimeException('Cannot open inventory CSV.');
        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            if (is_array($header) && isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }
            if ($header !== self::COLUMNS) {
                throw new RuntimeException('CSV header must exactly match the documented Carfolk import template.');
            }
            $rows = [];
            $references = [];
            $slugs = [];
            $line = 1;
            while (($cells = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $line++;
                if ($cells === [null]) continue;
                if (count($cells) !== count(self::COLUMNS)) {
                    throw new RuntimeException("Line {$line} has the wrong number of CSV columns.");
                }
                $row = array_combine(self::COLUMNS, array_map('trim', $cells));
                $v = Validator::make($row, [
                    'stock_reference' => ['required', 'string', 'min:2', 'max:60'],
                    'make' => ['required', 'string', 'min:2', 'max:80'],
                    'model' => ['required', 'string', 'min:1', 'max:100'],
                    'year' => ['required', 'integer', 'between:1950,2035'],
                    'price_kes' => ['required', 'integer', 'min:1', 'max:999999999999'],
                    'status' => ['required', 'in:available,reserved,sold'],
                    'mileage_km' => ['nullable', 'integer', 'min:0', 'max:999999999'],
                    'fuel_type' => ['nullable', 'in:Petrol,Diesel,Hybrid,Electric,Other'],
                    'transmission' => ['nullable', 'in:Automatic,Manual,CVT,Other'],
                    'body_type' => ['nullable', 'in:SUV,Sedan,Crossover,Hatchback,Pickup,Van,Coupe,Other'],
                    'condition' => ['required', 'in:New,Foreign-used,Locally-used'],
                    'colour' => ['nullable', 'string', 'max:50'],
                    'location' => ['required', 'string', 'min:2', 'max:140'],
                    'description' => ['nullable', 'string', 'max:10000'],
                    'features' => ['nullable', 'string', 'max:2000'],
                ]);
                if ($v->fails()) {
                    throw new RuntimeException("Line {$line} invalid: " . implode(' ', $v->errors()->all()));
                }
                $reference = mb_strtolower($row['stock_reference']);
                $slug = Str::slug($row['make'] . '-' . $row['model'] . '-' . $row['year'] . '-' . $row['stock_reference']);
                if (isset($references[$reference]) || isset($slugs[$slug]) || !$slug || strlen($slug) > 200) {
                    throw new RuntimeException("Line {$line} has a duplicate or invalid stock reference/URL slug.");
                }
                $references[$reference] = true;
                $slugs[$slug] = true;
                $row['features'] = $row['features'] === '' ? [] : array_values(array_filter(array_map('trim', explode('|', $row['features']))));
                if (count($row['features']) > 50 || collect($row['features'])->contains(fn($f) => strlen($f) > 120)) {
                    throw new RuntimeException("Line {$line} has too many or overlong vehicle features.");
                }
                foreach (['mileage_km', 'fuel_type', 'transmission', 'body_type', 'colour', 'description'] as $optional) {
                    if ($row[$optional] === '') $row[$optional] = null;
                }
                $rows[] = $row;
                if (count($rows) > 500) throw new RuntimeException('Import is limited to 500 vehicles per file.');
            }
            if (!$rows) throw new RuntimeException('Inventory CSV has no vehicle records.');
            self::ensureNew($rows);
            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private static function ensureNew(array $rows): void
    {
        $references = array_column($rows, 'stock_reference');
        $slugs = array_map(
            fn($r) => Str::slug($r['make'] . '-' . $r['model'] . '-' . $r['year'] . '-' . $r['stock_reference']),
            $rows,
        );
        if (Vehicle::query()->whereIn('stock_reference', $references)->orWhereIn('slug', $slugs)->exists()) {
            throw new RuntimeException('An inventory reference or URL slug already exists. No records imported.');
        }
    }

    public static function apply(array $rows): int
    {
        return DB::transaction(function () use ($rows) {
            self::ensureNew($rows);
            foreach ($rows as $row) {
                VehicleData::create(array_merge($row, [
                    'is_published' => false,
                    'is_featured' => false,
                ]));
            }
            return count($rows);
        });
    }
}
