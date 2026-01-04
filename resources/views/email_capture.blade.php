@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4 text-center fade-in">
    
    <!-- 1. Analyse Title -->
    <h1 class="font-serif text-4xl text-anthracite mb-4">{{ $texts['analysis_title'] }}</h1>
    
    <!-- 2. Analyse Text -->
    <p class="text-gray-600 mb-12">{{ $texts['analysis_text'] }}</p>

    <div class="bg-white rounded-lg shadow-xl p-8 mb-8 backdrop-blur-sm bg-opacity-90 border border-[#d4af37]/20">
        <!-- 3. Graph Header -->
        <h2 class="text-xl font-serif text-center text-anthracite mb-6">{{ $texts['graph_header'] }}</h2>
        
        <!-- Increased Size Container -->
        <div class="relative w-full max-w-2xl mx-auto aspect-square md:aspect-[4/3]">
            <canvas id="resultChart"></canvas>
        </div>
        <!-- Legend for Truncated Text -->
        <p class="text-xs text-gray-400 text-center mt-4 italic">
            (...): Abgeschnittener Text – fahre über den Punkt im Diagramm für mehr Details.
        </p>
    </div>

    <!-- Email Capture Form -->
    <div class="bg-cream border border-gold/30 p-8 rounded-xl max-w-lg mx-auto">
        <!-- 4. Report Title -->
        <h3 class="font-serif text-2xl text-gold mb-4">{{ $texts['report_title'] }}</h3>
        
        <!-- 5. Report Teaser -->
        <p class="text-sm text-gray-600 mb-6">
            {!! nl2br(e($texts['report_teaser'])) !!}
        </p>

        <form action="{{ route('quiz.download') }}" method="POST" class="space-y-4 text-left" id="downloadForm">
            @csrf
            
            <!-- Hidden Input for Chart Image -->
            <input type="hidden" name="chart_image" id="chartImageInput">

            <div>
                <label for="email" class="block text-xs uppercase tracking-widest text-gray-500 mb-1">E-Mail Adresse</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required 
                       class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-gold focus:ring-1 focus:ring-gold outline-none transition-all">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @error('privacy') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-start gap-3">
                <input type="checkbox" name="privacy" id="privacy" required class="mt-1">
                <label for="privacy" class="text-xs text-gray-500 leading-relaxed">
                    Ich stimme der Verarbeitung meiner Daten zur Erstellung des Reports zu (Datenschutz).
                </label>
            </div>

            <div class="flex items-start gap-3">
                <input type="checkbox" name="marketing" id="marketing" class="mt-1">
                <label for="marketing" class="text-xs text-gray-500 leading-relaxed">
                    Ich möchte weitere Impulse für mein Nervensystem per Newsletter erhalten (jederzeit kündbar).
                </label>
            </div>

            <div class="flex items-start gap-3">
                <input type="checkbox" name="contact_request" id="contact_request" class="mt-1">
                <label for="contact_request" class="text-xs text-gray-500 leading-relaxed">
                    Ich möchte für ein persönliches Kennenlerngespräch kontaktiert werden.
                </label>
            </div>

            <button type="submit" class="w-full bg-anthracite text-white px-8 py-4 rounded-full hover:bg-gold transition-colors duration-300 text-sm tracking-widest uppercase shadow-lg mt-4">
                PDF Report Anfordern
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('resultChart').getContext('2d');
        
        // 1. Full Labels
        const fullLabels = @json(array_values($categories));
        
        // Helper: Word Wrap Function (Result = Array of Strings)
        const wrapText = (str, maxLen) => {
            if (str.length <= maxLen) return [str];
            const words = str.split(' ');
            const lines = [];
            let currentLine = words[0];

            for (let i = 1; i < words.length; i++) {
                if (currentLine.length + 1 + words[i].length <= maxLen) {
                    currentLine += ' ' + words[i];
                } else {
                    lines.push(currentLine);
                    currentLine = words[i];
                }
            }
            lines.push(currentLine);
            return lines;
        };
        
        // 2. Format Labels for Axis: TRUNCATE (45) + WRAP (25)
        const formatAxisLabels = (labels, maxTotal = 45, maxLine = 25) => {
            return labels.map(label => {
                // A. Truncate
                let text = label;
                if (text.length > maxTotal) {
                    text = text.substr(0, maxTotal - 1) + ' (...)';
                }
                // B. Wrap
                return wrapText(text, maxLine);
            });
        };

        const displayLabels = formatAxisLabels(fullLabels, 45, 25);
        
        // 3. Data Logic
        const resultScores = @json($result['scores']);
        const categoryKeys = @json(array_keys($categories));
        const dataValues = categoryKeys.map(key => resultScores[key] || 0);

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: displayLabels, // Combined Logic Labels
                datasets: [{
                    label: 'Dein Profil',
                    data: dataValues,
                    backgroundColor: 'rgba(197, 160, 101, 0.2)', 
                    borderColor: '#C5A065',
                    pointBackgroundColor: '#2D2D2D',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#C5A065',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                layout: {
                    padding: 20 // Keep padding
                },
                scales: {
                    r: {
                        angleLines: { color: '#E5E5E5' },
                        grid: { color: '#E5E5E5' },
                        pointLabels: {
                            font: { size: 9, family: 'Montserrat', weight: '500' }, 
                            color: '#666'
                        },
                        suggestedMin: 0,
                        suggestedMax: 10,
                        ticks: { stepSize: 2, display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                         callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                // WRAP Tooltip Title at 40 chars
                                return wrapText(fullLabels[index], 40);
                            },
                            label: function(context) {
                                return 'Wert: ' + context.raw + ' / 10';
                            }
                        },
                        backgroundColor: 'rgba(45, 45, 45, 0.9)',
                        titleFont: { family: 'serif', size: 13 },
                        bodyFont: { family: 'Montserrat', size: 12 },
                        padding: 10,
                        displayColors: false
                    }
                },
                animation: { duration: 1500, easing: 'easeOutQuart' }
            }
        });
        
        // Capture logic for PDF
        document.getElementById('downloadForm').addEventListener('submit', function(e) {
             const canvas = document.getElementById('resultChart');
             document.getElementById('chartImageInput').value = canvas.toDataURL('image/png');
        });
    });
</script>
@endsection
