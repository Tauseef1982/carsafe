<?php

use App\Http\Controllers\CouponController;
use App\Mail\TripEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TripController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\CarImageController;
use App\Http\Controllers\AccountComplaintController;
use App\Http\Controllers\DriverComplaintController;
use App\Models\Trip;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
admin dashboard routes starts
*/
Route::get('process', [TripController::class, 'processPrepaidAccountDeductions']);

Route::group(['prefix' => 'admin','as' => 'admin.'], function () {

    Route::get('/login', [AdminController::class, 'login'])->name('login');
    Route::post('/login', [AdminController::class, 'attemptLogin'])->name('attempt.login');;
    Route::get('/logout', [AdminController::class, 'logout'])->name('logout');

Route::group(['middleware' => 'admin.auth'], function () {

        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/drivers', [AdminController::class, 'drivers'])->name('drivers.index');
        Route::post('/update-beta', [AdminController::class, 'updateBeta'])->name('updateBeta');

        Route::get('/inactive-drivers', [AdminController::class, 'inactivedrivers'])->name('drivers.inactive');
        Route::get('/driver/{id}', [AdminController::class, 'driver']);
        Route::get('/driver/inactive/{id}', [AdminController::class, 'driver_inactive']);
        Route::get('/driver/active/{id}', [AdminController::class, 'driver_active']);
        Route::post('/change-driver-fee', [AdminController::class, 'changeDriverFee'])->name('change-driver-fee');
        Route::post('/change-driver-plate', [AdminController::class, 'changeDriverplate'])->name('change-driver-plate');
        Route::get('/ajax-driver', [\App\Http\Controllers\DriverController::class, 'index']);
        Route::get('/payments', [AdminController::class, 'payments']);
        Route::post('/payment-from-driver', [AdminController::class, 'paymentFromDriver'])->name('pay-from-driver');
        Route::post('/payment-to-driver', [AdminController::class, 'paymentToDriver'])->name('pay-to-driver');
        Route::post('/weekly-fee-from-balance', [AdminController::class, 'payWeeklyFromBalance'])->name('weekly-fee-from-balance');
        Route::get('/dispatchers', [AdminController::class, 'dispatchers']);
        Route::get('/adjustments', [AdminController::class, 'adjustments']);
        Route::any('/adjustment', [AdminController::class, 'adjustment'])->name('adjustments.save');
        Route::get('/trips', [AdminController::class, 'trips'])->name('trips');
        Route::any('/trips2', [AdminController::class, 'dataTabletrips'])->name('dataTabletrips');
        Route::any('/trips_complaint', [AdminController::class, 'dataTabletripsComplaint'])->name('dataTabletripsComplaint');
        Route::any('/trips_extra', [AdminController::class, 'dataTabletripsExtra'])->name('dataTabletripsExtras');
        Route::any('/tripswithoutestimatedcost', [AdminController::class, 'tripswithoutestimatedcost'])->name('tripswithoutestimatedcost');
        Route::get('/get-update-prices-modal', [TripController::class, 'getUpdatePricesModal']);
        Route::get('/get-trip-payments', [TripController::class, 'getTripPayments']);
        Route::get('/get-single-payment-modal', [TripController::class, 'getSinglePayment']);
        Route::post('/update-single-payment-modal', [TripController::class, 'updateSinglePayment']);
        Route::delete('/delete-single-payment', [TripController::class, 'deleteSinglePayment']);

        Route::any('/trips_manually', [AdminController::class, 'dataTableManuallyTripstrips'])->name('dataTableManuallyTripstrips');
        //customer pay to gocab from this account credit card
        Route::post('/pay-account-to-gocab', [AccountController::class, 'payFromAccountToCab']);

        Route::get('accounts', [AccountController::class, 'index'])->name('show_account');

        Route::get('/accounts/invoices', [\App\Http\Controllers\AccountController::class, 'invoices']);
        Route::get('account-invoice/retry/{id}',[\App\Http\Controllers\AccountController::class, 'invoices_retry']);
        Route::get('/account-invoice/send-email/{hash}', [\App\Http\Controllers\AccountController::class, 'invoiceSendEmail'])->name('unauth.invoice.pay');
        Route::post('accounts/sendBulkInvoiceEmail', [AccountController::class, 'sendBulkInvoiceEmail'])->name('sendBulkInvoiceEmail');
        Route::post('add_account',[AccountController::class, 'create']);
        Route::get('show/account/{id}',[AccountController::class, 'show']);
        Route::post('delete/account/',[AccountController::class, 'destroy']);
        Route::get('edit/account/{id}',[AccountController::class, 'edit']);
        Route::post('update_account/{id}',[AccountController::class, 'update']);
        Route::post('account/status/{id}',[AccountController::class, 'status']);
        Route::get('accounts/complaints', [AccountComplaintController::class, 'index']);
        Route::get('accounts/cron-postpaid', [AccountComplaintController::class, 'cronPostpaid']);
        Route::post('accounts/cron-postpaid', [AccountComplaintController::class, 'cronPostpaidSubmit']);
        Route::get('update_complaint_status/{id}', [AccountComplaintController::class, 'edit']);
        Route::post('edit_complaint/{id}', [AccountComplaintController::class, 'update']);
        Route::get('trip/pay/{id}',[AdminController::class, 'payingTripAccountMethod']);
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');

//        Route::post('/add-payment', [AdminController::class, 'payTripAccountMethod']);

        Route::get('/accounts/cards', [CreditCardController::class, 'index']);
        Route::post('/add/credit-card', [CreditCardController::class, 'store']);
        Route::post('/add/ach', [CreditCardController::class, 'store']);
        Route::post('/update_creditcard/{id}', [CreditCardController::class, 'update']);
        Route::get('/edit/creditcard/{id}', [CreditCardController::class, 'edit']);
        Route::post('/delete/card/{id}', [CreditCardController::class, 'destroy']);
        Route::get('/accounts/payments', [AccountController::class, 'accountPayments']);
        Route::post('/pay-to-gocab', [AccountController::class, 'paymentToGocab'])->name('pay-to-gocab');

        Route::post('/pay-to-refill', [AccountController::class, 'paymentToRefill']);
        // Route::post('/pay-to-refill', [AccountController::class, 'paymentToRefill'])->name('pay-to-refill');
        Route::get('/ajax-payments-account', [AccountController::class, 'getPaymentsAccount'])->name('ajax-payments-account');
        Route::get('/ajax-get-totals', [AccountController::class, 'ajaxGetTotals']);
        Route::get('/ajax-batch-payments', [AccountController::class, 'getBatchPayments']);
        Route::get('/ajax-payments-for-batch', [AccountController::class, 'getPaymentsForBatch']);

        Route::post('/add-payment',[TripController::class,'update']);
        Route::post('/add-payment-new',[TripController::class,'updateNew']);
        Route::post('/register-complaint', [TripController::class,'register_complaint']);

        Route::post('/update-cost',[TripController::class, 'update_cost']);
        Route::post('/update-account',[TripController::class, 'update_account']);
        Route::post('/update-charges',[TripController::class, 'update_charges']);
        Route::get('/deduction-history-remove',[TripController::class, 'deductionRemove']);
        Route::get('/ajax-trips',[TripController::class, 'ajaxTrips']);
        Route::get('/ajax-trips-account',[TripController::class, 'ajaxTripsAccount']);

        Route::post('/delete/weekfee', [AdminController::class, 'delete_weekfee']);
        Route::get('/dublicate/drivers', [AdminController::class, 'dublicateDrivers']);
        Route::get('/success/{id}', function ($id) { return view('admin.success',compact('id')); });

        Route::any('/invoice/preview' , [AccountController::class, 'show_invoice']);

        // discount routes

        Route::get('discounts', [DiscountController::class,'index']);
        Route::post('add_discount', [DiscountController::class,'store']);
        Route::post('discount/delete', [DiscountController::class,'destroy']);
        Route::get('edit/discount/{id}', [DiscountController::class,'edit']);
        Route::post('discount/update/{id}', [DiscountController::class,'update']);
        // car images routes
        Route::post('add_images', [CarImageController::class, 'store']);
        Route::get('/get-uploaded-images/{id}', [CarImageController::class, 'index']);
        Route::get('/delete-image/{id}', [CarImageController::class, 'destroy']);
        //exprt execl route
        Route::get('/export-drivers-earnings', [AdminController::class, 'export'])->name('export.drivers.earnings');

        //coupon

        Route::get('coupons', [CouponController::class,'index']);
        Route::post('add_coupon', [CouponController::class,'store']);
        Route::post('coupon/delete', [CouponController::class,'destroy']);
        Route::get('edit/coupon/{id}', [CouponController::class,'edit']);
        Route::post('coupon/update/{id}', [CouponController::class,'update']);

        //driver complaints routes

        Route::get('/driver_complaints', [DriverComplaintController::class, 'index']);
        Route::post('/add_complaint' , [DriverComplaintController::class, 'store']);


    });

});

/*
admin dashboard routes ends
*/

Route::get('/', [\App\Http\Controllers\DriverController::class, 'login'])->name('driver.login');
Route::get('/login', [\App\Http\Controllers\DriverController::class, 'login']);
Route::post('/send-otp', [\App\Http\Controllers\DriverController::class, 'sendOtp'])->name('send-otp');
Route::get('/send-otp-form', [\App\Http\Controllers\DriverController::class, 'showOtpForm'])->name('send-otp-form');
Route::post('/verify-otp', [\App\Http\Controllers\DriverController::class, 'verifyOtp']);
Route::get('/logout', [\App\Http\Controllers\DriverController::class, 'logout'])->name('logout');

Route::post('/genToken', [CreditCardController::class, 'genToken'])->name('cardknox-genToken');
Route::get('start-again/{id}', [TripController::class,'start_payment_again']);

Route::group(['middleware' => 'driver.auth','as' => 'driver.'], function () {

    Route::get('/dashboard', [\App\Http\Controllers\DriverController::class, 'index'])->name('dashboard');
    Route::get('/single-trip/{id}', [TripController::class, 'show']);

    Route::get('/payment', [TripController::class, 'latestThree']);
    Route::get('/payment-new', [TripController::class, 'latestThreeNew'])->middleware('throttle:20,1');
    Route::get('/trip_history', [TripController::class, 'alltrips'])->middleware('throttle:20,1');
    Route::post('/add-payment', [TripController::class, 'update'])->middleware('throttle:20,1');
    Route::post('/add-payment-new', [TripController::class, 'updateNew'])->middleware('throttle:20,1');

    Route::post('register-complaint', [TripController::class,'register_complaint']);
    Route::get('/success', function () { return view('driver.success'); });
    Route::get('/success/{trip_id}', function ($id) {


        $trip = Trip::where('trip_id',$id)->first();
        $trip_id = $trip->trip_id;
        $paid_cost = $trip->trip_cost;

        \Log::info('cpmlaint-working');
        return view('driver.success', compact('trip_id', 'paid_cost'));


    });

});

    Route::get('/driver/{id}', [\App\Http\Controllers\DriverController::class, 'driver']);
    Route::get('/taxidrivers', [\App\Http\Controllers\DriverController::class, 'taxidrivers']);
    Route::get('/account-invoice/{hash}', [\App\Http\Controllers\AccountController::class, 'invoiceViewOrPay'])->name('unauth.invoice.view');
    Route::post('/account-invoice/{hash}', [\App\Http\Controllers\AccountController::class, 'invoicePay'])->name('unauth.invoice.pay');

    Route::get('/checkwebhook/{id}', [TripController::class, 'checkwebhook']);
//    Route::get('/cubePayment', [TripController::class, 'cubePayment'])->middleware('throttle:10,1');

//    Route::get('/add-dispatchers', [DriverController::class, 'addDispatchersToUsers']);
//    Route::get('/delete-dub/{id}', [AccountController::class, 'deleteDInvcoie']);
//    Route::get('account_ids', [AccountController::class, 'account_ids']);
//    Route::post('upload-excel', [AccountController::class, 'uploadExcel'])->name('upload.excel');
    Route::get('account_complaint/{hash}', [\App\Http\Controllers\AccountController::class, 'account_complaint'])->name('add_complaint');
    Route::post('add_account_complaint', [AccountComplaintController::class, 'store']);

// //////////////// customer routes///////////////////////

Route::group(['prefix' => 'customer','as' => 'customer.'], function () {


    Route::get('/login', [\App\Http\Controllers\UserPortalController::class, 'login'])->name('login');
    Route::post('/login', [\App\Http\Controllers\UserPortalController::class, 'loginAttemp']);
    Route::get('/logout', [\App\Http\Controllers\UserPortalController::class, 'logout']);
    Route::get('/reset_password', [\App\Http\Controllers\UserPortalController::class, 'reset_password']);
    Route::post('/reset_password', [\App\Http\Controllers\UserPortalController::class, 'reset_password_email']);
    Route::get('/change_password', [\App\Http\Controllers\UserPortalController::class, 'change_password']);
    Route::post('/change_password', [\App\Http\Controllers\UserPortalController::class, 'update_password']);
    Route::group(['middleware' => 'customer.auth'], function () {


    Route::get('/', [\App\Http\Controllers\UserPortalController::class, 'index'])->name('dashboard');
    Route::get('/index', [\App\Http\Controllers\UserPortalController::class, 'index']);
    Route::get('/trips', [\App\Http\Controllers\UserPortalController::class, 'trips']);
    Route::get('/cards', [\App\Http\Controllers\UserPortalController::class, 'creditCards']);
    Route::get('/editcard/{id}', [\App\Http\Controllers\UserPortalController::class, 'editCreditCard']);
    Route::post('/add/credit-card', [CreditCardController::class, 'store']);
    Route::post('/update_creditcard/{id}', [\App\Http\Controllers\UserPortalController::class, 'updateCreditCard']);
    Route::post('/delete/card/{id}', [\App\Http\Controllers\UserPortalController::class, 'deleteCard']);
    Route::get('/invoices', [\App\Http\Controllers\UserPortalController::class, 'invoices']);
    Route::get('/payments', [\App\Http\Controllers\UserPortalController::class, 'payments']);
    Route::get('/settings', [\App\Http\Controllers\UserPortalController::class, 'settings']);
    Route::post('/settings/update', [\App\Http\Controllers\UserPortalController::class, 'updateSettings']);
    Route::post('/pay-to-refill', [AccountController::class, 'paymentToRefill']);


    });

});

include('tests.php');



