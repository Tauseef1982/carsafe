<?php

namespace Tests\Unit;

use App\Services\GoCabService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoCabServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.gocab.base_url', 'https://api.gocab.test/api/company/trips');
        Config::set('services.gocab.api_key', 'csk_test_key_12345');
    }

    public function test_charge_trip_approved_covered(): void
    {
        Http::fake([
            'https://api.gocab.test/api/company/trips' => Http::response([
                'ok' => true,
                'approved' => true,
                'status' => 'COVERED',
                'reason' => null,
                'trip' => [
                    'id' => 1234,
                    'externalTripId' => 'test-trip-001',
                    'status' => 'COVERED',
                    'approved' => true,
                    'reason' => null,
                    'fareAmount' => '12.50',
                ],
            ], 201),
        ]);

        $res = GoCabService::chargeTrip([
            'cardNumber' => 'GC-100200300',
            'fareAmount' => 12.50,
            'tripDate' => '2026-07-22T14:30:00Z',
            'pickup' => ['address' => '12 Main St', 'latitude' => 41.10, 'longitude' => -74.06],
            'dropoff' => ['address' => '300 Route 59', 'latitude' => 41.11, 'longitude' => -74.07],
            'externalTripId' => 'test-trip-001',
        ]);

        $this->assertTrue($res['status']);
        $this->assertTrue($res['approved']);
        $this->assertEquals('COVERED', $res['state']);
        $this->assertEquals(1234, $res['trip']['id']);
    }

    public function test_charge_trip_uncovered_insufficient_credits(): void
    {
        Http::fake([
            'https://api.gocab.test/api/company/trips' => Http::response([
                'ok' => true,
                'approved' => false,
                'status' => 'UNCOVERED',
                'reason' => 'Insufficient credits.',
                'trip' => [
                    'id' => 1235,
                    'externalTripId' => 'test-trip-002',
                    'status' => 'UNCOVERED',
                    'approved' => false,
                    'reason' => 'Insufficient credits.',
                    'fareAmount' => '40.00',
                ],
            ], 201),
        ]);

        $res = GoCabService::chargeTrip([
            'cardNumber' => 'GC-100200300',
            'fareAmount' => 40.00,
            'tripDate' => '2026-07-22T14:30:00Z',
            'pickup' => ['address' => '12 Main St', 'latitude' => 41.10, 'longitude' => -74.06],
            'dropoff' => ['address' => '300 Route 59', 'latitude' => 41.11, 'longitude' => -74.07],
            'externalTripId' => 'test-trip-002',
        ]);

        $this->assertFalse($res['status']);
        $this->assertFalse($res['approved']);
        $this->assertEquals('UNCOVERED', $res['state']);
        $this->assertEquals('Insufficient credits.', $res['reason']);
    }

    public function test_charge_trip_409_treated_as_already_paid(): void
    {
        Http::fake([
            'https://api.gocab.test/api/company/trips' => Http::response([
                'ok' => false,
                'error' => 'Trip already charged for this externalTripId.',
                'trip' => [
                    'id' => 1234,
                    'externalTripId' => 'test-trip-001',
                    'status' => 'COVERED',
                    'approved' => true,
                    'reason' => null,
                    'fareAmount' => '12.50',
                ],
            ], 409),
        ]);

        $res = GoCabService::chargeTrip([
            'cardNumber' => 'GC-100200300',
            'fareAmount' => 12.50,
            'tripDate' => '2026-07-22T14:30:00Z',
            'pickup' => ['address' => '12 Main St', 'latitude' => 41.10, 'longitude' => -74.06],
            'dropoff' => ['address' => '300 Route 59', 'latitude' => 41.11, 'longitude' => -74.07],
            'externalTripId' => 'test-trip-001',
        ]);

        $this->assertTrue($res['status']);
        $this->assertTrue($res['approved']);
        $this->assertEquals('COVERED', $res['state']);
        $this->assertTrue($res['already_charged']);
        $this->assertEquals(1234, $res['trip']['id']);
    }

    public function test_void_trip_success(): void
    {
        Http::fake([
            'https://api.gocab.test/api/company/trips/1234/void' => Http::response([
                'ok' => true,
                'trip' => [
                    'id' => 1234,
                    'status' => 'VOID',
                    'approved' => false,
                ],
            ], 200),
        ]);

        $res = GoCabService::voidTrip(1234);

        $this->assertTrue($res['status']);
        $this->assertEquals('VOID', $res['state']);
    }

    public function test_get_single_trip(): void
    {
        Http::fake([
            'https://api.gocab.test/api/company/trips/1234' => Http::response([
                'ok' => true,
                'trip' => [
                    'id' => 1234,
                    'externalTripId' => 'test-trip-001',
                    'status' => 'COVERED',
                ],
            ], 200),
        ]);

        $res = GoCabService::getTrip(1234);

        $this->assertTrue($res['status']);
        $this->assertEquals(1234, $res['trip']['id']);
    }

    public function test_list_trips(): void
    {
        Http::fake([
            'https://api.gocab.test/api/company/trips*' => Http::response([
                'ok' => true,
                'items' => [
                    ['id' => 1234, 'status' => 'COVERED'],
                ],
                'total' => 1,
                'page' => 1,
                'limit' => 20,
            ], 200),
        ]);

        $res = GoCabService::listTrips(['status' => 'COVERED']);

        $this->assertTrue($res['status']);
        $this->assertEquals(1, $res['total']);
        $this->assertCount(1, $res['items']);
    }

    public function test_trip_can_void_helper(): void
    {
        $trip = new \App\Models\Trip();
        $trip->gocab_payment_id = 999;
        $trip->gocab_status = 'COVERED';
        $trip->payment_method = 'scn_card';
        $this->assertTrue($trip->canVoidGoCab());

        $trip->gocab_status = 'VOID';
        $trip->payment_method = 'cash';
        $this->assertFalse($trip->canVoidGoCab());
    }
}
