

@foreach($payments as $payment)
    <tr>
        <td>{{$payment->id}}</td>
        <td>{{$payment->driver_id}}</td>
        <td>{{$payment->trip_id}}</td>
        <td>From Driver
        </td>
        <td>{{$util->format_date($payment->payment_date)}}</td>
        <td>{{$payment->amount}}</td>

    </tr>
@endforeach


