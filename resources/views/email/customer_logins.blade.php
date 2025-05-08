
<!DOCTYPE html>
<html>
<head>
    <title>CarSafe</title>
</head>
<body>
    <p>Hi,</p>
  
  <p>We are excited to unveil the brand new <a href="{{ url('customer/login')}}">CarSafe Account Portal</a> where you'll be able to see your trip history, manage payments and download invoices.</p>
   <p>Here are your login details:</p>
    <p>UserName: {{$data['username']}}</p>
    <p>Password: {{$data['password']}}</p>
    <p>
        <a href="{{ url('customer/login')}}">
        Click here to Login
        </a>
    </p>

</body>
</html>
