<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Nouvelle Commande Reçue - INTELLECT IVOIRE</title>
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
            color: #93b86a;
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

        .badge-online {
            background: #2ecc711a;
            color: #2ecc71;
        }

        .badge-delivery {
            background: #e67e221a;
            color: #e67e22;
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

        .address-box {
            border-left: 4px solid #93b86a;
            padding-left: 15px;
            margin-top: 20px;
            background: #f9f9f9;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .payment-method {
            margin-top: 20px;
            padding: 15px;
            border: 1px dashed #ddd;
            border-radius: 8px;
            font-size: 13px;
            background-color: #fafafa;
        }

        .btn-panel {
            display: block;
            width: 200px;
            margin: 30px auto 10px auto;
            padding: 12px 20px;
            background-color: #1a1a1a;
            color: #ffffff !important;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            <h2>Nouvelle Commande Enregistrée</h2>
            <p>RÉFÉRENCE : #{{ $order->order_number }}</p>
        </div>

        <div class="content">
           
            <p>Bonjour,</p>
            <p>Vous avez reçu une nouvelle commande sur votre boutique en ligne <strong>INTELLECT-IVOIRE</strong>.
                Veuillez vérifier les détails ci-dessous afin de lancer le traitement.</p>

            <h3>Récapitulatif de la commande</h3>
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
                    <span style="font-weight: bold;">+ {{ number_format($order->municipality->shipping_fee ?? 0, 0, '.', ' ') }}
                        FCFA</span>
                </div>
                <div class="total-row total-main">
                    <span style="color: #1a1a1a;">TOTAL À PERCEVOIR</span>
                    <span style="color: #93b86a;">{{ number_format($order->total_amount, 0, '.', ' ') }} FCFA</span>
                </div>
            </div>

            <h3>Détails du client et de la livraison</h3>
            <div class="address-box">
                <p style="margin: 0; font-size: 14px;">
                    <strong>Nom complet :</strong> {{ $order->first_name }} {{ $order->last_name }}<br>
                    <strong>Email :</strong> {{ $order->email ?? 'Non renseigné' }}<br>
                    <strong>Contact :</strong> {{ $order->phone_number }}<br>
                    <strong>Adresse :</strong> {{ $order->address }}<br>
                    <strong>Zone de livraison :</strong> {{ $order->municipality->name ?? '' }}, {{ $order->city->name
                    ?? '' }}
                </p>
            </div>

            <div class="payment-method">
                <strong>Règlement :</strong><br>
                @if($order->payment_method === 'online')
                <span style="color: #2ecc71; font-weight: bold;">💳 Paiement en ligne Paystack (Fonds sécurisés)</span>
                @else
                <span style="color: #e67e22; font-weight: bold;">💵 En espèces à la livraison (En attente de
                    traitement)</span>
                @endif
            </div>

            <a href="{{ url('/admin/orders') }}" class="btn-panel">Voir la commande</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Intellect Ivoire') }} | Notification Système Admin
        </div>
    </div>
</body>

</html>