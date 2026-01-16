<?php

namespace App\Http\Controllers;

use App\Models\Drivers_payments_info;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriversPaymentsInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $driver_id = $request->input('driver_id');
        $paymentInfo = new Drivers_payments_info();
        $paymentInfo->driver_id = $driver_id;
        $paymentInfo->address_line1 = $request->input('address_line1');
        $paymentInfo->address_line2 = $request->input('address_line2');
        $paymentInfo->city = $request->input('city');
        $paymentInfo->state_code = $request->input('state_code');
        $paymentInfo->postal_code = $request->input('postal_code');
        $paymentInfo->country_code = $request->input('country_code');
        $paymentInfo->bussiness_name = $request->input('bussiness_name');
        $paymentInfo->email = $request->input('email');
        $paymentInfo->language = $request->input('language', 'en');
        $paymentInfo->currency = $request->input('currency', 'USD');
        $paymentInfo->send_notifications =  true;
        $paymentInfo->save();

        return redirect()->back()->with('success', 'Driver payment information added successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Drivers_payments_info $drivers_payments_info)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drivers_payments_info $drivers_payments_info)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paymentInfo = Drivers_payments_info::findOrFail($id);
        $paymentInfo->address_line1 = $request->input('address_line1');
        $paymentInfo->address_line2 = $request->input('address_line2');
        $paymentInfo->city = $request->input('city');
        $paymentInfo->state_code = $request->input('state_code');
        $paymentInfo->postal_code = $request->input('postal_code');
        $paymentInfo->country_code = $request->input('country_code');
        $paymentInfo->bussiness_name = $request->input('bussiness_name');
        $paymentInfo->email = $request->input('email');
        $paymentInfo->language = $request->input('language', 'en');
        $paymentInfo->currency = $request->input('currency', 'USD');
        $paymentInfo->send_notifications =  true;
        $paymentInfo->save();

        return redirect()->back()->with('success', 'Driver payment information updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drivers_payments_info $drivers_payments_info)
    {
        //
    }
}
