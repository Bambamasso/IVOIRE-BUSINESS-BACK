<h2>Livraison</h2>
<div class="address-box">
    <strong>Destinataire :</strong> {{ $order->first_name }} {{ $order->last_name }}<br>
    <strong>Adresse :</strong> {{ $order->address }}<br>
    <strong>Zone :</strong> {{ $order->municipality->name ?? '' }}{{ ($order->municipality && $order->city) ? ', ' : '' }}{{ $order->city->name ?? '' }}<br>
    <strong>Contact :</strong> {{ $order->phone_number }}
</div>
