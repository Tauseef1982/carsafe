@extends('customer.layouts.yajra')
@section('css')
<style>
  .icon{
    float: right;
    margin-top: -28px;
    margin-right: 20px;
  }
</style>

@endsection
@section('content')
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
        <div class="col-6">
        <h3 class="">Update Card Details</h3>
        </div>
        <div class="col-6">
        <ol class="breadcrumb">
        <li class="me-2">

        </li>
         <li class="breadcrumb-item"><a href="{{ url('customer-portal') }}">                                       <i data-feather="home"></i></a></li>
        <li class="breadcrumb-item text-primary"><a href="{{ url('customer/credit_cards') }}">Credit Cards</a></li>
        <li class="breadcrumb-item text-primary">Edit Card</li>


        </ol>

        </div>
        </div>
      </div>
      </div>
    <div class="">

       <div class="container-fluid">
      <div class="row">
       <!-- Zero Configuration  Starts-->
       <div class="col-sm-8 mx-auto">
       <div class="card">
        <div class="card-header bg-dark">
            <h5 class="text-primary">Please Edit Your Card Details</h5>
        </div>
           <div class="card-body">
               <form method="post" action="{{url('customer/update_creditcard')}}/{{$creditcard->id}}">
                   @csrf
                   <input hidden class="form-control mb-3"
                          value="{{$creditcard->account_id}}" name="account_id"/>
                   <input hidden class="form-control mb-3"
                          value="{{$creditcard->account_name}}" name="account_name"/>
                   <input hidden class="form-control mb-3"
                          value="{{$creditcard->account_number}}" name="account_number"/>
                   <div class="row">
                       <div class="col-6">
                           <label for="">Card Number</label>
                           <input type="text" class="form-control" name="card_number"
                                  value="{{$creditcard->card_number}}">
                       </div>
                       <div class="col-6">
                           <label for="">CVC</label>
                           <input type="number" class="form-control" name="cvc"
                                  value="{{$creditcard->cvc}}">
                       </div>
                       <div class="col-6">
                           <label for="">Expiry</label>
                           <input type="text" class="form-control" name="expiry"
                                  value="{{ \Carbon\Carbon::parse($creditcard->expiry)->format('m/y') }}">
                       </div>
                       <div class="col-6">
                           <label for="">Type</label>
                           <select class="form-control" name="charge_priority">
                               <option value="1" {{$creditcard->charge_priority == 1 ? 'selected' : ''}} >
                                   Primary
                               </option>
                               <option value="0" {{$creditcard->charge_priority == 0 ? 'selected' : ''}}>
                                   Secondary
                               </option>
                           </select>
                       </div>
                   </div>


                   <button class="btn btn-primary mt-3" type="submit">Update</button>

               </form>
           </div>
       </div>
        </div>
        <!-- Zero Configuration  Ends-->
      </div>
       </div>
      </div>


      </div>


@endsection

@section('js')
<script>
  $(document).ready(function () {
    $(".card-js").addClass("form-control mt-3");
});

</script>

@endsection
