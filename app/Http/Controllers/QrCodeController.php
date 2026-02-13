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

//     $qrImage = base64_encode(
//   FacadesQrCode::format('png')->size(300)->generate($code)
//     );
$qrImage = base64_encode(
    FacadesQrCode::format('png')
        ->size(300)
        ->color(0, 0, 0)        // QR (foreground) = black
        ->backgroundColor(255, 255, 255) // background = white
        ->generate($code)
);


    return response()->json([
        'code' => $code,
        'expires_at' => $record->expires_at,
        'qr_code' => 'data:image/png;base64,' . $qrImage,
    ]);
}

public function verify(Request $request)
{
    $token = $request->input('code');
     $data = json_decode($token, true);

     if (!$data) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid QR format'
        ], 400);
    }

    // Validate required fields
    if (!isset($data['Store'], $data['Ticket'], $data['Expires'])) {
        return response()->json([
            'status' => false,
            'message' => 'Incomplete QR data'
        ], 400);
    }
     $storeAccountMap = [
        "Landau's KJ" => 8553,
    ];
  if (!array_key_exists($data['Store'], $storeAccountMap)) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized store'
        ], 403);
    }
     $accountId = $storeAccountMap[$data['Store']];
      $expiresAt = Carbon::createFromFormat('m/d/y h:i A', $data['Expires']);

    if (Carbon::now()->greaterThan($expiresAt)) {
        return response()->json([
            'status' => false,
            'message' => 'QR code expired'
        ], 410);
    }
    $alreadyUsed = QrCode::where('code', $data['Ticket'])->exists();

    if ($alreadyUsed) {
        return response()->json([
            'status' => false,
            'message' => 'QR code already used'
        ], 409);
    }

     $qr = new QrCode();
    $qr->account_id = $accountId;
    $qr->code = $data['Ticket'];
    $qr->expires_at = $expiresAt;
    $qr->save();

     return response()->json([
        'status' => true,
        'message' => 'QR code valid',
        'account_id' => $accountId,
        'ticket' => $data['Ticket']
    ]);
}


}
