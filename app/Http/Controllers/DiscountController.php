<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Account;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $discounts = Discount::where('is_deleted' , 0); 
            return Datatables::of( $discounts)
            ->addColumn('discount', function ($row) {
                $discount = $row->percentage;
                return $discount;
            })
            ->addColumn('start_date', function ($row) {
                $start_date = $row->start_date;
                return $start_date;
            })
            ->addColumn('end_date', function ($row) {
                $end_date = $row->end_date;
                return $end_date;
            })
            ->addColumn('status', function ($row) {
                $status = $row->status == 1 ? 'Active' : 'Inactive';
                return $status ;
            })
            ->addColumn('actions', function ($row) {

                $html = '<a href="' . url('admin/edit/discount/' . $row->id) . '" class="btn-sm btn-primary">
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

        $accounts = Account::where('status' , 1)->get();
       
        return view("admin.discount.index", compact('accounts'));
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
        $discount = new Discount;
        $discount->percentage = $request->percentage;
        $discount->start_date = $request->start_date;
        $discount->end_date = $request->end_date;
        $discount->save();

        $discount->accounts()->attach($request->accounts);
        return redirect()->back()->with("success","Discount is added successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $id)
    {
        $discount = Discount::find($id);
        $accounts = Account::where('status' , 1)->get();

        return view("admin.discount.edit", compact("discount","accounts"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
         
        $discount = Discount::find($id);
        $discount->percentage = $request->percentage;
        $discount->start_date = $request->start_date;
        $discount->end_date = $request->end_date;
        $discount->status = $request->status;
        $discount->save(); 
        $discount->accounts()->sync($request->accounts);
        return redirect()->back()->with('success', 'Discount is updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
         $id = $request->id;
         $discount = Discount::find($id);
         $discount->is_deleted = 1;
         $discount->save(); 
         return redirect()->back()->with("success","Discount is deleted successfully");

    }
}
