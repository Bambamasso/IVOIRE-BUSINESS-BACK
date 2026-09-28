@extends('emails.layouts.order')

@php($code = $order->status->code ?? null)

@section('title', 'Mise à jour de votre commande')
@section('heading')
    @switch($code)
        @case('validated') Commande confirmée @break
        @case('delivered') Commande livrée @break
        @default Commande annulée
    @endswitch
@endsection
@section('ref', 'Référence : #' . $order->order_number)

@section('content')
    <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>

    @if ($code === 'validated')
        <span class="badge badge-green">En préparation</span>
        <p>Bonne nouvelle ! Votre commande a été validée par notre équipe et est en cours de préparation.</p>
        <div class="info-box info-green">
            <strong>Prochaine étape :</strong><br>
            Notre service de livraison vous contactera au <strong>{{ $order->phone_number }}</strong>
            pour planifier la remise de votre colis.
        </div>
    @elseif ($code === 'delivered')
        <span class="badge badge-gold">Livrée</span>
        <p>Votre commande a bien été livrée. Merci pour votre confiance !</p>
        <div class="info-box info-gold">
            <strong>Un souci avec votre commande ?</strong><br>
            Répondez à cet e-mail, notre service client vous répondra rapidement.
        </div>
    @else
        <span class="badge badge-red">Annulée</span>
        <p>Nous vous informons que votre commande a été annulée.</p>
        @if ($order->cancellation_reason)
            <div class="info-box info-red">
                <strong>Motif :</strong><br>
                {{ $order->cancellation_reason }}
            </div>
        @endif
        @if (($order->paymentStatus->code ?? null) === 'refunded')
            <div class="info-box info-green">
                <strong>Remboursement :</strong><br>
                Votre paiement en ligne vous sera remboursé sous 3 à 7 jours ouvrés.
            </div>
        @endif
    @endif

    @include('emails.orders.partials.items', ['heading' => 'Rappel des articles'])
@endsection
