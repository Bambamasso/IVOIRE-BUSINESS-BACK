<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9fbf7;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e1e8db;
        }

        .header {
            padding: 25px;
            text-align: center;
            background-color: #93b86a;
            color: #ffffff;
        }

        .content {
            padding: 30px;
            color: #3f4a34;
            line-height: 1.6;
        }

        .info-box {
            border-left: 4px solid #93b86a;
            background-color: #f1f6ec;
            padding: 15px;
            margin: 20px 0;
        }

        .info-box p {
            margin: 5px 0;
        }

        .message-box {
            background-color: #fdfbf3;
            border-left: 4px solid #e8d393;
            padding: 15px;
            margin: 20px 0;
        }

        .message-box p {
            margin: 0;
            white-space: pre-line;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #8a967f;
        }
    </style>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding: 20px;">
                <table class="container" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header">
                            <h2 style="margin: 0; font-size: 20px; letter-spacing: 1px;">NOUVEAU MESSAGE DE CONTACT</h2>
                        </td>
                    </tr>

                    <tr>
                        <td class="content">
                            <p>Bonjour,</p>
                            <p>Vous avez reçu un nouveau message depuis le formulaire de contact du site.</p>

                            <div class="info-box">
                                <p><strong>Nom complet :</strong> {{ $data['full_name'] ?? 'Non renseigné' }}</p>
                                <p><strong>Email :</strong>
                                    <a href="mailto:{{ $data['email'] ?? '' }}" style="color:#3f4a34;">
                                        {{ $data['email'] ?? 'Non renseigné' }}
                                    </a>
                                </p>
                                <p><strong>Téléphone :</strong>
                                    @if(!empty($data['phone_number']))
                                        <a href="tel:{{ $data['phone_number'] }}" style="color:#3f4a34;">
                                            {{ $data['phone_number'] }}
                                        </a>
                                    @else
                                        Non renseigné
                                    @endif
                                </p>
                                <p><strong>Objet :</strong> {{ $data['subject'] ?? 'Sans objet' }}</p>
                            </div>

                            <p><strong>Message :</strong></p>
                            <div class="message-box">
                                <p>{{ $data['message'] ?? 'Aucun message saisi.' }}</p>
                            </div>

                            <p>Vous pouvez répondre directement à ce client en utilisant son adresse email ci-dessus.</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} Intellect Ivoire-Business.<br>
                            Reçu le {{ date('d/m/Y à H:i') }} — message envoyé automatiquement depuis le formulaire de contact.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
