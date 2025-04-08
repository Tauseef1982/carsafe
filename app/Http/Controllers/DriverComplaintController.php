<?php

namespace App\Http\Controllers;

use App\Models\driverComplaint;
use Illuminate\Http\Request;

class DriverComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $complaints = driverComplaint::all();
        return view('admin.driver.driver_complaint', compact('complaints'));
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
        $complaint = new driverComplaint;
        $complaint->driver_id = $request->driver_id;
        $complaint->account_id = $request->account_id;
        $complaint->admin_username = $request->admin_username;
        $complaint->trip_id = $request->trip_id;
        $complaint->description = $request->description;
        $complaint->status = 'pending';
        $complaint->save();
        return redirect()->back()->with('success', 'Complaint added successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(driverComplaint $driverComplaint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(driverComplaint $driverComplaint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, driverComplaint $driverComplaint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(driverComplaint $driverComplaint)
    {
        //
    }
}
