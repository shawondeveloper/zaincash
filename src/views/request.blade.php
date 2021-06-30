<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZainCash IQ</title>
</head>
<body>
    <div>
        <h1>Hello i m working</h1>
        <form action="{{route('pay')}}" method="post">
        	@csrf
        	Amount : <input type="text" name="amount">
            <input type="submit" name="submitpay" value="submitpay">
        </form>
    </div>
</body>
</html>