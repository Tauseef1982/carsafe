<?php

namespace App\Exports;

use App\Models\Trip;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinanceEarningsExport implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Fetch trips with required fields
        $trips_data = Trip::where('is_delete', 0)
            ->where('payment_method', '!=', 'cash')
            ->whereBetween('date', [$this->from, $this->to])
            ->get(['trip_id', 'trip_cost', 'payment_method', 'account_number']);

        // Map data into rows for Excel
        return $trips_data->map(function ($trip) {
            return [
                'Trip ID'        => $trip->trip_id,
                'Trip Cost'      => $trip->trip_cost,
                'Method'         => $trip->payment_method,
                'Account Number' => $trip->account_number,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Trip ID',
            'Trip Cost',
            'Method',
            'Account Number'
        ];
    }
}
