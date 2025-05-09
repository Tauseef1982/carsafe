<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\CardKnoxService;

class CreditCardController extends Controller
{
    /**
     * Display a listinof the resource.
     */
    public function index(Request $request)
    {

        $creditcards = CreditCard::where('is_deleted' , 0);

        if(isset($request->tab)){
        if($request->tab == 'from_driver'){

            $creditcards = $creditcards->where('type','ach');

        }else{
            $creditcards = $creditcards->where('type','!=','ach');

        }
        }else{
            $creditcards = $creditcards->where('type','!=','ach');


        }
        $creditcards = $creditcards->get();

        $accountIds = Account::where('is_deleted', 0)->pluck('account_id');
        return view('admin.creditcards', compact('creditcards','accountIds'));
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

//        dd($request->all());
        $checkCard = CreditCard::where('account_id', $request->account_id)->first();
        $creditCard = new CreditCard;
        $creditCard->account_id = $request->account_id;

        if ($checkCard) {

            $creditCard->charge_priority = 0;

        }
        if ($request->type === 'ach') {

            $account = Account::where('account_id',$request->account_id)->first();
            $creditCard->account_number = $request->account_number;
            $creditCard->routing_number = $request->routing_number;
            $creditCard->type = 'ach';

            $cardResponse = CardKnoxService::saveAch(
                $request->account_id,
                $account->f_name,
                $request->account_number,
                $request->routing_number

            );

        } else {

            $cardNumber = $request->card_number;
            $maskedCard = substr($cardNumber, 0, 1) . str_repeat('*', strlen($cardNumber) - 5) . substr($cardNumber, -4);

            $creditCard->card_number = $maskedCard;
            $creditCard->cvc = $request->cvc;
            // Process expiry date
            $expiry = $request->input('expiry');

            if(isset($request->month) && isset($request->year)){
                $month = $request->month;
                $year = $request->year;
                $expiryWithoutSlash = $month.$year;

            }else{
                list($month, $year) = explode('/', $expiry);
                $expiryWithoutSlash = str_replace('/', '', $expiry);

            }

            $fullYear = '20' . $year;
            $expiryDate = \Carbon\Carbon::createFromDate($fullYear, $month, 1)->toDateString();
            $creditCard->expiry = $expiryDate;

            // Remove the slash from expiry for CardKnoxService

            // Call CardKnoxService to save the card
            $cardResponse = CardKnoxService::saveCard(
                $request->account_id,
                'credit',
                $request->card_number,
                $expiryWithoutSlash,
                $request->card_zip
            );
        }


        // Handle response from CardKnoxService
        if ($cardResponse['status']) {
            $creditCard->cardnox_token = $cardResponse['data']['xToken'];
            $creditCard->save();

            // Return response for AJAX or non-AJAX request
            if($request->ajax()){
                return response()->json(['status' => 'success', 'message' => 'Card added successfully.']);
            } else {
                return redirect()->back()->with("success", "Card added successfully.");
            }
        } else {
            // Handle error response for AJAX or non-AJAX request
            if($request->ajax()){
                return response()->json(['status' => 'error', 'message' => $cardResponse['msg']]);
            } else {
                return redirect()->back()->with("error", $cardResponse['msg']);
            }
        }


    }



    /**
     * Display the specified resource.
     */
    public function show(CreditCard $creditCard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $creditcard = CreditCard::find($id);
        return view('admin.edit-creditcard', compact('creditcard'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {

        $creditcard = CreditCard::find($id);

        $creditcard->account_id = $request->account_id;
        $creditcard->account_number = $request->account_number;
        $creditcard->account_name = $request->account_name;


        $expiry = $request->input('expiry');
        list($month, $year) = explode('/', $expiry);
        $fullYear = '20' . $year;
        $expiryDate = \Carbon\Carbon::createFromDate($fullYear, $month, 1)->toDateString();

        $creditcard->expiry = $expiryDate;
        $expiryWithoutSlash = str_replace('/', '', $expiry);

        $cardResponse = CardKnoxService::saveCard(
            $request->account_id,
            'credit',
            $request->card_number,
            $expiryWithoutSlash,
            $request->card_zip
        );

        if ($cardResponse['status']) {

            $creditcard->card_number = $request->card_number;
            $creditcard->expiry = $expiryDate;
            $creditcard->cvc = $request->cvc;
            $creditcard->cardnox_token = $cardResponse['data']['xToken'];
            $creditcard->charge_priority = $request->charge_priority;
            $creditcard->save();

            if($request->charge_priority == 1){
                CreditCard::where('account_id',$creditcard->account_id)->where('id','!=',$creditcard->id)->update(['charge_priority'=>0]);
            }

        }

        return redirect(url('admin/accounts/cards'))->with("success", "Card is updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        $creditcard = CreditCard::find($id);
        $account_id = $creditcard->account_id;
        $creditcard->is_deleted = 1;
        $creditcard->charge_priority = 0;
        $creditcard->save();

        $primary = CreditCard::where('account_id',$account_id)->where('is_deleted',0)->where('type','credit')->first();

        if($primary){
            $primary->charge_priority = 1;
            $primary->save();
        }
        return redirect()->back()->with("success", "card is deleted  succssfully");
    }

    public function genToken(Request $request)
    {

//        $request->validate([
//
//        ])


       $expiryWithoutSlash = $request->expiryMonth.$request->expiryYear;
        $cardResponse = CardKnoxService::saveCard(
            'onetimecreating',
            'credit',
            $request->cardNumber,
            $expiryWithoutSlash,
            $request->cvc.'001'
        );


        if ($cardResponse['status']) {

            if(request()->ajax()){
                return response()->json(['success' => true, 'token' => $cardResponse['data']['xToken']]);
            }
        }else{

            return response()->json(['success' => false, 'msg' => $cardResponse['msg']]);

        }

    }


}
