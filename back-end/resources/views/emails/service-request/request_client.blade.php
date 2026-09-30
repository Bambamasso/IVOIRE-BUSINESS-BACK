@extends('emails.layouts.order')

@section('title', 'Confirmation de votre demande de service')
@section('heading', 'Demande reçue')
@section('ref', $numeroDemande)

@section('content')
    <p>Bonjour {{ $nomClient }},</p>
    <p>
        Nous avons bien reçu votre demande de prestation concernant le service
        <strong>{{ $serviceDemande }}</strong>.
    </p>
    <p>
        Notre équipe administrative examine actuellement vos détails. Nous reviendrons vers vous
        très prochainement par email ou par téléphone pour discuter des spécificités et finaliser
        le prix avec vous.
    </p>

    <h2>Récapitulatif</h2>
    <div class="info-box info-green">
        <strong>Service :</strong> {{ $serviceDemande }}<br>
        <strong>Numéro de la demande :</strong> {{ $numeroDemande }}<br>
        <strong>Prix proposé :</strong>
        {{ $prixPropose ? number_format($prixPropose, 0, ',', ' ') . ' FCFA' : 'À négocier' }}
    </div>

    <p style="margin-top: 20px;">Merci de votre confiance !</p>
@endsection
