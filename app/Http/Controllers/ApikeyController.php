<?php

namespace App\Http\Controllers;

use App\Models\Apikey;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\CustomerBooking;
use Illuminate\Support\Facades\Http;
use App\Services\TokenService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ApikeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $user = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id' , $user)->first();


        $apikey = Apikey::where('account_id', $account->account_id)->first();
        $codeapikey = DB::table('qrcodeapis')->where('account_id', $account->account_id)->first();
        return view('customer.apikey' , compact('apikey', 'account' , 'codeapikey'));
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
         $key = hash('sha256', Str::random(60));

    ApiKey::updateOrCreate(
        ['account_id' => $request->account_id],
        ['api_key' => $key, 'is_active' => true]
    );

    return back()->with('success', 'API Key generated successfully');
        return $request->all();
    }

    /**
     * Display the specified resource.
     */
    public function show(Apikey $apikey)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Apikey $apikey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apikey $apikey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Apikey $apikey)
    {
        //
    }


    public function createTrip(Request $request)
    {
         try {

        $account = Account::where('account_id', $request->account_id)->first();

        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

            $tagvehicle1 = false;
            $tagvehicle2 = false;

            $driverType = $request->input('driver_type');

            if ($driverType === 'male') {
                $tagvehicle1 = true;
            } elseif ($driverType === 'female') {
                $tagvehicle2 = true;
            } else  {
                $tagvehicle1 = false;
                $tagvehicle2 = false;
            }


            $account_id = $account->account_id;
            $name = $account->f_name;
            $email = $account->email;
            $phone = $request->phone_number;
            $pickup_address = $request->pickup_location;
            // $pickup_lat = $request->pickup_lat;
            // $pickup_lng = $request->pickup_lng;
            $drop_location = $request->drop_location;
            // $drop_lat = $request->drop_lat;
            // $drop_lng = $request->drop_lng;

            $token = TokenService::token();


            $bookingData = [
                "order" => [
                    "company_id" => 48647,
                    "provider_id" => 72679,
                    "auto_assign" => true,

                    "items" => [
                        [
                            "@type" => "passengers",
                            "seq" => 0,
                            "passenger" => [
                                'name' => $name,
                                'email' => $email,
                                'phone' => $phone,
                            ],


                            "client_id" => 0,
                            "account" => [
                                "id" => 0,
                                "extra" => null
                            ],
                            "require" => [
                                "seats" => 1,
                                "wc" => 0,
                                "bags" => 1
                            ],
                            "pay_info" => [
                                [
                                    "@t" => 0,
                                    "data" => null
                                ]
                            ],
                            "custom_fields" => [
                                "tag.vehicle.1" => $tagvehicle1,
                                "tag.vehicle.2" => $tagvehicle2,
                                "tag.vehicle.3" => false
                            ],

                        ]
                    ],
                    "route" => [
                        "nodes" => [
                            [
                                "actions" => [
                                    [
                                        "@type" => "client_action",
                                        "item_seq" => 0,
                                        "action" => "in"
                                    ]
                                ],
                                'location' => [
                                    'name' => $pickup_address,
                                    'coords' => [
                                        // (int)($pickup_lng * 1e6),
                                        // (int)($pickup_lat * 1e6),
                                    ],
                                ],
                                "times" => [
                                    "arrive" => [
                                        "target" => 0,
                                        "latest" => 0
                                    ]
                                ],

                                "seq" => 0
                            ],
                            [
                                "actions" => [
                                    [
                                        "@type" => "client_action",
                                        "item_seq" => 0,
                                        "action" => "out"
                                    ]
                                ],
                                'location' => [
                                    'name' => $drop_location,
                                    'coords' => [
                                        // (int)($drop_lng * 1e6),
                                        // (int)($drop_lat * 1e6),
                                    ],
                                ],
                                "times" => null,
                                "info" => (object)[],
                                "seq" => 1
                            ]
                        ],
                        "legs" => [
                            [
                                "meta" => [
                                    "dist" => 0,
                                    "est_dur" => 0
                                ],
                                "pts" => [
                                    //15621480
                                ],
                                "from_seq" => 0,
                                "to_seq" => 1
                            ]
                        ],
                        "meta" => [
                            "dist" => 0,
                            "est_dur" => 0
                        ]
                    ]
                ]
            ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . TokenService::token(),
            'Content-Type' => 'application/json',
        ])->post('https://api.taxicaller.net/api/v1/booker/order', $bookingData);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'TaxiCaller API error',
                'details' => $response->json()
            ], 422);
        }

        $data = $response->json();

        CustomerBooking::create([
            'order_id'   => $data['order']['order_id'],
            'account_id' => $account->account_id,
            'data'       => json_encode($data),
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $data['order']['order_id'],
            'order_token' => $data['order_token']
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Server error',
            'message' => $e->getMessage()
        ], 500);
    }
    }

    public function generateCodeApiKey(Request $request)
    {
        $key = hash('sha256', Str::random(60));
        DB::table('qrcodeapis')->updateOrInsert(
            ['account_id' => $request->account_id],
            ['api_key' => $key, 'is_active' => true]
        );
        return back()->with('success', 'API Key for QR is generated successfully');
    }
}
