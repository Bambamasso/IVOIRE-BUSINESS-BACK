@extends('emails.layouts.order')

@section('title', 'Nouvelle commande')
@section('heading', 'Nouvelle commande')
@section('ref', 'Référence : #' . $order->order_number)

@section('content')
    <p>Une nouvelle commande vient d'être passée sur <strong>{{ config('app.name', 'Intellect Ivoire') }}</strong>.</p>

    <p>
        <span class="badge {{ $order->payment_method === 'online' ? 'badge-green' : 'badge-gold' }}">
            {{ $order->payment_method === 'online' ? 'Payée en ligne' : 'Paiement à la livraison' }}
        </span>
    </p>

    @include('emails.orders.partials.items', ['heading' => 'Détail de la commande'])

    <h2>Client & livraison</h2>
    <div class="address-box">
        <strong>Nom :</strong> {{ $order->first_name }} {{ $order->last_name }}<br>
        <strong>Email :</strong> {{ $order->email ?? '—' }}<br>
        <strong>Téléphone :</strong> {{ $order->phone_number }}<br>
        <strong>Adresse :</strong> {{ $order->address }}<br>
        <strong>Zone :</strong> {{ $order->municipality->name ?? '' }}{{ ($order->municipality && $order->city) ? ', ' : '' }}{{ $order->city->name ?? '' }}
    </div>

    <div class="info-box {{ $order->payment_method === 'online' ? 'info-green' : 'info-gold' }}">
        <strong>Règlement :</strong>
        @if ($order->payment_method === 'online')
            💳 Paiement en ligne — fonds sécurisés.
        @else
            💵 Espèces à encaisser à la livraison.
        @endif
    </div>

    <a class="btn" href="{{ config('shop.frontend_url') }}/admin/orders/detail/{{ $order->id }}">
        Voir la commande
    </a>
@endsection
