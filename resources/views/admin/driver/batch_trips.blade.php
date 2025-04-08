

@foreach($batchs as $batch)
    <tr>
        <td class="show_batch" data-idd="{{$batch->id}}" style="cursor: pointer;"><i
                class="fa fa-plus"></i></td>
        <td>{{$batch->id}}</td>
        <td>{{$util->format_date($batch->created_at)}}</td>
        <td>{{$batch->amount}}</td>

    </tr>
    <tr class="nested-table-row" data-idd="{{$batch->id}}">
        <td colspan="3">
            <table class="table nested-table" style="padding-left:50px;">
                <thead>
                <tr>

                    <th>Driver Id</th>
                    <th>Trip Id</th>
                    <th>Date</th>
                    <th>Trip</th>

                </tr>
                </thead>
                @foreach($batch->trips() as $btrip)
                    <tr>

                        <td>{{$btrip->driver_id}}</td>
                        <td>{{$btrip->trip_id}}</td>
                        <td>{{$util->format_date($batch->created_at)}}</td>
                        <td>{{$btrip->paidAgianstTripByAdminQuery()->where('batch_id',$batch->id)->sum('amount')}}</td>

                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
@endforeach

<script>
    $(document).ready(function () {
        $('.show_batch').on('click', function () {
           
            const batchId = $(this).data('idd');
            const nestedRow = $(`.nested-table-row[data-idd="${batchId}"]`);
            const icon = $(this).find('i');

            // Toggle the nested table's visibility
            nestedRow.find('.nested-table').slideToggle();

            // Toggle the icon
            if (icon.hasClass('fa-plus')) {
                icon.removeClass('fa-plus').addClass('fa-minus');
            } else {
                icon.removeClass('fa-minus').addClass('fa-plus');
            }
        });
    });
</script>
