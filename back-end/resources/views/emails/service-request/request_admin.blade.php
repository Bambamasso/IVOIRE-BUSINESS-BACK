<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin: 0; padding: 0; background-color: #f0f0f0; font-family: Arial, sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px;">
                    <tr>
                        <td style="padding: 20px; background-color: #1a1a1a; border-radius: 12px 12px 0 0;">
                            <span style="color: #93b86a; font-weight: bold; font-size: 12px;">NOUVELLE ALERTE SERVICE</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333;">Demande n° {{ $request_number }}</h2>
                            
                            <table width="100%" border="0" cellspacing="0" cellpadding="5" style="font-size: 14px; color: #444444;">
                                <tr>
                                    <td width="150" style="font-weight: bold; border-bottom: 1px solid #f0f0f0; padding: 10px 0;">Client :</td>
                                    <td style="border-bottom: 1px solid #f0f0f0; padding: 10px 0;">{{ $full_name }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; border-bottom: 1px solid #f0f0f0; padding: 10px 0;">Email :</td>
                                    <td style="border-bottom: 1px solid #f0f0f0; padding: 10px 0;">{{ $email }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; border-bottom: 1px solid #f0f0f0; padding: 10px 0;">Service :</td>
                                    <td style="border-bottom: 1px solid #f0f0f0; padding: 10px 0;"><strong>{{ $service.name}}</strong></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; border-bottom: 1px solid #f0f0f0; padding: 10px 0;">Prix proposé :</td>
                                    <td style="border-bottom: 1px solid #f0f0f0; padding: 10px 0; color: #93b86a; font-weight: bold;">{{ number_format($propose_price, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            </table>

                            <div style="margin-top: 25px; padding: 15px; background-color: #fefefe; border: 1px dashed #cccccc; border-radius: 8px;">
                                <p style="margin: 0; font-size: 13px; font-weight: bold; color: #666;">Message du client :</p>
                                <p style="margin: 10px 0 0; font-size: 14px; color: #333; line-height: 1.5;">{{ $details }}</p>
                            </div>

                            <div style="text-align: center; margin-top: 30px;">
                                <a href="{{ url('/admin/requests') }}" style="display: inline-block; padding: 15px 30px; background-color: #93b86a; color: #ffffff; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 14px;">Gérer la demande</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>