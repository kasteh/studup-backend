<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de Vérification StudUP</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 20px; }
        .code { font-size: 24px; font-weight: bold; text-align: center; color: #007bff; margin: 20px 0; padding: 10px; border: 1px dashed #ccc; border-radius: 4px; }
        .footer { font-size: 12px; text-align: center; color: #888; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Votre code de vérification</h2>
        </div>
        <p>Bonjour,</p>
        <p>Merci de vous être inscrit sur StudUP. Pour finaliser votre inscription, veuillez utiliser le code de vérification ci-dessous :</p>
        <div class="code">
            {{ $verificationCode }}
        </div>
        <p>Ce code est valable pour les 15 prochaines minutes.</p>
        <p>Si vous n'avez pas demandé ce code, vous pouvez ignorer cet e-mail en toute sécurité.</p>
        <div class="footer">
            <p>Cordialement,<br>L'équipe StudUP</p>
        </div>
    </div>
</body>
</html>