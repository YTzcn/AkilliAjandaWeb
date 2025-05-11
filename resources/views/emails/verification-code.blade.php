<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3b5998;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .verification-code {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 5px;
            margin: 20px 0;
            color: #3b5998;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>E-posta Doğrulama</h1>
    </div>
    
    <div class="content">
        <p>Merhaba {{ $name }},</p>
        
        <p>AkilliAjanda hesabınızı doğrulamak için aşağıdaki kodu kullanın:</p>
        
        <div class="verification-code">
            {{ $code }}
        </div>
        
        <p>Bu kod {{ $expiresAt->diffForHumans() }} süre sonra geçerliliğini yitirecektir.</p>
        
        <p><strong>Not:</strong> Bu kodu kimseyle paylaşmayın.</p>
    </div>
    
    <div class="footer">
        <p>Bu e-posta AkilliAjanda tarafından otomatik olarak gönderilmiştir.</p>
        <p>Eğer bu e-postayı siz talep etmediyseniz, lütfen dikkate almayın.</p>
    </div>
</body>
</html> 