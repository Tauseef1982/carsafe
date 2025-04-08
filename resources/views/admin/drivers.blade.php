@extends('admin.layout.yajra')

@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-6">

            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><i data-feather="home"></i></a></li>

                </ol>
            </div>
        </div>
    </div>
    </div>
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row size-column">
            <div class=" risk-col xl-100 box-col-12">
                <div class=" total-users">
                    <div class="">
                        <div class="row d-flex">
                            <div class="col card bg-primary me-3 text-dark p-3 text-center fill-flex">
                                <div class="">
                                <h5>Active Drivers</h5>
                                <h6> {{$active_drivers}}</h6>
                                </div>
                            </div>
                            <div class="col fill-flex card me-3 bg-secondary  p-3 text-center">
                            <div class="">
                                <h5>Registered This week</h5>
                                <h6> {{$this_week_drivers}}</h6>
                                </div>
                            </div>
                            <div class="col fill-flex card bg-primary text-dark p-3 text-center">
                            <div class="">
                                <h5>Drivers without trips this week</h5>
                                <h6>{{$driversWithoutTrips}}</h6>
                                </div>
                            </div>
                        </div>
                       
                       
                      

                    </div>
                    <div class="card-body pt-0 ">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="display" id="drivers-table">
                                    <thead>
                                    <tr>

                                        <th>Driver Id</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Phone</th>
                                        <th>Username</th>
                                        <th>Last trip Date</th>
                                        <th>Current Balance</th>
                                        <th>Created At</th>
                                        <th>Beta</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                    <tfoot>
                                    <tr>

                                        <th>Driver Id</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Phone</th>
                                        <th>Username</th>
                                        <th>Last trip Date</th>
                                        <th>Current Balance</th>
                                        <th>Created At</th>
                                        <th>Beta</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>


                        </div>


                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
@section('js')

    <script type="text/javascript">

        var eextra_link = '';
        @if(isset(request()->show_negative))
            eextra_link = '?show_negative=true'
        @endif
        $(function () {
            $('#drivers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.drivers.index') }}"+eextra_link, // Update with the correct route
                columns: [
                    {data: 'driver_id', name: 'driver_id'},
                    {data: 'first_name', name: 'first_name', render: function(data, type, row) {
                            return row.first_name + ' ' + row.last_name;
                        }},
                    {data: 'status', name: 'status'},
                    {data: 'phone', name: 'phone'},
                    {data: 'username', name: 'username'},
                    {data: 'last_trip_date', name: 'last_trip_date'},
                    {data: 'balance', name: 'balance'},
                    {data: 'created_at', name: 'created_at'},

                    {data: 'beta', name: 'beta'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        });
    </script>
    <script>
        $(document).on('change', '.switch input[type="checkbox"]', function () {
    let isChecked = $(this).is(':checked') ? 1 : 0;
    let recordId = $(this).data('id');

    $.ajax({
        url: "{{ url('admin/update-beta') }}", // Define this route in your Laravel application
        method: "POST",
        data: {
            id: recordId,
            beta: isChecked,
            _token: "{{ csrf_token() }}" // Include CSRF token for security
        },
        success: function (response) {
            if (response.success) {
                alert(response.msg);
            } else {
                alert(response.msg);
            }
        },
        error: function (xhr) {
            alert('An error occurred: ' + xhr.responseText);
        }
    });
});
    </script>
@endsection
