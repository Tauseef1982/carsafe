@extends('admin.layout.yajra')
@php

    $util = new \App\Utils\dateUtil();

@endphp
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
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">

                <div class="card">
                    <div class="card-header">
                        <h5 class="pull-left">Drivers Complaints</h5>
                        <button class="btn btn-primary float-end" data-bs-toggle="modal"
                            data-bs-target="#addComplaintModal">Add Complaint</button>
                    </div>
                    <div class="card-body">
                        <div class="tabbed-card">
                            <ul class="pull-right nav nav-pills nav-primary" id="pills-clrtabinfo" role="tablist">
                                <!-- <li class="nav-item"><a class="nav-link active" id="pills-clrhome-tabinfo" data-bs-toggle="pill" href="#pills-clrhomeinfo" role="tab" aria-controls="pills-clrhome" aria-selected="true">Postpaid Accounts</a></li> -->
                                <!-- <li class="nav-item"><a class="nav-link" id="pills-clrprofile-tabinfo" data-bs-toggle="pill" href="#pills-clrprofileinfo" role="tab" aria-controls="pills-clrprofile" aria-selected="false">Prepaid Accounts</a></li> -->

                            </ul>
                            <div class="tab-content" id="pills-clrtabContentinfo">
                                <div class="tab-pane fade show active" id="pills-clrhomeinfo" role="tabpanel"
                                    aria-labelledby="pills-clrhome-tabinfo">
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="advance-1">
                                            <thead>
                                                <tr>
                                                    <th>Driver Id</th>
                                                    <th>Account ID</th>
                                                    <th>Username</th>
                                                    <th>Trip ID</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Admin Note</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach ($complaints as $complaint)
                                                    <tr>
                                                        <td>{{ $complaint->driver_id }}</td>
                                                        <td>{{ $complaint->account_id }}</td>
                                                        <td>{{ $complaint->admin_username }}</td>
                                                        <td>{{ $complaint->trip_id }}</td>
                                                        <td>{{ $complaint->description }}</td>
                                                        <td>{{ $complaint->status }}</td>
                                                        <td>{{ $complaint->admin_note }}</td>
                                                        @php
                                                            $date = \Carbon\Carbon::parse($complaint->created_at)
                                                                ->timezone('America/New_York')
                                                                ->format('Y-m-d');
                                                        @endphp
                                                        <td>{{ $date  }}</td>
                                                        <td>
                                                            <button class="btn btn-primary editBtn" data-bs-toggle="modal"
                                                                data-bs-target="#editModal" data-id="{{ $complaint->id }}"
                                                                data-status="{{ $complaint->status }}"
                                                                data-description="{{$complaint->description}}"
                                                                data-note="{{ $complaint->admin_note }}">
                                                                Edit
                                                            </button>
                                                           @if(Auth::guard('admin')->user()->role == 'admin')
                                                            <a href="{{ url('/admin/delete/complaint/' . $complaint->id) }}"
                                                                class="btn btn-danger"
                                                                onclick="return confirm('Are you sure you want to delete this complaint?')">
                                                                Delete
                                                            </a>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                @endforeach





                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>Driver Id</th>
                                                    <th>Account ID</th>
                                                    <th>Username</th>
                                                    <th>Trip ID</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Admin Note</th>
                                                    <th>Date</th>
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
        </div>
    </div>

                {{-- add complaint modal --}}
                <div class="modal fade" id="addComplaintModal" tabindex="-1" aria-labelledby="addComplaintModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="addComplaintModalLabel">Add Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="{{ url('admin/add_complaint') }}" method="POST">
                    @csrf
                  <div class="modal-body">
                    <label for="">Driver ID</label>
                    <input type="text" class="form-control mb-3" name="driver_id" placeholder="Please Enter Driver ID" required>
                    <label for="">Account ID</label>
                    <input type="text" class="form-control mb-3" name="account_id" placeholder="Please Enter Account ID" required>
                    <label for="">Admin Username</label>
                    <input type="text" class="form-control mb-3" name="admin_username" placeholder="Please Enter your username" required>
                    <label for="">Trip Id</label>
                    <input type="text" class="form-control mb-3" name="trip_id" placeholder="Please Enter trip_id">

                    <label for="">Description</label>
                    <textarea name="description" id="" class="form-control" placeholder="Please Enter Details"></textarea>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                  </form>
                </div>
              </div>
            </div>
            <!--edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Update Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
              <form action="{{ url('admin/update/driver_complaint') }}" method="post">
                @csrf
                <input type="hidden" name="id" id="edit-id" >

                @if(Auth::guard('admin')->user()->role == 'admin')
                 <label for="">Status</label>
                <select name="status" class="form-select" id="edit-status">
                  <option value="Pending">Pending</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Resolved">Resolved</option>

                </select>
                @elseif(Auth::guard('admin')->user()->role == 'dispatcher')
                <input type="hidden" name="status" id="edit-status-hidden">
                @endif
                <label for="">Description</label>
                <textarea name="description" class="form-control" id="complaint_description"></textarea>
                <label for="">Note</label>
                <textarea name="admin_note" id="edit-note" class="form-control" ></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update Stauts</button>
              </div>
            </form>
            </div>
          </div>
        </div>


@endsection

@section('js')
<script>
   $(document).ready(function() {
    $(document).on('click', '.editBtn', function () {
    let id = $(this).data('id');
    let status = $(this).data('status');
    let note = $(this).data('note');
    let description = $(this).data('description');

    $('#edit-id').val(id);
    $('#edit-status').val(status);

    if ($('#edit-status-hidden').length) {
        $('#edit-status-hidden').val(status);
    }

    $('#edit-note').val(note);
    $('#complaint_description').val(description);
});

});


</script>

@endsection
