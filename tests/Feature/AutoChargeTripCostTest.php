<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CreditCard;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Services\CardKnoxService;
use App\Services\PaymentSaveService;
use App\Services\TwilioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AutoChargeTripCostTest extends TestCase
{
    use DatabaseTransactions;

    protected $driver;
    protected $account;
    protected $trip;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test driver
        $this->driver = Driver::create([
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'driver_id' => 'DRV_' . rand(10000, 99999),
            'phone' => '8455551234',
            'role' => 'driver',
            'username' => 'testdriver_' . rand(1000, 9999),
        ]);

        // Create a test prepaid account
        $this->account = new Account();
        $this->account->account_id = 'ACC_' . rand(10000, 99999);
        $this->account->f_name = 'John';
        $this->account->lname = 'Doe';
        $this->account->phone = '8455555678';
        $this->account->email = 'testaccount@example.com';
        $this->account->account_type = 'prepaid';
        $this->account->balance = 10.00;
        $this->account->status = 1;
        $this->account->paypertrip = 'off';
        $this->account->autofill = 'off';
        $this->account->auto_charge_trip_cost = 0;
        $this->account->pins = '1234,5678';
        $this->account->save();

        // Create a test trip
        $this->trip = new Trip();
        $this->trip->trip_id = (string) rand(1000000000, 9999999999);
        $this->trip->driver_id = $this->driver->driver_id;
        $this->trip->account_number = $this->account->account_id;
        $this->trip->payment_method = 'account';
        $this->trip->trip_cost = 35.00;
        $this->trip->extra_charges = 0;
        $this->trip->passenger_phone = '8455559999';
        $this->trip->date = now()->toDateString();
        $this->trip->time = now()->format('H:i');
        $this->trip->is_delete = 0;
        $this->trip->save();
    }

    /**
     * Scenario 1: Setting is OFF (default) and balance is insufficient.
     * Expected: Card is NOT charged, returns 'Prepaid Account:Low Balance.', balance untouched.
     */
    public function test_low_balance_with_setting_off_declines_without_charging_card(): void
    {
        $this->account->auto_charge_trip_cost = 0;
        $this->account->balance = 10.00;
        $this->account->save();

        $controller = new \App\Http\Controllers\TripController();
        $request = Request::create('/admin/add-payment', 'POST', [
            'trip' => $this->trip->trip_id,
            'payment_method' => 'account',
            'account' => $this->account->account_id,
            'amount' => 35.00,
            'account_pin' => '1234',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);

        $this->assertFalse($data['status']);
        $this->assertEquals('Prepaid Account:Low Balance.', $data['message']);

        // Account balance must remain unchanged
        $this->account->refresh();
        $this->assertEquals(10.00, (float) $this->account->balance);

        // No payment recorded
        $this->assertDatabaseMissing('payments', [
            'trip_id' => $this->trip->trip_id,
            'type' => 'credit',
        ]);
    }

    /**
     * Scenario 2: Setting is ON and balance is insufficient, but card charge fails/declined.
     * Expected: Card was attempted, returned false, trip declines with 'Prepaid Account:Low Balance.'
     */
    public function test_low_balance_with_setting_on_declines_if_card_charge_fails(): void
    {
        $this->account->auto_charge_trip_cost = 1;
        $this->account->balance = 10.00;
        $this->account->save();
        // No credit card exists for account, so PaymentSaveService::prepPaidRefill will return false

        $controller = new \App\Http\Controllers\TripController();
        $request = Request::create('/admin/add-payment', 'POST', [
            'trip' => $this->trip->trip_id,
            'payment_method' => 'account',
            'account' => $this->account->account_id,
            'amount' => 35.00,
            'account_pin' => '1234',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);

        $this->assertFalse($data['status']);
        $this->assertEquals('Prepaid Account:Low Balance.', $data['message']);

        // Account balance remains unchanged
        $this->account->refresh();
        $this->assertEquals(10.00, (float) $this->account->balance);
    }

    /**
     * Scenario 2b: Setting is OFF, non-JSON web form request.
     * Expected: Returns a redirect back with flash error message.
     */
    public function test_low_balance_with_setting_off_via_web_redirect(): void
    {
        $this->account->auto_charge_trip_cost = 0;
        $this->account->balance = 10.00;
        $this->account->save();

        $controller = new \App\Http\Controllers\TripController();
        $request = Request::create('/admin/add-payment', 'POST', [
            'trip' => $this->trip->trip_id,
            'payment_method' => 'account',
            'account' => $this->account->account_id,
            'amount' => 35.00,
            'account_pin' => '1234',
        ]);
        // Note: Accept is text/html, not json
        $session = app('session.store');
        $request->setLaravelSession($session);

        $response = $controller->update($request);

        $this->assertTrue($response->isRedirection());
        $this->assertEquals('Prepaid Account:Low Balance.', session('error'));
    }

    /**
     * Scenario: Inactive account declines.
     */
    public function test_inactive_account_declines(): void
    {
        $this->account->status = 0;
        $this->account->save();

        $controller = new \App\Http\Controllers\TripController();
        $request = Request::create('/admin/add-payment', 'POST', [
            'trip' => $this->trip->trip_id,
            'payment_method' => 'account',
            'account' => $this->account->account_id,
            'amount' => 35.00,
            'account_pin' => '1234',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);

        $this->assertFalse($data['status']);
        $this->assertEquals('Account is inactive.', $data['message']);
    }

    /**
     * Scenario 3: Balance is SUFFICIENT.
     * Expected: Deducted directly from balance without charging card.
     */
    public function test_sufficient_balance_deducts_directly(): void
    {
        $this->account->auto_charge_trip_cost = 0;
        $this->account->balance = 100.00;
        $this->account->save();

        $controller = new \App\Http\Controllers\TripController();
        $request = Request::create('/admin/add-payment', 'POST', [
            'trip' => $this->trip->trip_id,
            'payment_method' => 'account',
            'account' => $this->account->account_id,
            'amount' => 35.00,
            'account_pin' => '1234',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);

        $this->assertTrue($data['status']);
        $this->assertEquals('Payment completed successfully', $data['message']);

        // 100.00 - 35.00 = 65.00
        $this->account->refresh();
        $this->assertEquals(65.00, (float) $this->account->balance);

        // Payment record exists
        $this->assertDatabaseHas('payments', [
            'trip_id' => $this->trip->trip_id,
            'user_type' => 'driver',
            'type' => 'credit',
        ]);
    }

    /**
     * Scenario 4: Admin Show page toggle.
     */
    public function test_admin_can_toggle_setting_on_account_show_page(): void
    {
        $adminController = new \App\Http\Controllers\AccountController();

        // 1. Turn ON
        $requestOn = Request::create('/admin/auto_charge_trip_cost', 'POST', [
            'account_id' => $this->account->id,
            'auto_charge_trip_cost' => 'on',
        ]);
        $adminController->auto_charge_trip_cost($requestOn);
        $this->account->refresh();
        $this->assertEquals(1, (int) $this->account->auto_charge_trip_cost);

        // 2. Turn OFF
        $requestOff = Request::create('/admin/auto_charge_trip_cost', 'POST', [
            'account_id' => $this->account->id,
            // no auto_charge_trip_cost checkbox in request
        ]);
        $adminController->auto_charge_trip_cost($requestOff);
        $this->account->refresh();
        $this->assertEquals(0, (int) $this->account->auto_charge_trip_cost);
    }

    /**
     * Scenario 5: Admin Edit page update.
     */
    public function test_admin_can_update_setting_on_account_edit_page(): void
    {
        $adminController = new \App\Http\Controllers\AccountController();

        // Turn ON via edit form
        $requestOn = Request::create('/admin/update_account/' . $this->account->id, 'POST', [
            'f_name' => 'John',
            'email' => $this->account->email,
            'phone' => $this->account->phone,
            'status' => 1,
            'auto_charge_trip_cost' => '1',
            'paypertrip' => 'off',
            'autofill' => 'off',
        ]);
        $adminController->update($requestOn, $this->account->id);
        $this->account->refresh();
        $this->assertEquals(1, (int) $this->account->auto_charge_trip_cost);

        // Turn OFF via edit form
        $requestOff = Request::create('/admin/update_account/' . $this->account->id, 'POST', [
            'f_name' => 'John',
            'email' => $this->account->email,
            'phone' => $this->account->phone,
            'status' => 1,
            'auto_charge_trip_cost' => '0',
            'paypertrip' => 'off',
            'autofill' => 'off',
        ]);
        $adminController->update($requestOff, $this->account->id);
        $this->account->refresh();
        $this->assertEquals(0, (int) $this->account->auto_charge_trip_cost);
    }

    /**
     * Scenario 6: Customer settings page update.
     */
    public function test_customer_can_update_setting_on_customer_settings_page(): void
    {
        $userPortalController = new \App\Http\Controllers\UserPortalController();

        // Login as customer
        Auth::guard('customer')->login($this->account);

        // Turn ON
        $requestOn = Request::create('/customer/settings/update', 'POST', [
            'f_name' => 'John',
            'email' => $this->account->email,
            'phone' => $this->account->phone,
            'auto_charge_trip_cost' => '1',
            'autofill' => 'off',
        ]);
        $userPortalController->updateSettings($requestOn);
        $this->account->refresh();
        $this->assertEquals(1, (int) $this->account->auto_charge_trip_cost);

        // Turn OFF
        $requestOff = Request::create('/customer/settings/update', 'POST', [
            'f_name' => 'John',
            'email' => $this->account->email,
            'phone' => $this->account->phone,
            'auto_charge_trip_cost' => '0',
            'autofill' => 'off',
        ]);
        $userPortalController->updateSettings($requestOff);
        $this->account->refresh();
        $this->assertEquals(0, (int) $this->account->auto_charge_trip_cost);
    }
}
