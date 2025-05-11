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
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3b5998;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
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
        <h1>{{ $title }}</h1>
    </div>
    
    <div class="content">
        <p>Merhaba,</p>
        
        <p>{{ $body }}</p>
        
        @if($type == 'event')
            <p><strong>Etkinlik Detayları:</strong></p>
            <ul>
                <li>Etkinlik: {{ $itemTitle }}</li>
                <li>Kalan Süre: {{ $timeUntil }} dakika</li>
            </ul>
        @else
            <p><strong>Görev Detayları:</strong></p>
            <ul>
                <li>Görev: {{ $itemTitle }}</li>
                <li>Kalan Süre: {{ $timeUntil }} saat</li>
            </ul>
        @endif
    </div>
    
    <div class="footer">
        <p>Bu e-posta AkilliAjanda tarafından otomatik olarak gönderilmiştir.</p>
    </div>
</body>
</html> 