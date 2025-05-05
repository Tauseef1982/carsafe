<!DOCTYPE html>
<html>
<head>
    <title>CarSafe</title>
</head>
<body>
<h3>New Complaint Submitted</h3>

<p><strong>Account ID:</strong> {{ $complaint->account_id }}</p>
<p><strong>Trip ID:</strong> {{ $complaint->trip_id }}</p>
<p><strong>Complaint:</strong> {{ $complaint->complaint }}</p>
<p><strong>Date:</strong> {{ $trip->date }}</p>

</body>
</html>