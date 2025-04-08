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
                <div class="card total-users">
                    <div class="card-header card-no-border">
                        <h5>Inactive Drivers</h5>

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
                                        <th>Current Balance</th>
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
                                        <th>Current Balance</th>
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
        $(function () {
            $('#drivers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.drivers.inactive') }}", // Update with the correct route
                columns: [
                    {data: 'driver_id', name: 'driver_id'},
                    {data: 'first_name', name: 'first_name', render: function(data, type, row) {
                            return row.first_name + ' ' + row.last_name;
                        }},
                    {data: 'status', name: 'status'},
                    {data: 'phone', name: 'phone'},
                    {data: 'username', name: 'username'},
                    {data: 'balance', name: 'balance'},
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
