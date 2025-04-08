
@foreach ($trips as $trip)

    <li>
        <a href="{{url('single-trip')}}/{{$trip->id}}">
            @php
                $formattedDate = \Carbon\Carbon::parse($trip->date)->format('m/d/Y');
            @endphp
            <span style="display: block;" class="notranslate">Date:{{$formattedDate}} </span>
            <span style="display: block;">Price:{{$trip->trip_cost}} </span>
            <span style="display: block;">Payment Method:{{$trip->payment_method}} </span>
            <span style="display: block;">Gocab Balance:{{$trip->gocab_paid}} </span>
            <span style="display: block;">From:{{$trip->location_from}} </span>

        </a>
    </li>

@endforeach
