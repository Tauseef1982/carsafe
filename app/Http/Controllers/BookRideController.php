<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CustomerBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Services\TokenService;


class BookRideController extends Controller{

    public function index(){
         $user = Auth::guard('customer')->user()->account_id;
        $account = Account::where('account_id',$user)->first();
        return view('customer.book-ride', compact('account'));

    }

public function store(Request $request)
{

    try {
        $account = Account::where('account_id', $request->account_id)->first();

        if (!$account) {
            return back()->with('error', 'Account not found.');
        }

            $tagvehicle1 = false;
            $tagvehicle2 = false;

            if ($request->driver_type === 'male') {
                $tagvehicle1 = true;
            } elseif ($request->driver_type === 'female') {
                $tagvehicle2 = true;
            } elseif ($request->driver_type === 'both') {
                $tagvehicle1 = false;
                $tagvehicle2 = false;
            }




        $account_id = $account->account_id;
        $name = $account->f_name;
        $email = $account->email;
        $phone = $request->phone_number;
        $pickup_address = $request->pickup_location;
        $pickup_lat = $request->pickup_lat;
        $pickup_lng = $request->pickup_lng;
        $drop_location = $request->drop_location;
        $drop_lat = $request->drop_lat;
        $drop_lng = $request->drop_lng;

        $token = TokenService::token();


        $bookingData = [
            "order" => [
                "company_id" => 48647,
                "provider_id" => 0,
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
                                    (int) ($pickup_lng * 1e6),
                                    (int) ($pickup_lat * 1e6),
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
                                    (int) ($drop_lng * 1e6),
                                    (int) ($drop_lat * 1e6),
                                ],
                            ],
                            "times" => null,
                            "info" => (object) [],
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
        //dd(json_encode($bookingData, JSON_PRETTY_PRINT));

        $response = Http::withHeaders([
    'Authorization' => 'Bearer ' . $token,
    'Content-Type' => 'application/json',
      ])->post('https://api.taxicaller.net/api/v1/booker/order', $bookingData);


        if ($response->successful()) {

            $data = $response->json();
            $orderToken = $data['order_token'] ?? null;
            $orderId = $data['order']['order_id'] ?? null;

            $new = new CustomerBooking();
            $new->order_id  = $orderId;
            $new->account_id  = $account_id;
            $new->data = json_encode($data);
            $new->save();

            return back()->with([
                'success' => 'Booking successful!',
                'order_token' => $orderToken,
                'order_id' => $orderId,
            ]);
        }

        return back()->with('error', 'API Error: ' . $response->body());

    } catch (\Throwable $e) {

        return back()->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}

}
