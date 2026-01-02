@extends('layouts.app')

@section('content')
<div class="text-center fade-in">
    <h1 class="font-serif text-4xl text-anthracite mb-4">Deine Analyse ist bereit</h1>
    <p class="text-gray-600 mb-12">Hier ist der erste Blick auf dein Nervensystem-Profil.</p>

    <!-- Visual Chart -->
    <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100 mb-12 max-w-lg mx-auto relative overflow-hidden">
        <!-- Blur Overlay if desired, currently showing chart fully as "teaser" -->
        <canvas id="nerveChart"></canvas>
    </div>

    <div class="bg-cream border border-gold/30 p-8 rounded-xl max-w-lg mx-auto">
        <h3 class="font-serif text-2xl text-gold mb-4">Dein persönlicher Report</h3>
        <p class="text-sm text-gray-600 mb-6">
            Um deine detaillierte Auswertung inklusive individueller Übungen für deinen Bereich 
            <strong class="text-anthracite">"{{ $result['lowest_category']['label'] }}"</strong> 
            zu erhalten, trage bitte deine E-Mail-Adresse ein.
        </p>

        <form action="{{ route('quiz.download') }}" method="POST" class="space-y-4 text-left">
            @csrf
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
        const ctx = document.getElementById('nerveChart').getContext('2d');
        
        // Data from Controller
        const labels = @json(array_values($categories));
        const dataValues = @json(array_values($result['scores']));

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Dein Nervensystem-Profil',
                    data: dataValues,
                    backgroundColor: 'rgba(197, 160, 101, 0.2)', // Gold/Cream transparent
                    borderColor: '#C5A065',
                    pointBackgroundColor: '#2D2D2D',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#C5A065',
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    r: {
                        angleLines: { color: '#E5E5E5' },
                        grid: { color: '#E5E5E5' },
                        pointLabels: {
                            font: { size: 10, family: 'Montserrat' },
                            color: '#666'
                        },
                        suggestedMin: 0,
                        suggestedMax: 10,
                        ticks: { stepSize: 2, display: false } // Hide numbers on axis for cleaner look
                    }
                },
                plugins: {
                    legend: { display: false }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    });
</script>
@endsection
