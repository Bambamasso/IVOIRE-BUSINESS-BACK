<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seuil d'alerte de stock bas
    |--------------------------------------------------------------------------
    |
    | Lorsqu'un produit (ou une variante) descend à ce niveau de stock ou en
    | dessous — sans être totalement épuisé — une alerte est journalisée et un
    | e-mail est envoyé à l'administrateur. Mettre à 0 pour désactiver.
    |
    */

    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Coordonnées utilisées dans les e-mails
    |--------------------------------------------------------------------------
    */

    'frontend_url'  => rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/'),
    'support_phone' => env('SUPPORT_PHONE', ''),

];
