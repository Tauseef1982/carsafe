<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoapifyService
{
    /**
     * Clean and normalize an address string for geocoding.
     *
     * @param string|null $address
     * @return string|null
     */
    public static function cleanAddress(?string $address): ?string
    {
        if (empty($address)) {
            return null;
        }

        // Remove newlines, carriage returns and collapse duplicate whitespace
        $cleaned = preg_replace('/\s+/', ' ', str_replace(["\r", "\n", "\t"], ' ', $address));
        $cleaned = trim($cleaned);

        // Strip trailing and leading slashes or commas
        $cleaned = trim($cleaned, " /\\,");

        return $cleaned !== '' ? $cleaned : null;
    }

    /**
     * Geocode an address string into latitude and longitude coordinates.
     * Uses 30-day caching to maximize performance and minimize API usage.
     *
     * @param string|null $address
     * @return array{lat: float, lng: float}|null
     */
    public static function getCoordinates(?string $address): ?array
    {
        $cleaned = self::cleanAddress($address);
        if (!$cleaned) {
            return null;
        }

        $cacheKey = 'geo_coords_' . md5(strtolower($cleaned));

        try {
            return Cache::remember($cacheKey, now()->addDays(30), function () use ($cleaned) {
                $endpoint = config('services.geoapify.base_url') ?? env('GEOAPIFY_BASE_URL', 'https://api.geoapify.com/v1/geocode/search');
                $apiKey = config('services.geoapify.api_key') ?? env('GEOAPIFY_API_KEY', '4c6cd85076094e929039d46d2ceb5949');
                $timeout = (int) (config('services.geoapify.timeout') ?? 5);

                if (empty($apiKey)) {
                    Log::warning('GeoapifyService: API key is not configured.');
                    return null;
                }

                $response = Http::timeout($timeout)->get($endpoint, [
                    'text' => $cleaned,
                    'apiKey' => $apiKey,
                    'limit' => 1,
                ]);

                if (!$response->successful()) {
                    Log::warning('GeoapifyService: Geocode API request returned error', [
                        'address' => $cleaned,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return null;
                }

                $data = $response->json();
                $feature = $data['features'][0] ?? null;

                if (!$feature) {
                    Log::info('GeoapifyService: No features returned for address', ['address' => $cleaned]);
                    return null;
                }

                // Geoapify GeoJSON format provides coordinates in properties (lat/lon) or geometry (lon, lat)
                $lat = $feature['properties']['lat'] ?? ($feature['geometry']['coordinates'][1] ?? null);
                $lng = $feature['properties']['lon'] ?? ($feature['properties']['lng'] ?? ($feature['geometry']['coordinates'][0] ?? null));

                if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
                    return [
                        'lat' => round((float) $lat, 7),
                        'lng' => round((float) $lng, 7),
                    ];
                }

                return null;
            });
        } catch (\Throwable $e) {
            Log::error('GeoapifyService: Exception during geocoding', [
                'address' => $cleaned,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
