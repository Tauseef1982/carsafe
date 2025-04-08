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
                        <h5 class="pull-left">Dispatchers</h5>
                        <!-- <button class="pull-right btn btn-primary" data-bs-toggle="modal" data-original-title="test"
                                data-bs-target="#exampleModal">Add Adjustment
                        </button> -->
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display" id="advance-1">
                                <thead>
                                <tr>
                                    <th> Id</th>
                                    <th>Dispatcher Id</th>
                                    <th>Username</th>
                                   
                                  

                                </tr>
                                </thead>
                                <tbody>

                                @foreach($dispatchers as $adj)
                                    <tr>
                                        <td>{{$adj->id}}</td>
                                        <td>{{$adj->name}}</td>
                                        <td>{{$adj->username}}</td>
                                        

                                    </tr>
                                @endforeach

                                <tfoot>
                                <tr>
                                <th> Id</th>
                                    <th>Dispatcher Id</th>
                                    <th>Username</th>

                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

   

@endsection
