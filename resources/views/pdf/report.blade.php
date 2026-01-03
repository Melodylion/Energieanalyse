<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', sans-serif; /* Standard safe font for PDF */
            margin: 0;
            padding: 0;
            color: #2D2D2D;
            line-height: 1.5;
        }
        .header {
            background-color: #F9F7F2;
            padding: 40px;
            text-align: center;
            border-bottom: 2px solid #C5A065;
        }
        .title {
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 24px;
            color: #C5A065;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #666;
        }
        .content {
            padding: 50px;
        }
        .section-title {
            font-size: 18px;
            color: #C5A065;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .scores-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 40px;
        }
        .scores-table td {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .scores-table .label { width: 70%; }
        .scores-table .value { width: 30%; text-align: right; font-weight: bold; }
        
        .box {
            background-color: #F9F7F2;
            border-left: 4px solid #C5A065;
            padding: 20px;
            margin-top: 30px;
        }
        .highlight {
            color: #C5A065;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background-color: #2D2D2D;
            color: #fff;
            text-align: center;
            line-height: 50px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="header" style="padding: 20px;">
        @if(isset($logo))
            <div style="margin-bottom: 20px;">
                <img src="{{ $logo }}" style="max-height: 80px; width: auto;">
            </div>
            <!-- Optional: Show title below logo if desired, or just replace logic -->
            <div class="title" style="font-size: 18px;">{{ $title }}</div>
        @else
            <div class="title" style="font-size: 18px;">{{ $title }}</div>
        @endif
        <div class="subtitle">Persönliche Analyse für {{ $email }}</div>
    </div>

    <div class="content" style="padding: 30px;">
        <p style="margin-top:0;">Hallo,</p>
        <p>hier ist deine persönliche Auswertung vom {{ $date }}.</p>

        <div class="section-title" style="margin-bottom: 10px;">Deine Ergebnisse</div>
        
        <table class="scores-table" style="margin-bottom: 20px;">
            @foreach($categories as $key => $label)
            <tr>
                <td class="label" style="padding: 4px 0;">{{ $label }}</td>
                <td class="value" style="padding: 4px 0;">
                    {{ number_format($scores[$key], 1) }}
                    <span style="display:inline-block; margin-left:10px; background:#eee; width:40px; height:4px; vertical-align:middle;">
                        <span style="display:block; background:#C5A065; height:100%; width: {{ $scores[$key] * 10 }}%"></span>
                    </span>
                </td>
            </tr>
            @endforeach
        </table>

        <!-- Focus Area Compact -->
        <div style="margin-bottom: 20px;">
            <span class="section-title" style="border:none; margin-right: 10px;">Fokus-Bereich:</span>
            <span style="font-size: 16px; font-weight: bold; color: #2D2D2D;">{{ $lowest_category['label'] }} ({{ $lowest_category['value'] }})</span>
        </div>

        <div class="box" style="padding: 15px; margin-top: 10px;">
            <p style="margin: 0; font-weight: bold; color:#C5A065;">Dein Impuls:</p>
            <p style="margin-top: 5px; font-style: italic; font-size: 11px;">
                "Nimm dir heute 5 Minuten Zeit. Schließe die Augen und atme tief in den Bauch. 
                Erlaube dir, einfach nur dazusein, ohne etwas leisten zu müssen."
            </p>
        </div>

        <!-- Personal Invitation -->
        <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            <h3 style="color: #C5A065; font-size: 14px; text-transform:uppercase;">Einladung zum Gespräch</h3>
            <p style="font-size: 11px; color: #666;">
                Möchtest du tiefer in deine Analyse eintauchen oder herausfinden, wie ich dich individuell begleiten kann?
                Ich lade dich herzlich zu einem unverbindlichen Kennenlerngespräch ein.
            </p>
            <p style="font-size: 11px; margin-top: 10px;">
                <strong>Melde dich gerne direkt unter:</strong><br>
                <a href="mailto:zauberbruecke@sophie-philipp.ch" style="color: #C5A065; text-decoration: none;">zauberbruecke@sophie-philipp.ch</a>
                oder besuche meine Website:<br>
                <a href="https://sophie-philipp.ch" target="_blank" style="color: #C5A065; text-decoration: none; font-weight:bold;">www.sophie-philipp.ch</a>
            </p>
        </div>
    </div>

    <div class="footer">
        <a href="https://sophie-philipp.ch" style="color: #fff; text-decoration: none;">www.sophie-philipp.ch</a> &bull; Nervensystem Coaching
    </div>
</body>
</html>
