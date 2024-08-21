<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{$data['title']}}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            text-align: center;
        }
        
        p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        a {
            display: inline-block;
            background-color: #4681f4;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        a:hover {
            background-color: #5783db;
        }
        
        a:active {
            background-color: #004085;
        }
        
        a:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
        }
    </style>

</head>
<body>

    <div class="container">
        <p>{{$data['body']}}</p>
        <a href="{{$data['url']}}">Click here to reset your password</a>
        <p>Thanks.</p>
    </div>
    
</body>
</html>