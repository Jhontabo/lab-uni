<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 25px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .highlight {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            border-left-color: #28a745;
        }
        .danger {
            border-left-color: #dc3545;
        }
        .warning {
            border-left-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <div>🔬 Sistema de Gestión de Laboratorios</div>
        </div>
        
        <div class="greeting">
            {{ $greeting ?? '¡Hola!' }}
        </div>
        
        <div class="content">
            {{ $introLines[0] ?? '' }}
            
            @if(isset($introLines[1]))
                <br><br>{{ $introLines[1] }}
            @endif
            
            @if(isset($outroLines))
                @foreach($outroLines as $line)
                    <br><br>{{ $line }}
                @endforeach
            @endif
            
            @if(isset($actionText) && isset($actionUrl))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
                </div>
            @endif
        </div>
        
        <div class="footer">
            <p>{{ $salutation ?? 'Saludos, ' . config('app.name') }}</p>
            <p style="font-size: 12px; color: #999; margin-top: 10px;">
                Este es un correo automático. Por favor no responder a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>