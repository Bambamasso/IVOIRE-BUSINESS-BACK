@extends('emails.layouts.order')

@section('title', 'Votre compte administrateur')
@section('heading', 'Bienvenue')

@section('content')
    <p>Bonjour,</p>
    <p>
        Un compte vous a été créé sur la plateforme
        <strong>Intellect Ivoire-Business</strong>. Voici vos identifiants de connexion :
    </p>

    <div class="info-box info-green">
        <strong>Identifiant :</strong> {{ $username }}<br>
        <strong>Mot de passe temporaire :</strong> {{ $password }}
    </div>

    <p style="margin-top: 20px; font-size: 13px; color: #666;">
        Pour votre sécurité, nous vous recommandons de changer ce mot de passe dès votre première connexion.
    </p>
@endsection
