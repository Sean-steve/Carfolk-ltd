<?php

namespace App\Support;

use App\Models\AuditEvent;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Mutations of the dealership gallery. Lock the vehicle to serialize
 * admin edits and preserve a single primary image and a stable display order.
 */
final class VehiclePhotoEditor
{
    private static function lock(Vehicle $vehicle): Vehicle
    {
        $locked = Vehicle::query()->whereKey($vehicle->getKey())->lockForUpdate()->firstOrFail();
        abort_if($locked->archived_at, 404);
        return $locked;
    }

    private static function belongs(Vehicle $vehicle, VehicleImage $image): VehicleImage
    {
        return $vehicle->images()->whereKey($image->getKey())->firstOrFail();
    }

    public static function primary(Vehicle $vehicle, VehicleImage $image): void
    {
        DB::transaction(function () use ($vehicle, $image) {
            $locked = self::lock($vehicle);
            $target = self::belongs($locked, $image);
            $locked->images()->where('is_primary', true)->update(['is_primary' => false]);
            $target->update(['is_primary' => true]);
            AuditEvent::record('vehicle.photo_primary_changed', $locked, ['image_id' => $target->id]);
        });
    }

    public static function reorder(Vehicle $vehicle, array $ids): void
    {
        DB::transaction(function () use ($vehicle, $ids) {
            $locked = self::lock($vehicle);
            $actual = $locked->images()->pluck('id')->all();
            $unique = array_unique($ids);
            $submitted = $ids;
            sort($submitted);
            sort($actual);
            if (count($unique) !== count($ids) || $submitted !== $actual) {
                throw ValidationException::withMessages([
                    'order' => 'Your photo gallery changed. Refresh the page and try ordering the current photos again.',
                ]);
            }
            foreach ($ids as $index => $id) {
                $locked->images()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
            AuditEvent::record('vehicle.photos_reordered', $locked, ['count' => count($ids)]);
        });
    }

    public static function move(Vehicle $vehicle, VehicleImage $image, string $direction): void
    {
        DB::transaction(function () use ($vehicle, $image, $direction) {
            $locked = self::lock($vehicle);
            $target = self::belongs($locked, $image);
            $images = $locked->images()->get();
            $ids = $images->pluck('id')->all();
            $index = array_search($target->id, $ids, true);
            $other = $direction === 'up' ? $index - 1 : $index + 1;
            if ($index === false || $other < 0 || $other >= count($ids)) return;
            [$ids[$index], $ids[$other]] = [$ids[$other], $ids[$index]];
            foreach ($ids as $position => $id) {
                $locked->images()->whereKey($id)->update(['sort_order' => $position + 1]);
            }
            AuditEvent::record('vehicle.photos_reordered', $locked, ['count' => count($ids)]);
        });
    }

    public static function updateAlt(Vehicle $vehicle, VehicleImage $image, ?string $alt): void
    {
        DB::transaction(function () use ($vehicle, $image, $alt) {
            $locked = self::lock($vehicle);
            $target = self::belongs($locked, $image);
            $target->update(['alt_text' => filled($alt) ? trim($alt) : null]);
            AuditEvent::record('vehicle.photo_alt_updated', $locked, ['image_id' => $target->id]);
        });
    }

    public static function replace(Vehicle $vehicle, VehicleImage $image, UploadedFile $photo): void
    {
        PhotoImageGuard::validate($photo, 'photo');
        $newPath = null;
        $old = null;
        try {
            DB::transaction(function () use ($vehicle, $image, $photo, &$newPath, &$old) {
                $locked = self::lock($vehicle);
                $target = self::belongs($locked, $image);
                $old = ['disk' => $target->disk, 'path' => $target->path];
                $newPath = $photo->store('vehicles/'.$locked->id, 'public');
                if (!$newPath) throw new \RuntimeException('Unable to store replacement vehicle photo.');
                // Keep the original image identity, primary flag, caption and position.
                $target->update(['disk' => 'public', 'path' => $newPath]);
                AuditEvent::record('vehicle.photo_replaced', $locked, ['image_id' => $target->id]);
            });
        } catch (\Throwable $error) {
            if ($newPath !== null) Storage::disk('public')->delete($newPath);
            throw $error;
        }
        if ($old !== null && !($old['disk'] === 'public' && $old['path'] === $newPath)) {
            Storage::disk($old['disk'])->delete($old['path']);
            VehicleImageVariants::deleteForPath($old['disk'], $old['path']);
        }
        try {
            VehicleImageVariants::generate($image->fresh());
        } catch (\Throwable $error) {
            Log::warning('Replacement vehicle photo responsive variants pending', [
                'image_id' => $image->id, 'exception' => $error->getMessage(),
            ]);
        }
    }
}
