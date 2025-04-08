
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Resolved</title>
</head>
<body>
    <p>Hi,</p>
    <br>
    <p>Message from CarSafe</p>
    <p>Your issue with Trip Id {{ $complaintData['trip_id'] }} has been resolved.</p>
    <p>{{$complaintData['note']}}</p>
    <p>
        <a href="{{ route('add_complaint', $complaintData['hash_id']) }}">
            Click here to re-submit an issue
        </a>
    </p>
</body>
</html>
