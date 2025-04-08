@extends('admin.admin-layout')

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
        <div class="row">
            <div class=" xl-100 col-lg-12 box-col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="pull-left">Change Status</h5>
                        <div class="card-body">
                            <form method="post" action="{{url('admin/edit_complaint')}}/{{$complaint->id}}">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-12">
                                        <label for="">Username</label>
                                        <input type="text" class="form-control" name="username"
                                               value="{{$complaint->username}}">
                                    </div>
                                   
                                   
                                    <div class="col-12">
                                        <label for="">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="pending" {{$complaint->status == "pending" ? 'selected' : ''}} >
                                                Pending
                                            </option>
                                            <option value="solved" {{$complaint->status == "solved" ? 'selected' : ''}}>
                                                Solve
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="">Note</label>
                                        <textarea class="form-control" name="note">{{$complaint->note}}</textarea>
                                       <input type="checkbox" class="mt-3" value="yes" id="send_email" name="send_email">
                                       <label for="send_email">Please check this box if you want to send emil to customer</label>
                                    </div>

                                </div>


                                <button class="btn btn-primary mt-3" type="submit">Update</button>

                            </form>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
