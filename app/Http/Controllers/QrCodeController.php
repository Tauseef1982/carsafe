<?php

namespace App\Http\Controllers;

use App\Models\QrCode;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as FacadesQrCode;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QrCodeController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(QrCode $qrCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QrCode $qrCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QrCode $qrCode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QrCode $qrCode)
    {
        //
    }


     public function generate(Request $request)
{
    $code = strtoupper(Str::random(10));

    $record = QrCode::create([
        'account_id' => $request->account_id,
        'code' => $code,
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $qrImage = base64_encode(
  FacadesQrCode::format('png')->size(300)->generate($code)
    );

    return response()->json([
        'code' => $code,
        'expires_at' => $record->expires_at,
        'qr_code' => 'data:image/png;base64,' . $qrImage,
    ]);
}


}
