<?php

namespace App\Services;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoCabService
{
    /**
     * Get the base URL for GoCab company trips endpoints.
     */
    public static function getBaseUrl(): string
    {
        $url = config('services.gocab.base_url') ?? env('GOCAB_BASE_URL', 'https://api.gocab.com/api/company/trips');
        $url = rtrim($url, '/');

        // Ensure endpoint ends with /api/company/trips
        if (!str_ends_with($url, '/trips')) {
            $url .= '/trips';
        }

        return $url;
    }

    /**
     * Get the API Key configured for GoCab SCN.
     */
    public static function getApiKey(): ?string
    {
        return config('services.gocab.api_key') ?? env('GOCAB_API_KEY');
    }

    /**
     * Get request headers required by GoCab SCN.
     */
    public static function getHeaders(): array
    {
        return [
            'x-api-key' => self::getApiKey(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Charge a trip against a family's prepaid balance via GoCab SCN API.
     *
     * Strict requirement: Only accepted fields may be sent (unknown keys cause 400).
     * Numeric values (fareAmount, coordinates) must be plain numbers.
     *
     * @param array $data [
     *     'cardNumber'     => string,
     *     'fareAmount'     => float|int,
     *     'tripDate'       => string|Carbon|DateTime,
     *     'pickup'         => ['address' => string, 'latitude' => float, 'longitude' => float],
     *     'dropoff'        => ['address' => string, 'latitude' => float, 'longitude' => float],
     *     'externalTripId' => string
     * ]
     * @return array
     */
    public static function chargeTrip(array $data): array
    {
        try {
            $apiKey = self::getApiKey();
            if (empty($apiKey)) {
                return [
                    'status' => false,
                    'approved' => false,
                    'code' => 401,
                    'state' => 'UNAUTHORIZED',
                    'message' => 'GoCab API key is not configured in services.php / .env',
                ];
            }

            // 1. Format & validate tripDate (ISO 8601 UTC string, e.g. 2026-07-22T14:30:00Z)
            $tripDate = !empty($data['tripDate'])
                ? Carbon::parse($data['tripDate'])->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z')
                : Carbon::now('UTC')->format('Y-m-d\TH:i:s\Z');

            // 2. Strict payload build: GoCab rejects requests containing ANY unexpected keys
            $payload = [
                'cardNumber' => trim((string)($data['cardNumber'] ?? '')),
                'fareAmount' => round((float)($data['fareAmount'] ?? 0), 2),
                'tripDate' => $tripDate,
                'pickup' => [
                    'address' => trim((string)($data['pickup']['address'] ?? '')),
                    'latitude' => (float)($data['pickup']['latitude'] ?? 0),
                    'longitude' => (float)($data['pickup']['longitude'] ?? 0),
                ],
                'dropoff' => [
                    'address' => trim((string)($data['dropoff']['address'] ?? '')),
                    'latitude' => (float)($data['dropoff']['latitude'] ?? 0),
                    'longitude' => (float)($data['dropoff']['longitude'] ?? 0),
                ],
                'externalTripId' => (string)($data['externalTripId'] ?? ''),
            ];

            // Validate mandatory fields
            if (empty($payload['cardNumber'])) {
                return ['status' => false, 'approved' => false, 'code' => 400, 'message' => 'GoCab cardNumber is required.'];
            }
            if ($payload['fareAmount'] <= 0) {
                return ['status' => false, 'approved' => false, 'code' => 400, 'message' => 'GoCab fareAmount must be greater than 0.'];
            }
            if (empty($payload['externalTripId'])) {
                return ['status' => false, 'approved' => false, 'code' => 400, 'message' => 'externalTripId is required for safe idempotency.'];
            }

            $endpoint = self::getBaseUrl();
            $timeout = (int)(config('services.gocab.timeout') ?? 15);

            $headers = self::getHeaders();
            $maskedHeaders = $headers;
            if (!empty($maskedHeaders['x-api-key'])) {
                $key = (string)$maskedHeaders['x-api-key'];
                $maskedHeaders['x-api-key'] = strlen($key) > 8
                    ? substr($key, 0, 4) . '***' . substr($key, -4)
                    : '***';
            }

            Log::info('GoCab SCN: Sending complete chargeTrip request', [
                'endpoint' => $endpoint,
                'method' => 'POST',
                'headers' => $maskedHeaders,
                'payload' => $payload,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                'raw_input' => $data,
            ]);

            $response = Http::withHeaders($headers)
                ->timeout($timeout)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('GoCab SCN: chargeTrip response received', [
                'externalTripId' => $payload['externalTripId'],
                'status_code' => $statusCode,
                'response_body' => $body ?? $response->body(),
            ]);

            // Handle 201 Created: Trip submitted (covered or uncovered)
            if ($statusCode === 201) {
                $approved = (bool)($body['approved'] ?? false);
                $statusStr = $body['status'] ?? ($approved ? 'COVERED' : 'UNCOVERED');
                $reason = $body['reason'] ?? null;
                $tripData = $body['trip'] ?? null;

                if ($approved) {
                    Log::info('GoCab SCN: Trip approved and charged', [
                        'externalTripId' => $payload['externalTripId'],
                        'gocabTripId' => $tripData['id'] ?? null,
                    ]);

                    return [
                        'status' => true,
                        'approved' => true,
                        'code' => 201,
                        'state' => 'COVERED',
                        'reason' => null,
                        'trip' => $tripData,
                        'message' => 'Trip covered and charged successfully.',
                        'raw' => $body,
                    ];
                }

                // 201 but approved: false -> UNCOVERED (not an error, but not paid!)
                Log::warning('GoCab SCN: Trip uncovered / declined', [
                    'externalTripId' => $payload['externalTripId'],
                    'reason' => $reason,
                ]);

                return [
                    'status' => false,
                    'approved' => false,
                    'code' => 201,
                    'state' => 'UNCOVERED',
                    'reason' => $reason ?? 'Trip not covered',
                    'trip' => $tripData,
                    'message' => 'Trip not covered: ' . ($reason ?? 'Declined by program rules.'),
                    'raw' => $body,
                ];
            }

            // Handle 409 Conflict: Duplicate externalTripId already charged -> TREAT AS ALREADY PAID
            if ($statusCode === 409) {
                $tripData = $body['trip'] ?? null;
                Log::info('GoCab SCN: 409 Conflict - Trip already charged (safe retry)', [
                    'externalTripId' => $payload['externalTripId'],
                    'gocabTripId' => $tripData['id'] ?? null,
                ]);

                return [
                    'status' => true,
                    'approved' => true,
                    'code' => 409,
                    'state' => 'COVERED',
                    'already_charged' => true,
                    'reason' => null,
                    'trip' => $tripData,
                    'message' => 'Trip already charged for this externalTripId.',
                    'raw' => $body,
                ];
            }

            // Handle 400 Bad Request: Validation or formatting errors
            if ($statusCode === 400) {
                $errorMsg = self::formatValidationErrors($body);
                Log::error('GoCab SCN: 400 Bad Request', ['payload' => $payload, 'error' => $errorMsg]);

                return [
                    'status' => false,
                    'approved' => false,
                    'code' => 400,
                    'state' => 'VALIDATION_ERROR',
                    'reason' => $errorMsg,
                    'message' => $errorMsg,
                    'raw' => $body,
                ];
            }

            // Handle 401 Unauthorized
            if ($statusCode === 401) {
                Log::error('GoCab SCN: 401 Unauthorized - check API Key');
                return [
                    'status' => false,
                    'approved' => false,
                    'code' => 401,
                    'state' => 'UNAUTHORIZED',
                    'message' => 'Unauthorized: Invalid GoCab API Key or company inactive.',
                    'raw' => $body,
                ];
            }

            // Handle 404 Not Found (Card not found or Family not found)
            if ($statusCode === 404) {
                $errorMsg = $body['error'] ?? 'Card or family record not found on GoCab.';
                Log::warning('GoCab SCN: 404 Not Found', ['cardNumber' => $payload['cardNumber'], 'error' => $errorMsg]);

                return [
                    'status' => false,
                    'approved' => false,
                    'code' => 404,
                    'state' => 'NOT_FOUND',
                    'message' => $errorMsg,
                    'raw' => $body,
                ];
            }

            // Handle 500 or other unexpected status codes
            $errorMsg = $body['error'] ?? 'Unexpected response from GoCab server.';
            Log::error('GoCab SCN: Server Error', ['status' => $statusCode, 'body' => $body]);

            return [
                'status' => false,
                'approved' => false,
                'code' => $statusCode,
                'state' => 'SERVER_ERROR',
                'message' => $errorMsg,
                'raw' => $body,
            ];

        } catch (\Throwable $e) {
            Log::error('GoCab SCN: Exception charging trip', [
                'endpoint' => $endpoint ?? self::getBaseUrl(),
                'payload' => $payload ?? ($data ?? []),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => false,
                'approved' => false,
                'code' => 500,
                'state' => 'EXCEPTION',
                'message' => 'GoCab connection error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get a single trip by GoCab's trip ID.
     * GET /api/company/trips/:id
     *
     * @param int|string $gocabTripId
     * @return array
     */
    public static function getTrip(int|string $gocabTripId): array
    {
        try {
            $endpoint = self::getBaseUrl() . '/' . $gocabTripId;

            $response = Http::withHeaders(self::getHeaders())
                ->timeout(10)
                ->get($endpoint);

            $body = $response->json();

            if ($response->successful() && ($body['ok'] ?? false)) {
                return [
                    'status' => true,
                    'trip' => $body['trip'] ?? null,
                    'raw' => $body,
                ];
            }

            return [
                'status' => false,
                'message' => $body['error'] ?? 'Trip not found or inaccessible.',
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('GoCab SCN: Exception retrieving trip', ['id' => $gocabTripId, 'error' => $e->getMessage()]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * List company trips with optional query filters and pagination.
     * GET /api/company/trips
     *
     * @param array $filters [
     *     'status'         => 'COVERED'|'UNCOVERED'|'VOID',
     *     'cardId'         => int,
     *     'familyId'       => int,
     *     'externalTripId' => string,
     *     'from'           => ISO 8601 string,
     *     'to'             => ISO 8601 string,
     *     'page'           => int,
     *     'limit'          => int
     * ]
     * @return array
     */
    public static function listTrips(array $filters = []): array
    {
        try {
            $endpoint = self::getBaseUrl();

            // Filter out null/empty values
            $queryParams = array_filter($filters, fn($val) => !is_null($val) && $val !== '');

            $response = Http::withHeaders(self::getHeaders())
                ->timeout(15)
                ->get($endpoint, $queryParams);

            $body = $response->json();

            if ($response->successful() && ($body['ok'] ?? false)) {
                return [
                    'status' => true,
                    'items' => $body['items'] ?? [],
                    'total' => (int)($body['total'] ?? 0),
                    'page' => (int)($body['page'] ?? 1),
                    'limit' => (int)($body['limit'] ?? 20),
                    'raw' => $body,
                ];
            }

            return [
                'status' => false,
                'message' => $body['error'] ?? 'Failed to list GoCab trips.',
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('GoCab SCN: Exception listing trips', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Void (cancel / reverse) a previously submitted trip.
     * POST /api/company/trips/:id/void
     *
     * If the trip was covered, the fare is refunded to the family's balance.
     *
     * @param int|string $gocabTripId
     * @return array
     */
    public static function voidTrip(int|string $gocabTripId): array
    {
        try {
            $endpoint = self::getBaseUrl() . '/' . $gocabTripId . '/void';

            Log::info('GoCab SCN: Voiding trip', ['gocabTripId' => $gocabTripId]);

            $response = Http::withHeaders(self::getHeaders())
                ->timeout(15)
                ->post($endpoint);

            $statusCode = $response->status();
            $body = $response->json();

            if ($statusCode === 200 && ($body['ok'] ?? false)) {
                Log::info('GoCab SCN: Trip successfully voided and refunded', ['gocabTripId' => $gocabTripId]);
                return [
                    'status' => true,
                    'state' => 'VOID',
                    'trip' => $body['trip'] ?? null,
                    'message' => 'Trip has been voided and refunded.',
                    'raw' => $body,
                ];
            }

            if ($statusCode === 400) {
                // Trip was already voided
                return [
                    'status' => false,
                    'already_voided' => true,
                    'message' => $body['error'] ?? 'Trip is already voided.',
                    'raw' => $body,
                ];
            }

            if ($statusCode === 404) {
                return [
                    'status' => false,
                    'message' => 'Trip not found on GoCab.',
                    'raw' => $body,
                ];
            }

            return [
                'status' => false,
                'message' => $body['error'] ?? 'Failed to void trip on GoCab.',
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('GoCab SCN: Exception voiding trip', ['id' => $gocabTripId, 'error' => $e->getMessage()]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Charge a CarSafe Trip model directly and update its GoCab attributes.
     *
     * @param Trip $trip
     * @param string $cardNumber
     * @param float|null $fareAmount
     * @return array
     */
    public static function chargeTripModel(Trip $trip, string $cardNumber, ?float $fareAmount = null): array
    {
        $amount = !is_null($fareAmount) ? $fareAmount : (float)($trip->trip_cost ?? 0);

        // Resolve coordinates from trip model or fallback to Geoapify
        $pickupLat = (float)($trip->pickup_lat ?? 0);
        $pickupLng = (float)($trip->pickup_lng ?? 0);
        $dropoffLat = (float)($trip->dropoff_lat ?? 0);
        $dropoffLng = (float)($trip->dropoff_lng ?? 0);

        if (($pickupLat == 0.0 || $pickupLng == 0.0) && !empty($trip->location_from)) {
            $pCoords = GeoapifyService::getCoordinates($trip->location_from);
            if ($pCoords) {
                $pickupLat = $pCoords['lat'];
                $pickupLng = $pCoords['lng'];
                $trip->pickup_lat = $pickupLat;
                $trip->pickup_lng = $pickupLng;
                $trip->save();
            }
        }

        if (($dropoffLat == 0.0 || $dropoffLng == 0.0) && !empty($trip->location_to)) {
            $dCoords = GeoapifyService::getCoordinates($trip->location_to);
            if ($dCoords) {
                $dropoffLat = $dCoords['lat'];
                $dropoffLng = $dCoords['lng'];
                $trip->dropoff_lat = $dropoffLat;
                $trip->dropoff_lng = $dropoffLng;
                $trip->save();
            }
        }

        $params = [
            'cardNumber' => $cardNumber,
            'fareAmount' => $amount,
            'tripDate' => $trip->date ? $trip->date . ' ' . ($trip->time ?? '00:00:00') : Carbon::now(),
            'pickup' => [
                'address' => $trip->location_from ?: 'Pickup location',
                'latitude' => $pickupLat,
                'longitude' => $pickupLng,
            ],
            'dropoff' => [
                'address' => $trip->location_to ?: 'Dropoff location',
                'latitude' => $dropoffLat,
                'longitude' => $dropoffLng,
            ],
            'externalTripId' => (string)$trip->trip_id,
        ];

        $result = self::chargeTrip($params);

        // Update Trip model with transaction result
        $trip->gocab_card_number = $cardNumber;
        $trip->gocab_status = $result['state'] ?? ($result['status'] ? 'COVERED' : 'UNCOVERED');
        $trip->gocab_reason = $result['reason'] ?? null;
        $trip->gocab_response = $result['raw'] ?? null;

        if ($result['status'] && !empty($result['trip']['id'])) {
            $trip->gocab_payment_id = $result['trip']['id'];
            $trip->gocab_paid = (float)($result['trip']['fareAmount'] ?? $amount);
            $trip->payment_method = 'gocab';
        }

        $trip->save();

        return $result;
    }

    /**
     * Format validation error array into a readable string.
     */
    protected static function formatValidationErrors(array $body): string
    {
        if (isset($body['error']) && is_string($body['error'])) {
            return $body['error'];
        }

        if (isset($body['error']['fieldErrors']) && is_array($body['error']['fieldErrors'])) {
            $messages = [];
            foreach ($body['error']['fieldErrors'] as $field => $errs) {
                $errStr = is_array($errs) ? implode(', ', $errs) : (string)$errs;
                $messages[] = "{$field}: {$errStr}";
            }
            if (!empty($messages)) {
                return implode('; ', $messages);
            }
        }

        return 'Invalid input data provided to GoCab.';
    }
}
