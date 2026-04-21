<!DOCTYPE html>
<html>
<body style="margin: 0; padding: 0; background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f7f6; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 24px; overflow: hidden;">
                    <tr>
                        <td align="center" style="background-color: #93b86a; padding: 50px 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 900;">Merci de votre confiance !</h1>
                            <p style="color: #eef5e6; margin-top: 10px; font-size: 16px;">Votre projet commence ici.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 40px;">
                            <p style="font-size: 18px; color: #333333; font-weight: bold;">Bonjour {{ $full_name }},</p>
                            
                            <p style="font-size: 15px; color: #555555; line-height: 1.8;">
                                C'est un plaisir de recevoir votre demande. Nous avons bien réceptionné votre dossier concernant le service <strong>{{ $serviceRe }}</strong>. 
                                <br><br>
                                Notre équipe technique est déjà en train d'analyser vos besoins pour vous proposer la solution la plus adaptée. Un expert vous contactera très prochainement par mail ou par téléphone pour discuter des détails et finaliser l'offre avec vous.
                            </p>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f9fbf7; border-radius: 15px; margin: 30px 0; border: 1px solid #e9efdf;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <p style="margin: 0 0 15px 0; font-size: 13px; color: #93b86a; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">Récapitulatif de votre demande</p>
                                        
                                        <table width="100%" border="0" cellspacing="0" cellpadding="5">
                                            <tr>
                                                <td width="120" style="font-size: 14px; color: #888;">N° Dossier :</td>
                                                <td style="font-size: 14px; color: #333; font-weight: bold;">{{ $request_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; color: #888;">Prix proposé :</td>
                                                <td style="font-size: 14px; color: #333; font-weight: bold;">{{ $propose_price ? number_format($propose_price, 0, ',', ' ') . ' FCFA' : 'À négocier' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 15px; color: #555555; line-height: 1.8;">
                                Si vous avez des documents complémentaires à nous transmettre ou une urgence, n'hésitez pas à répondre directement à cet e-mail.
                            </p>

                            <p style="margin-top: 40px; font-size: 15px; color: #333;">
                                À très bientôt,<br>
                            </p>
                        </td>
                    </tr>
                </table>
               
            </td>
        </tr>
    </table>
</body>
</html>