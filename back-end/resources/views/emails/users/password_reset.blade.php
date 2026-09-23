@extends('emails.layouts.order')

@section('title', 'Mot de passe réinitialisé')
@section('heading', 'Mot de passe réinitialisé')

@section('content')
    <p>Bonjour,</p>
    <p>
        Le mot de passe de votre compte (identifiant <strong>{{ $username }}</strong>) vient d'être réinitialisé.
        Voici votre nouveau mot de passe temporaire :
    </p>

    <div class="info-box info-gold">
        <strong>Mot de passe temporaire :</strong> {{ $password }}
    </div>

    <p style="margin-top: 20px; font-size: 13px; color: #666;">
        Pour votre sécurité, nous vous recommandons de changer ce mot de passe dès votre prochaine connexion.
        Si vous n'êtes pas à l'origine de cette demande, contactez immédiatement un administrateur.
    </p>
@endsection
