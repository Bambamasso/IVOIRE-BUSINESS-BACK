<!DOCTYPE html>
<html>

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

        /* Utilisation de ton vert #93b86a */
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

        .button-container {
            text-align: center;
            margin-top: 30px;
        }

        .button {
            background-color: #93b86a;
            color: #ffffff !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
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
                            <h2 style="margin: 0; font-size: 20px; letter-spacing: 1px;">NOUVELLE DEMANDE</h2>
                        </td>
                    </tr>

                    <tr>
                        <td class="content">
                            <p>Bonjour,</p>
                            <p>Une nouvelle demande de service vient d'être enregistrée. Voici les premiers détails à
                                consulter :</p>

                            <div class="info-box">
                                <p style="margin: 5px 0;"><strong>Client :</strong> {{ $nomClient }}</p>
                                <p style="margin: 5px 0;"><strong>Service :</strong> {{ $serviceDemande }}</p>
                                <p style="margin: 5px 0;"><strong>Montant proposé par le client :</strong>
                                    {{ $prixPropose }}</p>
                            </div>

                            <p>Vous pouvez accéder directement à la fiche complète pour valider ou répondre au client
                                via le bouton ci-dessous.</p>

                            <div class="button-container">
                                <a href="#" class="button">
                                    Accéder à l'interface Admin
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} Système de Gestion Interne.<br>
                            Ce message vous est adressé en tant qu'administrateur.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>