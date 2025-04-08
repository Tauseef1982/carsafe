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
                        <h5 class="pull-left">Edit Discount</h5>



                      

                    </div>
                    <div class="card-body">
                    <form method="post" action="{{url('admin/discount/update')}}/{{$discount->id}}">

<div class="modal-body">
    @csrf
    
    

    <label for="">Discount %</label>
    <input type="text" class="form-control mb-3" name="percentage" value="{{$discount->percentage}}" />
    <label for="">Start Date</label>
    <input type="date" class="form-control mb-3" required name="start_date" value="{{$discount->start_date}}"/>
    <label for="">End Date</label>
    <input type="date" class="form-control mb-3" required name="end_date" value="{{$discount->end_date}}">
    <label for="">Status</label>
      <select name="status" id="" class="form-control">
         <option value="1" @if ($discount->status == 1) selected @endif>Active</option>
         <option value="0" @if ($discount->status == 0) selected @endif>Inactive</option>
      </select>
      <label for="accounts">Select Accounts</label>
        <select class="form-control select2" name="accounts[]" multiple="multiple">
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" {{ in_array($account->id, $discount->accounts->pluck('id')->toArray()) ? 'selected' : '' }}>
                    {{ $account->account_id }}
                </option>
            @endforeach
        </select>

</div>
<div class="modal-footer">
    
    <button class="btn btn-primary" type="submit">Update
    </button>
</div>
</form>

                    </div>
                </div>
            </div>
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
});
</script>



  

@endsection
