
<!DOCTYPE html>
<html>
<head>
    <title>CarSafe</title>
</head>
<body>
    <p>Hi,</p>
    <br>
    <p>We are excited to unveil to brand new CarSafe Account Portal where you'll be able to see you trip history, manage payment and download invoices.</p>
   <p>Here are your login details:</p>
    <p>UserName :{{$data['username']}}</p>
    <p>Password :{{$data['password']}}</p>
    <p>
        <a href="{{ url('customer/login')}}">
        Click here to Login
        </a>
    </p>

</body>
</html>
