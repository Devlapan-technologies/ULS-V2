<!DOCTYPE html>
<html>
<head>
    <title>New Login - ULS</title>
</head>
<body style="margin: 0; padding: 0; background-color: transparent;">
    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; max-width: 500px; margin: 0 auto; background: white; border: 1px solid #e5e7eb; padding: 24px;">
        
        <div style="display: flex; align-items: center; gap: 8px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; margin-bottom: 16px;">
            <i class="fas fa-shield-alt" style="color: #7c3aed; font-size: 20px;"></i>
            <h2 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Unified Login Service</h2>
        </div>
        
        <div style="margin-bottom: 16px; font-size: 14px; color: #374151;">
            Hello <strong style="color: #7c3aed;">{{ $name }}</strong>,
        </div>
        
        <p style="margin: 0 0 16px 0; font-size: 14px; color: #374151;">
            A new login was detected to your account.
        </p>
        
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 14px; color: #4b5563;">
            <i class="far fa-clock" style="width: 16px; color: #9ca3af; font-size: 14px;"></i>
            <span>{{ $time }}</span>
        </div>
        
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 14px; color: #4b5563;">
            <i class="fas fa-globe" style="width: 16px; color: #9ca3af; font-size: 14px;"></i>
            <span>{{ $ip }}</span>
        </div>
        
        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 14px;">
            <i class="fas fa-exclamation-triangle" style="color: #dc2626; margin-right: 6px;"></i>
            <span>If this wasn't you, please contact your administrator.</span>
        </div>
        
        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
            <i class="fas fa-shield-alt" style="margin-right: 4px;"></i>
            ULS - Devlapan
        </div>
        
    </div>
</body>
</html>