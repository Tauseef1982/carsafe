


<table class="display table table-sm" id="from_customer">
    <thead>
    <tr>

        <th>Id</th>
        <th>Driver Id</th>
        <th>Trip Id</th>
        <th>Trip Date</th>
        <th>Payment Type</th>
        <th>Date</th>
        <th>Amount</th>
    </tr>
    </thead>
    @php $cust_p = 0; @endphp
    <tbody>
    @foreach($payments as $payment)

        <tr>
            <td>{{$payment->id}}</td>
            <td>{{$payment->driver_id}}</td>
            <td>{{$payment->trip_id}}</td>
            <td>{{$payment->trip->date}}</td>
            <td>From Driver</td>
            <td>{{$util->format_date($payment->payment_date)}}</td>
            <td>{{$payment->amount}}</td>
            @php $cust_p = $cust_p + $payment->amount; @endphp
        </tr>

    </tbody>
    @endforeach
    <tfoot>
    <tr>
        <th>Id</th>
        <th>Driver Id</th>
        <th>Trip Id</th>
        <th>Trip Date</th>
        <th>Payment Type</th>
        <th>Date</th>
        <th>Amount = {{$cust_p}}</th>

    </tr>
    </tfoot>
</table>



