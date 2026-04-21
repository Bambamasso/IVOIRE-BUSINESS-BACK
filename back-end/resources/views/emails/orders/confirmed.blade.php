<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de Commande</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f8f8; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px 0; }
        .footer { text-align: center; font-size: 0.8em; color: #777; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Confirmation de votre commande #{{ $order->order_number }}</h2>
        </div>
        <div class="content">
            <p>Bonjour {{ $order->user->name ?? 'Client' }},</p>
            <p>Nous vous confirmons que votre commande a été bien reçue et est en cours de traitement. Voici les détails de votre commande :</p>

            <h3>Détails de la commande</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td>
                                {{ $item->product->name }}
                                @if ($item->variant)
                                    ({{ $item->variant->name }})
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;">Montant Total:</td>
                        <td>{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <h3>Adresse de livraison</h3>
            <p>
                {{ $order->address }}<br>
                {{ $order->municipality->name ?? '' }}, {{ $order->city->name ?? '' }}<br>
                Téléphone: {{ $order->phone_number }}
            </p>

            <p>Méthode de paiement: {{ $order->payment_method === 'online' ? 'Paiement en ligne' : 'Paiement à la livraison' }}</p>

            <p>Merci de votre confiance !</p>
            <p>L'équipe de {{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
