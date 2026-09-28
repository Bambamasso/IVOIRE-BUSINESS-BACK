<h2>{{ $heading ?? 'Récapitulatif des articles' }}</h2>
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
                    <span class="product-name">{{ $item->product->title ?? 'Produit indisponible' }}</span>
                    @if ($item->variant && $item->variant->attributValues->isNotEmpty())
                        <span class="variant-info">
                            @foreach ($item->variant->attributValues as $av)
                                {{ $av->attribute->name ?? 'Option' }}: {{ $av->value }}@unless($loop->last) | @endunless
                            @endforeach
                        </span>
                    @endif
                </td>
                <td style="text-align: center;">x{{ $item->quantity }}</td>
                <td style="text-align: right; font-weight: 700;">
                    {{ number_format($item->unit_price, 0, '.', ' ') }} FCFA
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@php($shipping = $order->municipality->shipping_fee ?? 0)
<div class="totals">
    <div class="total-row">
        <span>Sous-total</span>
        <span style="font-weight: 700;">{{ number_format($order->total_amount - $shipping, 0, '.', ' ') }} FCFA</span>
    </div>
    <div class="total-row">
        <span>Livraison</span>
        <span style="font-weight: 700;">+ {{ number_format($shipping, 0, '.', ' ') }} FCFA</span>
    </div>
    <div class="total-row total-main">
        <span>Total</span>
        <span style="color: #5f7d3c;">{{ number_format($order->total_amount, 0, '.', ' ') }} FCFA</span>
    </div>
</div>
