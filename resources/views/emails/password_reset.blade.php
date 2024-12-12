<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            font-size: 24px;
            color: #333333;
            margin-bottom: 20px;
        }
        .email-body {
            font-size: 16px;
            color: #555555;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .email-code {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin: 20px 0;
        }
        .email-footer {
            font-size: 14px;
            color: #777777;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            Password Reset Request
        </div>
        <div class="email-body">
            You have requested to reset your password. Please use the following code to proceed:
            <div class="email-code">{{ $token }}</div>
            If you did not request a password reset, please ignore this email.
        </div>
        <div class="email-footer">
            © {{ date('Y') }} Your Company. All rights reserved.
        </div>
    </div>
</body>
</html>
