@extends('emails.layouts.order')

@section('title', 'Confirmation de commande')
@section('heading', 'Merci pour votre commande')
@section('ref', 'Référence : #' . $order->order_number)

@section('content')
    <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
    <p>
        Votre commande a bien été enregistrée chez <strong>{{ config('app.name', 'Intellect Ivoire') }}</strong>.
        @if ($order->payment_method === 'online')
            Votre paiement a été reçu. Vous serez notifié dès que votre commande sera préparée.
        @else
            Vous recevrez un e-mail dès qu'elle aura été validée par notre équipe.
        @endif
    </p>

    @include('emails.orders.partials.items')

    @include('emails.orders.partials.delivery')

    <div class="info-box {{ $order->payment_method === 'online' ? 'info-green' : 'info-gold' }}">
        <strong>Mode de paiement :</strong><br>
        @if ($order->payment_method === 'online')
            💳 Paiement en ligne — réglé.
        @else
            💵 Paiement en espèces à la livraison. Un conseiller vous contactera au
            <strong>{{ $order->phone_number }}</strong> pour convenir du jour et du lieu de livraison.
        @endif
    </div>

    <p style="margin-top: 28px; font-size: 13px; text-align: center; color: #666;">
        Une question ? Répondez simplement à cet e-mail.
    </p>
@endsection
