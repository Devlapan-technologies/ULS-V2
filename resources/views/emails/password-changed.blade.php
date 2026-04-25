<!DOCTYPE html>
<html>
<head>
    <title>Password Changed Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #7c3aed; color: white; padding: 10px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .warning { background: #fef3c7; padding: 15px; margin: 15px 0; border-left: 4px solid #f59e0b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔒 Password Changed</h2>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>Your password was successfully changed:</p>
            
            <div class="warning">
                <p><strong>Time of change:</strong> {{ $time }}</p>
            </div>
            
            <p>If you made this change, no further action is needed.</p>
            <p><strong>If you didn't change your password:</strong> Please contact your administrator immediately.</p>
        </div>
    </div>
</body>
</html>