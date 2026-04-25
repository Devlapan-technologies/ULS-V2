<!DOCTYPE html>
<html>
<head>
    <title>New Login - ULS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            line-height: 1.5; 
            margin: 0;
            padding: 20px;
            background-color: #f4f4f5;
        }
        .container { 
            max-width: 500px; 
            margin: 0 auto; 
            background: white;
            border: 1px solid #e5e7eb;
            padding: 24px;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 16px;
        }
        .header i {
            color: #7c3aed;
            font-size: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }
        .greeting {
            margin-bottom: 16px;
            font-size: 14px;
            color: #374151;
        }
        .greeting strong {
            color: #7c3aed;
        }
        .info-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 14px;
            color: #4b5563;
        }
        .info-line i {
            width: 16px;
            color: #9ca3af;
            font-size: 14px;
        }
        .warning {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .warning i {
            color: #dc2626;
            margin-right: 6px;
        }
        .warning strong {
            color: #dc2626;
        }
        .footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-shield-alt"></i>
            <h2>Unified Login Service</h2>
        </div>
        
        <div class="greeting">
            Hello <strong>{{ $name }}</strong>,
        </div>
        
        <p style="margin: 0 0 16px 0; font-size: 14px; color: #374151;">
            A new login was detected to your account.
        </p>
        
        <div class="info-line">
            <i class="far fa-clock"></i>
            <span>{{ $time }}</span>
        </div>
        
        <div class="info-line">
            <i class="fas fa-globe"></i>
            <span>{{ $ip }}</span>
        </div>
        
        <div class="warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>If this wasn't you, please contact your administrator.</span>
        </div>
        
        <div class="footer">
            <i class="fas fa-shield-alt" style="margin-right: 4px;"></i>
            ULS - Devlapan
        </div>
    </div>
</body>
</html>