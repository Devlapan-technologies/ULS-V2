<!DOCTYPE html>
<html>
<head>
    <title>Password Changed Alert</title>
</head>
<body style="margin: 0; padding: 0; background-color: transparent;">
    <div style="font-family: Arial, sans-serif; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px; background-color: transparent;">
        <div style="background: #7c3aed; color: white; padding: 10px; text-align: center;">
            <h2 style="margin: 0; font-size: 18px;">🔒 Password Changed</h2>
        </div>
        <div style="padding: 20px; background: #f9f9f9;">
            <p style="margin: 0 0 10px 0;">Hello <strong>{{ $name }}</strong>,</p>
            <p style="margin: 0 0 10px 0;">Your password was successfully changed:</p>
            
            <div style="background: #fef3c7; padding: 15px; margin: 15px 0; border-left: 4px solid #f59e0b;">
                <p style="margin: 0;"><strong>Time of change:</strong> {{ $time }}</p>
            </div>
            
            <p style="margin: 0 0 10px 0;">If you made this change, no further action is needed.</p>
            <p style="margin: 0;"><strong>If you didn't change your password:</strong> Please contact your administrator immediately.</p>
        </div>
    </div>
</body>
</html>