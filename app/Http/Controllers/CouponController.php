<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Coupon;
use App\Models\Driver;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $coupons = Coupon::where('is_deleted' , 0);
            return Datatables::of( $coupons)
                ->addColumn('status', function ($row) {
                    $status = $row->status == 1 ? 'Active' : 'Inactive';
                    return $status ;
                })
                ->addColumn('actions', function ($row) {

                    $html = '<a href="' . url('admin/edit/Coupon/' . $row->id) . '" class="btn-sm btn-primary">
                    <i class="fa fa-pencil"></i>
                </a>


                <a class="btn-sm btn-danger btn_trash" data-bs-toggle="modal"
                        data-original-title="test" data-bs-target="#exampleModal" data-id=' . $row->id . '>
                    <i class="fa fa-trash"></i>
                </a>';
                    return $html;
                })
                ->rawColumns(['actions'])
                ->make();

        }

        $drivers = Driver::where('status',1)->get();

        return view("admin.coupon.index", compact('drivers'));
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
        $coupon = new Coupon();
        $coupon->name = $request->name;
        $coupon->price = $request->price;
        $coupon->status = $request->status;
        $coupon->save();

        $coupon->drivers()->attach($request->accounts);
        return redirect()->back()->with("success","Coupon is added successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $id)
    {
        $coupon = Coupon::find($id);
        $accounts = Account::where('status' , 1)->get();

        return view("admin.coupon.edit", compact("Coupon","accounts"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {

        $coupon = Coupon::find($id);
        $coupon->name = $request->name;
        $coupon->price = $request->price;
        $coupon->status = $request->status;
        $coupon->save();
        $coupon->accounts()->sync($request->accounts);
        return redirect()->back()->with('success', 'Coupon is updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        $coupon = Coupon::find($id)->delete();
        return redirect()->back()->with("success","Coupon is deleted successfully");

    }
}
