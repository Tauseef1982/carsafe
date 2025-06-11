<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{asset('assets/images/logo/carsafe-logo.webp')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('assets/images/logo/carsafe-logo.webp')}}" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/bootstrap.css')}}">
    <title>CarSafe</title>
    <style>
        .btn-primary {
    background-color: #f05829 !important;
    border-color: #f05829 !important;
}
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col text-center pt-4">
                <img src="{{asset('assets/images/logo/carsafe-logo.webp')}}" width="200px" alt="">
            </div>
        </div>
        <div class="row">
            <div class="col pt-4">
               <h2 class="text-center">Please Submit Your Complaint </h2>
               <p class="text-center">
               <small class="mx-auto">(You can submit one trip complaint at a time)</small>
               </p>
               
               @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 mx-auto">
             <form action="{{url('add_account_complaint')}}" method="post">
                @csrf
                @if($account_payment)
                    <input type="text" hidden name="hash_id" value="{{ $account_payment->hash_id }}">
                @endif
             <input type="text" hidden class="form-control mt-2 mb-3" value="{{$account_payment->account_id}}" name="account_id" placeholder="Please Enter Your Account Id Here....">
                <label for="">Trip Id</label>
                <input type="text" class="form-control mt-2 mb-3" required name="trip_id" placeholder="Please Enter Trip Id Here....">
                <label for="">Complaint</label>
                <textarea class="form-control mt-2 mb-3" name="complaint" required placeholder="Please Describe Your Issue In Detail here..." name="complaint" id=""></textarea>
                <div class="text-center">
                <input type="submit" value="submit" class="btn btn-primary">
                </div>
             </form>
            </div>
        </div>
    </div>
    
</body>
</html>