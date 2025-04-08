<?php

namespace App\Imports;


use App\Models\Account;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;

class UsersImport implements ToCollection,ToArray
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {


        $count = 0;
        foreach($rows as $k=>$row){

            if($k > 0){

                $trip_id = trim($row[0]);
                $account_id = trim($row[1]);

                if(Account::where('account_id',$account_id)->exists()){

                    $trip = Trip::where('trip_id',$trip_id)->first();
                    if($trip){

                        $trip->payment_method = 'account';
                        $trip->account_number = $account_id;
                        $trip->dev_status = 'updated_by_excel_of_cube';
                        $trip->save();
                        $count++;

                    }
                    }


                }


        }

    }

    public function array(array $array)
    {
       
        foreach ($array as $row) {
           
        }
    }
}
