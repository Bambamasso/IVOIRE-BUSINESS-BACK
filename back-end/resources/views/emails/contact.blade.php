<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
    <style>
        /* Styles généraux */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
        }

        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9edf2;
        }

        /* En-tête */
        .header {
            background: linear-gradient(135deg, #1a2a6c, #2d4373);
            padding: 30px 25px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .header .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 8px;
            letter-spacing: 0.5px;
        }

        /* Corps */
        .content {
            padding: 30px 25px;
        }

        .greeting {
            font-size: 16px;
            color: #333333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f3f8;
        }

        .greeting strong {
            color: #1a2a6c;
        }

        /* Grille d'informations */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 12px 15px 12px 0;
            font-weight: 600;
            color: #1a2a6c;
            font-size: 14px;
            width: 30%;
            border-bottom: 1px solid #f0f3f8;
        }

        .info-value {
            display: table-cell;
            padding: 12px 0;
            color: #333333;
            font-size: 14px;
            border-bottom: 1px solid #f0f3f8;
            word-break: break-word;
        }

        /* Badge pour le sujet */
        .subject-badge {
            display: inline-block;
            background: #e8f0fe;
            color: #1a2a6c;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        /* Zone du message */
        .message-box {
            background: #f8faff;
            border-left: 4px solid #1a2a6c;
            padding: 18px 20px;
            border-radius: 6px;
            margin: 15px 0 10px 0;
        }

        .message-box p {
            margin: 0;
            color: #333333;
            font-size: 15px;
            line-height: 1.6;
        }

        .message-label {
            font-weight: 600;
            color: #1a2a6c;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }

        /* Pied de page */
        .footer {
            background: #f8faff;
            padding: 20px 25px;
            border-top: 1px solid #e9edf2;
            text-align: center;
        }

        .footer p {
            margin: 5px 0;
            color: #7a8a9e;
            font-size: 13px;
        }

        .footer .highlight {
            color: #1a2a6c;
            font-weight: 500;
        }

        .footer .divider {
            display: inline-block;
            width: 30px;
            height: 2px;
            background: #dce3ed;
            margin: 8px auto;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .email-container {
                margin: 10px;
                border-radius: 8px;
            }

            .header {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 18px;
            }

            .content {
                padding: 20px 15px;
            }

            .info-label {
                display: block;
                width: 100%;
                padding-bottom: 2px;
                border-bottom: none;
            }

            .info-value {
                display: block;
                width: 100%;
                padding-top: 0;
                padding-bottom: 12px;
                border-bottom: 1px solid #f0f3f8;
            }

            .info-row {
                display: block;
            }

            .message-box {
                padding: 14px 16px;
            }
        }
    </style>
</head>

<body>

    <div class="email-container">

        <!-- ===== EN-TÊTE ===== -->
        <div class="header">
            <h1>📩 Nouveau message de contact</h1>
            <span class="badge">E-commerce &amp; Services</span>
        </div>

        <!-- ===== CORPS ===== -->
        <div class="content">

            <div class="greeting">
                👋 Vous avez reçu un nouveau message depuis le formulaire de contact de votre site.
            </div>

            <!-- Grille d'informations -->
            <div class="info-grid">

                <div class="info-row">
                    <div class="info-label">👤 Nom complet</div>
                    <div class="info-value"><strong>{{ $data['full_name'] ?? 'Non renseigné' }}</strong></div>
                </div>

                <div class="info-row">
                    <div class="info-label">📧 Email</div>
                    <div class="info-value">
                        <a href="mailto:{{ $data['email'] ?? '' }}" style="color:#1a2a6c; text-decoration:underline;">
                            {{ $data['email'] ?? 'Non renseigné' }}
                        </a>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">📞 Téléphone</div>
                    <div class="info-value">
                        @if(!empty($data['phone_number']))
                            <a href="tel:{{ $data['phone_number'] }}" style="color:#1a2a6c; text-decoration:underline;">
                                {{ $data['phone_number'] }}
                            </a>
                        @else
                            <span style="color:#999;">Non renseigné</span>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">📌 Sujet</div>
                    <div class="info-value">
                        <span class="subject-badge">{{ $data['subject'] ?? 'Sans objet' }}</span>
                    </div>
                </div>

            </div>

            <!-- Message -->
            <div class="message-box">
                <span class="message-label">💬 Message :</span>
                <p>{{ $data['message'] ?? 'Aucun message saisi.' }}</p>
            </div>

        </div>

        <!-- ===== PIED DE PAGE ===== -->
        <div class="footer">
            <div class="divider"></div>
            <p>
                📅 Reçu le <span class="highlight">{{ date('d/m/Y à H:i') }}</span>
            </p>
            <p style="font-size:12px; color:#aab8c9;">
                Cet email a été généré automatiquement depuis le formulaire de contact de votre site.
            </p>
        </div>

    </div>

</body>
</html>