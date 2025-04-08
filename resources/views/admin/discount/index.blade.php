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
                        <h5 class="pull-left">Discounts</h5>


                        <button class="pull-right btn btn-primary" data-bs-toggle="modal" data-original-title="test"
                                data-bs-target="#invoiceModal">Add Discount
                        </button>

                      

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm" id="discounts">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Discount</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Action</th>

                                </tr>
                                </thead>
                                <tbody>




                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="invoiceModal" tabindex="-1"
         role="dialog" aria-labelledby="invoiceModalLabel"
         aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New Discount</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{url('admin/add_discount')}}">

                    <div class="modal-body">
                        @csrf
                        
                        

                        <label for="">Discount %</label>
                        <input type="text" class="form-control mb-3" name="percentage" required placeholder="Please Enter Discount %" />
                        <label for="">Start Date</label>
                        <input type="date" class="form-control mb-3" required name="start_date"/>
                        <label for="">End Date</label>
                        <input type="date" class="form-control mb-3" required name="end_date" value="">
                        <div class="mb-2">
						<label for="">Select Accounts</label>
<select class="form-control select2 mb-3" name="accounts[]" multiple="multiple" required>
    @foreach($accounts as $client)
        <option value="{{ $client->id }}">{{ $client->account_id }}</option>
    @endforeach
</select>
	


                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark" type="button"
                                data-bs-dismiss="modal">Close
                        </button>
                        <button class="btn btn-primary" type="submit">Save
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    {{-- invoice modal --}}

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
     <div class="modal-body">
     <h1 class="modal-title fs-5 text-center">Do you want to delete this dicount?</h1>
    
     
      </div>
      <form method="post" action="{{url('admin/discount/delete')}}">
        @csrf
        <input type="hidden" value="" name="id" id="discount_id">
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Delete</button>
      </div>
      </form>
    </div>
  </div>
</div>



    <div class="modal fade" id="addCardModal" tabindex="-1"
         role="dialog" aria-labelledby="addCardLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document" id="addCardModal_append_modal_body">

        </div>
    </div>

@endsection

@section('js')
<script>
    $(document).ready(function (){

        $('.select2').select2({
        placeholder: "Select clients",
        allowClear: true
    });
    
        $(document).on('click', '.btn_trash', function() {
    let id = $(this).data('id');
    console.log(id);  // This should log the ID to the console
    $('#discount_id').val(id);
});

        var accounts = $('#discounts').DataTable({
            processing: true,
            serverSide: true,
            dom: "Blfrtip",
           
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 1000]],

            ajax: {
                url: "{{ url('admin/discounts') }}",
                method: "Get",
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'discount', name: 'discount',},
                {data: 'start_date', name: 'start_date'},
                {data: 'end_date', name: 'end_date'},
                {data: 'status', name: 'status'},
                {data: 'actions', name: 'actions'},


            ],
            buttons: [
                'excel', 'colvis', 'pdf', 'print'
            ],
            language: {},

        });

       

    });
    
</script>


  

@endsection
