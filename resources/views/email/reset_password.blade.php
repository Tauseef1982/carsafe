<!DOCTYPE html>
<html>
<head>
    <title>Reset Password Email</title>
</head>
<body>
    <h1>{{ $data['title'] }}</h1>
    <p>{{ $data['body'] }}</p>
    <p>
        <a href="{{ $data['url'] }}" style="padding: 10px 15px; background-color: #3490dc; color: white; text-decoration: none; border-radius: 5px;">
            Click Here to Change your Password
        </a>
    </p>
</body>
</html>
