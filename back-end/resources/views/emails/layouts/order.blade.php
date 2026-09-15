<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'Intellect Ivoire'))</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            background-color: #f4f5f7;
        }

        .container {
            max-width: 600px;
            margin: 24px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .header {
            background-color: #1a1a1a;
            padding: 36px 24px;
            text-align: center;
            color: #ffffff;
        }

        .header img {
            max-width: 110px;
            margin-bottom: 14px;
            border-radius: 8px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header .ref {
            margin-top: 8px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #93b86a;
        }

        .content {
            padding: 28px 30px;
            font-size: 14px;
        }

        .content h2 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8a8f98;
            margin: 26px 0 10px;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 16px;
        }

        .badge-green {
            background: #93b86a1f;
            color: #5f7d3c;
        }

        .badge-gold {
            background: #e8d3933d;
            color: #8a6d1f;
        }

        .badge-red {
            background: #e74c3c1f;
            color: #c0392b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #9aa0a6;
            letter-spacing: 1px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        td {
            padding: 14px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            vertical-align: top;
        }

        .product-name {
            font-weight: 700;
            color: #1a1a1a;
            display: block;
        }

        .variant-info {
            font-size: 11px;
            color: #5f7d3c;
            font-weight: 700;
            text-transform: uppercase;
        }

        .totals {
            margin-top: 18px;
            background: #fafafa;
            padding: 18px;
            border-radius: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .total-main {
            font-size: 17px;
            font-weight: 800;
            color: #1a1a1a;
            margin-top: 8px;
            padding-top: 10px;
            border-top: 2px solid #ececec;
        }

        .info-box {
            padding: 14px 16px;
            margin-top: 16px;
            border-radius: 10px;
            font-size: 13px;
        }

        .info-green {
            border-left: 4px solid #93b86a;
            background: #f7faf3;
        }

        .info-gold {
            border-left: 4px solid #e8d393;
            background: #fdfbf2;
        }

        .info-red {
            border-left: 4px solid #e74c3c;
            background: #fdf6f5;
        }

        .address-box {
            border-left: 4px solid #93b86a;
            padding: 12px 15px;
            margin-top: 12px;
            background: #f9f9f9;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
        }

        .btn {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 26px;
            background-color: #1a1a1a;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer {
            text-align: center;
            padding: 22px;
            font-size: 11px;
            color: #aeb2b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer strong {
            color: #1a1a1a;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            @if(file_exists(public_path('storage/Logo.png')))
                <img src="{{ $message->embed(public_path('storage/Logo.png')) }}"
                    alt="{{ config('app.name', 'Intellect Ivoire') }}">
            @endif
            <h1>@yield('heading')</h1>
            @hasSection('ref')
                <div class="ref">@yield('ref')</div>
            @endif
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Intellect Ivoire') }} — Abidjan, Côte d'Ivoire
            @if(config('shop.support_phone'))
                <br><strong>Service client : {{ config('shop.support_phone') }}</strong>
            @endif
        </div>
    </div>
</body>

</html>