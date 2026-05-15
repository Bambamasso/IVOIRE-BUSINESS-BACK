<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Mise à jour de votre commande - INTELLECT IVOIRE</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #1a1a1a;
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header p {
            margin-top: 10px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        /* Couleurs dynamiques selon le statut */
        .badge-confirmed {
            background: #93b86a1a;
            color: #93b86a;
        }

        .badge-rejected {
            background: #e74c3c1a;
            color: #e74c3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        td {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .product-name {
            font-weight: 900;
            color: #1a1a1a;
            display: block;
        }

        .variant-info {
            font-size: 11px;
            color: #93b86a;
            font-weight: bold;
            text-transform: uppercase;
        }

        .totals {
            margin-top: 20px;
            background: #fcfcfc;
            padding: 20px;
            border-radius: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .total-main {
            font-size: 18px;
            font-weight: 900;
            color: #1a1a1a;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #eee;
        }

        .info-box {
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .info-confirmed {
            border-left: 4px solid #93b86a;
            background: #f9faf9;
        }

        .info-rejected {
            border-left: 4px solid #e74c3c;
            background: #fdf8f8;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            @if(file_exists(public_path('storage/Logo.png')))
                <img src="{{ $message->embed(public_path('storage/Logo.png')) }}" alt="Logo"
                    style="max-width: 100px; margin-bottom: 15px; border-radius: 8px;">
            @endif
            
            @if($order->status->name === 'Validé(e)')
                <h2>Commande Confirmée !</h2>
                <p style="color: #93b86a;">RÉFÉRENCE : #{{ $order->order_number }}</p>
            @else
                <h2>Commande Annulée</h2>
                <p style="color: #e74c3c;">RÉFÉRENCE : #{{ $order->order_number }}</p>
            @endif
        </div>

        <div class="content">
            
            @if($order->status->name === 'Validé(e)')
                <div class="status-badge badge-confirmed">En cours de préparation</div>
                <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
                <p>Bonne nouvelle ! Votre commande vient d'être validée par notre équipe. Nous préparons actuellement vos articles avec le plus grand soin.</p>
                
                <div class="info-box info-confirmed">
                    <strong>Prochaine étape :</strong><br>
                    Notre service de livraison prendra contact avec vous sur votre numéro (<strong>{{ $order->phone_number }}</strong>) pour planifier la remise de votre colis.
                </div>
            @else
                <div class="status-badge badge-rejected">Annulée / Rejetée</div>
                <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
                <p>Nous vous informons que votre commande a été annulée ou à votre demande.</p>
                
                <div class="info-box info-rejected">
                    <strong>Motif :</strong><br>
                    {{ $order->cancellation_reason }}.
                </div>
            @endif

            <h3>Rappel des articles</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th style="text-align: center;">Qté</th>
                        <th style="text-align: right;">Prix</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td>
                                <span class="product-name">{{ $item->product->title }}</span>
                                @if ($item->variant)
                                    <span class="variant-info">
                                        @foreach($item->variant->attributValues as $av)
                                            {{ $av->attribute->name }}: {{ $av->value }}{{ !$loop->last ? ' | ' : '' }}
                                        @endforeach
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">x{{ $item->quantity }}</td>
                            <td style="text-align: right; font-weight: bold;">
                                {{ number_format($item->unit_price, 0, '.', ' ') }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <div class="total-row">
                    <span>Sous-total</span>
                    <span style="font-weight: bold;">{{ number_format($order->total_amount - ($order->municipality->shipping_fee ?? 0), 0, '.', ' ') }} FCFA</span>
                </div>
                <div class="total-row">
                    <span>Frais de livraison</span>
                    <span style="font-weight: bold;">+ {{ number_format($order->municipality->shipping_fee ?? 0, 0, '.', ' ') }} FCFA</span>
                </div>
                <div class="total-row total-main">
                    <span>TOTAL</span>
                    <span style="color: #93b86a;">{{ number_format($order->total_amount, 0, '.', ' ') }} FCFA</span>
                </div>
            </div>

            <p style="margin-top: 30px; font-size: 13px; text-align: center; color: #666;">
                Merci pour votre confiance.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Intellect Ivoire') }} | Abidjan, Côte d'Ivoire
            <br>
            <span style="font-weight: bold; color: #1a1a1a;">Service Client : +225 XX XX XX XX XX</span>
        </div>
    </div>
</body>

</html>