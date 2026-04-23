<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        /* Styles de base pour simuler un peu de design sans flex */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .header {
            padding: 20px;
            text-align: center;
            background-color: #1e3a8a;
        }

        /* Bleu foncé */
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777777;
        }

        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td align="center" style="background-color: #f4f4f4; padding: 20px 0;">
                <table class="container" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header">
                            <img src="" alt="Logo Entreprise" width="150"
                                style="display: block; margin: 0 auto;">
                        </td>
                    </tr>

                    <tr>
                        <td class="content">
                            <h1 style="color: #93b86a;">Bonjour Mr, Mme {{ $nomClient }},</h1>
                            <p>Nous avons bien reçu votre demande de prestation concernant le service :
                                <strong>{{ $serviceDemande }}</strong>.</p>
                            <p>Notre équipe administrative examine actuellement vos détails. Nous reviendrons vers vous
                                très prochainement par email ou par téléphone pour discuter des spécificités et
                                finaliser le prix avec vous.</p>

                            <table cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="margin-top: 20px; border-top: 1px solid #eeeeee; padding-top: 20px;">
                                <tr>
                                    <td>
                                        <p><strong>Récapitulatif :</strong></p>
                                        <ul>
                                            <li>Service : {{ $serviceDemande }}</li>
                                            <li>Numéro de la demande : {{ $numeroDemande }}</li>
                                            <li>Prix proposé : {{ $prixPropose ?? 'À négocier' }}</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-top: 30px;">Merci de votre confiance !</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} Votre Entreprise. Tous droits réservés.<br>
                            Abidjan, Côte d'Ivoire.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>