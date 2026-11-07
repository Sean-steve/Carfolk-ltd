<?php

namespace App\Console\Commands;

use App\Models\VehicleImage;
use App\Support\VehicleImageVariants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class BuildVehicleImageVariants extends Command
{
    protected $signature = 'carfolk:images:build
        {--dry-run : Show which derivatives would be made without writing files}
        {--limit=0 : Maximum number of photo records to check (0 = all)}
        {--force : Rebuild existing generated variants only; originals are never changed}';

    protected $description = 'Safely generate static thumbnail, listing card and detail WebP files for existing Carfolk photos.';

    public function handle(): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 0) {
            $this->error('--limit must be an integer greater than or equal to zero.');
            return self::FAILURE;
        }

        $processed = 0;
        $created = 0;
        $errors = 0;
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        foreach (VehicleImage::query()->orderBy('id')->cursor() as $image) {
            if ($limit > 0 && $processed >= $limit) break;
            $processed++;
            try {
                $result = VehicleImageVariants::generate($image, $force, $dryRun);
                $created += $result['generated'];
            } catch (\Throwable $exception) {
                $errors++;
                Log::warning('Unable to generate vehicle photo derivatives', [
                    'image_id' => $image->id,
                    'exception' => $exception->getMessage(),
                ]);
                $this->warn('Skipped photo '.$image->id.' after processing error.');
            }
        }

        $label = $dryRun ? 'Would generate' : 'Generated';
        $this->info(sprintf(
            '%s %d responsive images from %d photo records (%d errors). Original photographs unchanged.',
            $label, $created, $processed, $errors,
        ));
        if ($dryRun) $this->line('Run without --dry-run to create the static variants.');
        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }
}
