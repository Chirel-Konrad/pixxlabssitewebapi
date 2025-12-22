<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de réinitialisation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #666666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #856404;
        }
        .expiry {
            font-size: 14px;
            color: #dc3545;
            text-align: center;
            margin-top: 15px;
            font-weight: 500;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #999999;
            border-top: 1px solid #e9ecef;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔐 Réinitialisation de mot de passe</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Bonjour {{ $userName }},
            </div>
            
            <div class="message">
                Vous avez demandé à réinitialiser votre mot de passe. Utilisez le code de vérification ci-dessous pour continuer :
            </div>
            
            <div class="otp-box">
                <div class="otp-label">Votre code de vérification</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry">⏱️ Ce code expire dans 10 minutes</div>
            </div>
            
            <div class="warning">
                <strong>⚠️ Attention :</strong> Ne partagez jamais ce code avec qui que ce soit. Notre équipe ne vous demandera jamais ce code par téléphone ou par email.
            </div>
            
            <div class="message">
                Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email. Votre mot de passe restera inchangé.
            </div>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé par <strong>Polariix</strong></p>
            <p>Besoin d'aide ? Contactez-nous à <a href="mailto:support@polariix.com">support@polariix.com</a></p>
        </div>
    </div>
</body>
</html>
