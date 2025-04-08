<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Account_Complaint;
use App\Models\Trip;
use App\Mail\ComplaintSolved;
use App\Services\PaymentSaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AccountComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $complaints = Account_Complaint::orderBy('id', 'desc')->get();
        return view('admin/account_complaints', compact('complaints'));
    }

    public function cronPostpaid()
    {

        return view('admin.cron_postpaid');
    }

    public function cronPostpaidSubmit(Request $request)
    {

          //dd($request);
        if($request->from_date != '' && $request->to_date != '') {

            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

                $from_date = Carbon::createFromDate($request->from_date)->format('Y-m-d');
                $to_date = Carbon::createFromDate($request->to_date)->format('Y-m-d');



            $accounts = Account::with(['trips' => function ($query) use ($from_date, $to_date) {
                $query->where('payment_method', 'account')
                    ->where('is_delete', 0)
                    ->whereBetween('date', [$from_date, $to_date]);
            }])->where('account_type', 'postpaid')
                ->where('is_deleted', 0)
                ->get();

            $custom_msg = $request->custom_msg ? $request->custom_msg : null;
           // dd($custom_msg);
            $resp = PaymentSaveService::postPaidDeductTripsPayments($accounts, $from_date, $to_date,$custom_msg);

            if($request->ajax()){
                return response()->json($resp);

            }

            Log::info('allPostpaidCronDoneManually');
        }
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

        $trip = Trip::where('trip_id', $request->trip_id)->first();
        if(!$trip){
            return redirect()->back()->with('error', 'Please enter a valid trip Id');
        }
        $complaint = new Account_Complaint();
        $complaint->account_id = $request->account_id;
        $complaint->trip_id = $request->trip_id;
        $complaint->complaint = $request->complaint;
        $complaint->hash_id = $request->hash_id;
        $complaint->save();

        return redirect()->back()->with('success', 'Your Complaint is submitted, our team will contact you soon, now you can submit another claim!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Account_Complaint $account_Complaint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $id)
    {
        $complaint = Account_Complaint::find($id);
        return view('admin/update_complaint', compact('complaint'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $complaint = Account_Complaint::find($id);
        $complaint->status = $request->status;
        $complaint->username = $request->username;
        $complaint->note = $request->note;
        $complaint->save();
        $account = Account::where('account_id',$complaint->account_id)->first();

        $complaintData = [
            'trip_id' => $complaint->trip_id,
            'hash_id' => $complaint->hash_id,
            'note'    => $complaint->note,
        ];

       if($request->send_email == "yes"){
        Mail::to($account->email)->send(new ComplaintSolved($complaintData));
        //Mail::to('meilechwieder@gmail.com')->send(new ComplaintSolved($complaintData));
       //Mail::to('rehman.tuseef757@gmail.com')->send(new ComplaintSolved($complaintData));

       }

        return redirect()->back()->with('success', 'Status changed');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account_Complaint $account_Complaint)
    {
        //
    }
}
