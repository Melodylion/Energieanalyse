<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            color: #2D2D2D;
            line-height: 1.5;
        }
        /* Main Header (Page 1) */
        .header {
            background-color: #F9F7F2;
            padding: 40px;
            text-align: center;
            border-bottom: 2px solid #C5A065;
        }
        /* Small Header (Page 2+) */
        .small-header {
            background-color: #F9F7F2;
            padding: 15px; /* Smaller padding */
            text-align: center;
            border-bottom: 1px solid #C5A065; /* Thinner line */
            margin-bottom: 30px;
        }
        .small-header img {
            max-height: 50px; /* Smaller Logo */
            width: auto;
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
        .page-break {
            page-break-before: always;
        }
        
        /* Report Box */
        .box {
            background-color: #F9F7F2;
            border-left: 4px solid #C5A065;
            padding: 20px;
        }
        
        .highlight { color: #C5A065; font-weight: bold; }

        /* Table Styles */
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

    <!-- PAGE 1: Intro & Chart -->
    <div class="header" style="padding: 20px;">
        @if(isset($logo))
            <div style="margin-bottom: 20px;">
                <img src="{{ $logo }}" style="max-height: 80px; width: auto;">
            </div>
        @endif
        
        <div class="title" style="font-size: 24px; margin-bottom: 5px;">{{ $texts['analysis_title'] }}</div>
        <div class="subtitle" style="text-transform: none; color: #666; font-size: 14px; letter-spacing: 0;">{{ $texts['analysis_text'] }}</div>
    </div>

    <div class="content" style="padding: 30px; text-align: center;">
        @if(isset($chart_image))
            <div style="margin-bottom: 20px; margin-top: 20px;">
                <h3 style="color: #2D2D2D; font-family: sans-serif; font-size: 18px; margin-bottom: 30px;">
                    {{ $texts['graph_header'] }}
                </h3>
                <!-- Increased Chart Size: ~700px or 90% -->
                <img src="{{ $chart_image }}" style="width: 90%; max-width: 750px; height: auto;">
            </div>
        @endif
    </div>

    <!-- PAGE 2: Master Body Logic -->
    <div class="page-break"></div>
    
    <div class="small-header">
        @if(isset($logo))
            <img src="{{ $logo }}">
        @endif
    </div>

    <div class="content" style="padding: 40px;">
        <div class="box" style="text-align: left; padding: 30px; background-color: #fff; border: 1px solid #eee; border-left: 4px solid #C5A065;">
            <div class="section-title" style="margin-bottom: 20px; border-bottom: none; color: #C5A065; font-size: 22px;">
                {{ $texts['report_title'] }}
            </div>

            @php
                // Prepare HTML Blocks for Assignments
                $fokusHtml = '
                <div style="margin-top: 20px; margin-bottom: 20px;">
                    <p style="font-weight: bold; color: #2D2D2D; margin-bottom: 5px;">Dein Fokus-Bereich (Stärkste Ausprägung):</p>
                    <div style="background: #F9F7F2; padding: 15px; border-radius: 4px;">
                        <strong style="color: #C5A065;">' . ($best_category_model->label ?? 'N/A') . '</strong>
                        <p style="font-size: 12px; margin-top: 5px; color: #444;">' . ($best_category_model->description_positive ?? $best_category_model->description ?? '') . '</p>
                    </div>
                </div>';

                $developmentHtml = '
                <div style="margin-top: 20px; margin-bottom: 20px;">
                    <p style="font-weight: bold; color: #2D2D2D; margin-bottom: 5px;">Dein Entwicklungs-Bereich (Schwächste Ausprägung):</p>
                    <div style="background: #fff; border: 1px solid #eee; padding: 15px; border-radius: 4px;">
                        <strong style="color: #666;">' . ($worst_category_model->label ?? 'N/A') . '</strong>
                        <p style="font-size: 12px; margin-top: 5px; color: #444;">' . ($worst_category_model->description_negative ?? $worst_category_model->description ?? '') . '</p>
                    </div>
                </div>';
            @endphp

            <div style="font-size: 14px; color: #444; line-height: 1.6; margin-bottom: 30px; white-space: pre-wrap;">{!! $texts['report_text'] !!}</div>
            

            

        </div>
    </div>

    <!-- PAGE 3: Detail Overview -->
    <div class="page-break"></div>

    <!-- Small Header Page 3 -->
    <div class="small-header">
        @if(isset($logo))
            <img src="{{ $logo }}">
        @endif
    </div>

    <div class="content" style="padding: 40px;">
        <div style="text-align: left;">
            <div class="section-title" style="font-size: 16px; color: #2D2D2D; margin-bottom: 25px;">Detaillierte Übersicht (Alle Kategorien)</div>
            <table class="scores-table">
                @foreach($categories as $key => $label)
                <tr>
                    <td class="label" style="padding: 8px 0; font-size: 12px; color: #555;">{{ $label }}</td>
                    <td class="value" style="padding: 8px 0; font-size: 12px;">
                        {{ number_format($scores[$key], 1) }}
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="footer">
        <a href="https://sophie-philipp.ch" style="color: #fff; text-decoration: none;">www.sophie-philipp.ch</a> &bull; Nervensystem Coaching
    </div>
</body>
</html>
