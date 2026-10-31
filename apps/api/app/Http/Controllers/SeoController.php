<?php
namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\Vehicle;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    private function publicOrigin(): ?string
    {
        $url = rtrim((string) config('carfolk.public_url'), '/');
        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            return null;
        }
        return $url;
    }

    private function indexable(): bool
    {
        return (bool) config('carfolk.seo_enabled') && $this->publicOrigin() !== null;
    }

    public function robots(): Response
    {
        if (!$this->indexable()) {
            return response("User-agent: *\nDisallow: /\n", 200)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('Cache-Control', 'no-store');
        }
        return response("User-agent: *\nAllow: /\nDisallow: /manage\nDisallow: /api/\nSitemap: "
            .$this->publicOrigin()."/sitemap.xml\n", 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function sitemap(): Response
    {
        if (!$this->indexable()) {
            return response("<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"/>", 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')->header('Cache-Control', 'no-store');
        }
        $urls = [$this->publicOrigin().'/'];
        // One bounded sitemap: use a sitemap index and pagination before 50,000 vehicles.
        $vehicles = Vehicle::published()->orderBy('id')->limit(49999)->get(['slug','updated_at']);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc></url>';
        }
        foreach ($vehicles as $vehicle) {
            $loc = $this->publicOrigin().'/listing/'.rawurlencode($vehicle->slug);
            $xml .= '<url><loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                .'</loc><lastmod>'.$vehicle->updated_at->toDateString().'</lastmod></url>';
        }
        return response($xml.'</urlset>', 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function listing(string $slug)
    {
        $vehicle = Vehicle::published()->with('images')->where('slug', $slug)->firstOrFail();
        $site = SiteSetting::publicDetails();
        $canonical = $this->publicOrigin() ? $this->publicOrigin().'/listing/'.rawurlencode($slug) : null;
        $photo = $vehicle->images->first();
        $image = $photo ? Storage::disk($photo->disk)->url($photo->path) : null;
        if ($image && str_starts_with($image, '/')) $image = url($image);
        $title = trim($vehicle->year.' '.$vehicle->make.' '.$vehicle->model.' '.$vehicle->trim);
        $description = trim(preg_replace('/\s+/', ' ', strip_tags((string)$vehicle->description)));
        if (!$description) $description = $title.' for sale at Carfolk LTD. Contact the dealership for verified vehicle details and availability.';
        $description = mb_substr($description, 0, 160);
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Vehicle',
            'name' => $title,
            'modelDate' => (string) $vehicle->year,
            'brand' => ['@type' => 'Brand', 'name' => $vehicle->make],
            'model' => $vehicle->model,
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'KES',
                'price' => $vehicle->price_kes,
                'availability' => match ($vehicle->status) {
                    'available' => 'https://schema.org/InStock',
                    'reserved' => 'https://schema.org/PreOrder',
                    default => 'https://schema.org/SoldOut'
                },
                'seller' => ['@type' => 'Organization','name' => $site['name']],
                ...($canonical ? ['url' => $canonical] : []),
            ],
            ...($image ? ['image' => [$image]] : []),
        ];
        return response()
            ->view('seo.listing', compact('vehicle','site','title','description','image','canonical','jsonLd')
                + ['indexable' => $this->indexable()])
            ->header('Cache-Control', 'public, max-age=60');
    }
}
