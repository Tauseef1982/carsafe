
<!DOCTYPE html>
<html>
<head>
    <title>CarSafe</title>
</head>
<body>
    <p>Hi,</p>
    <br>
    <p>Your CarSafe UserName and Password is given Below.Please Don't Share this email to anyone.</p>
    <p>If You receive this email by mistaken please delete this email</p>
    <p>UserName :{{$data['username']}}</p>
    <p>Password :{{$data['password']}}</p>
    <p>
        <a href="{{ url('customer/login')}}">
            Click here to Login
        </a>
    </p>

</body>
</html>
