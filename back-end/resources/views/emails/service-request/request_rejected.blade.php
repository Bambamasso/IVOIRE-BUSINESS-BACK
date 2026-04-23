<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
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
            background-color: #475569;
        }

        /* Gris ardoise pro */
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }

        .reason-box {
            background-color: #fff1f2;
            border-left: 4px solid #e11d48;
            padding: 15px;
            margin: 20px 0;
            color: #9f1239;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777777;
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
                            <img src="" alt="Logo" width="120">
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            <h2>Bonjour Mr, Mme {{ $nomClient }},</h2>
                            <p>Nous avons bien étudié votre demande concernant le service :
                                <strong>{{ $serviceDemande }}</strong>.</p>

                            <p>Malheureusement, nous ne pouvons pas donner suite à cette demande spécifique pour la
                                raison suivante :</p>

                            <div class="reason-box">
                                <strong>Motif :</strong> {{ $raisonRejet }}
                            </div>

                            <p>Cela ne remet pas en cause votre compte. Vous pouvez tout à fait soumettre une nouvelle
                                demande en tenant compte de ces précisions ou nous contacter directement pour en
                                discuter.</p>

                            <p style="margin-top: 25px;">Cordialement,<br>L'équipe de gestion.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} Votre Entreprise.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>