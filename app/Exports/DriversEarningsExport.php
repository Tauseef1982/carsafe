<?php

namespace App\Exports;

use App\Models\Driver;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DriversEarningsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $fromlastweek = Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $tolastweek = Carbon::now()->subWeek()->endOfWeek(Carbon::SATURDAY)->toDateString();


         
   
        // return Driver::where('role' , 'LIKE' , '%"DRIVER"%' )
        // ->where('status', 1)->with(['trips' => function ($query) use ($fromlastweek, $tolastweek) {
        //     $query->where('date', '>=', $fromlastweek)
        //           ->where('date', '<=', $tolastweek)   
        //           ->where('status', 'not like', '%Cancelled%') 
        //           ->where('is_delete', 0);  
        // }])
        // ->get()
        // ->map(function ($driver) use ($fromlastweek, $tolastweek) {
        //     $totalEarnings = $driver->trips->sum(function ($trip) {
                
        //         return $trip->trip_cost;
        //     });
            


        $drivers = Driver::where('role','LIKE','%"DRIVER"%')
         ->where('status', 1)
            ->with(['trips' => function ($query) use ($fromlastweek, $tolastweek) {
                $query->whereBetween('date', [$fromlastweek, $tolastweek])
                    ->where('payment_method' , '!=' , 'cash')
                    ->where('is_delete', 0); 
            }])
            ->get();

        $drivers = $drivers->map(function ($driver) {
            // Filter trips for valid conditions
            $validTrips = $driver->trips->filter(function ($trip) {
                return $trip;

            });

            // Calculate total earnings
            $totalEarnings = $validTrips->sum('trip_cost');

            return [
                'Driver Username' => $driver->username,
                'Total Earnings' => $totalEarnings,
            ];
        });

// Convert to array if needed
        $drivers = $drivers->values();

        return $drivers;


       

       
    }

 
    public function headings(): array
    {
        return ["Driver Username", "Total Earnings Last week"];
    }
}
