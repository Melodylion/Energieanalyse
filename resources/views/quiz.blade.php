@extends('layouts.app')

@section('content')
<div x-data="quiz()" x-cloak class="pb-12 max-w-3xl mx-auto">
    <!-- Progress Bar -->
    <div class="h-1 w-full bg-gray-200 rounded-full mb-8 overflow-hidden">
        <div class="h-full bg-gold transition-all duration-500 ease-out" :style="'width: ' + progress + '%'"></div>
    </div>

    <form @submit.prevent="submitQuiz">
        <template x-for="(q, index) in questions" :key="index">
            <div x-show="currentStep === index" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 min-h-[400px] flex flex-col justify-center text-center">
                
                <p class="text-xs text-gold font-bold tracking-widest uppercase mb-4" x-text="'Frage ' + (index + 1) + ' von 22'"></p>
                <h2 class="font-serif text-2xl md:text-3xl text-anthracite mb-12 leading-relaxed" x-text="q.text"></h2>

                <div class="mb-8 px-4">
                    <div class="flex justify-between text-xs text-gray-400 mb-2 uppercase tracking-widest font-bold">
                        <span>Stimme gar nicht zu</span>
                        <span>Stimme voll zu</span>
                    </div>
                    <input type="range" min="1" max="10" step="1" x-model.number="answers[index]" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <div class="mt-4 text-gold font-serif text-4xl font-bold" x-text="answers[index]"></div>
                </div>

                <div class="flex justify-between mt-auto pt-8 border-t border-gray-50">
                    <button type="button" @click="prev()" x-show="index > 0" class="text-sm text-gray-500 hover:text-anthracite transition-colors">
                        Zurück
                    </button>
                    <div x-show="index === 0"></div> <!-- Spacer -->
                    
                    <button type="button" @click="next()" x-show="index < 21" class="bg-anthracite text-white px-8 py-3 rounded-full hover:bg-gold transition-colors duration-300 text-sm tracking-widest uppercase">
                        Weiter
                    </button>
                    
                    <button type="submit" x-show="index === 21" class="bg-gold text-white px-8 py-3 rounded-full hover:bg-anthracite transition-colors duration-300 text-sm tracking-widest uppercase shadow-lg transform hover:scale-105">
                        Analyse beenden
                    </button>
                </div>
            </div>
        </template>
    </form>
</div>

<script>
    function quiz() {
        return {
            currentStep: 0,
            // 22 Questions hardcoded as requested
            questions: [
                // Gehört werden
                { text: 'Ich habe Menschen in meinem Leben, bei denen ich mich mit all meinen Themen sicher und gehört fühle.' },
                { text: 'Ich erlaube mir selbst, Raum für alles einzunehmen, was mich aktuell beschäftigt.' },
                // Wissen & Anwendung
                { text: 'Ich verstehe, wie mein Nervensystem auf Stress reagiert (z.B. Kampf/Flucht/Starre).' },
                { text: 'Ich weiß theoretisch, was mir bei Stress hilft, und kann dieses Wissen im Alltag abrufen.' },
                // Ankommen
                { text: 'Es fällt mir leicht, im Alltag bewusst Tempo rauszunehmen und innerlich anzukommen.' },
                { text: 'Ich kann Momente der Stille aushalten, ohne mich sofort ablenken zu müssen.' },
                // Wahrnehmen
                { text: 'Ich spüre während des Tages rechtzeitig, wenn mein Körper Anzeichen von Stress zeigt.' },
                { text: 'Ich kann meine Gedanken und Gefühle im Hier und Jetzt beobachten, ohne sie sofort zu bewerten.' },
                // Akzeptanz
                { text: 'Ich kann schwierige Gefühle oder Situationen annehmen, anstatt gegen sie anzukämpfen.' },
                { text: 'Ich bin in der Lage, liebevoll mit mir umzugehen, auch wenn ich mich gerade nicht „perfekt“ fühle.' },
                // Loslassen
                { text: 'Es gelingt mir gut, belastende Gedanken oder Aufgaben am Feierabend loszulassen.' },
                { text: 'Ich erkenne Dinge, die mir nicht mehr dienen, und kann mich aktiv von ihnen distanzieren.' },
                // Kraftquelle Aktivierung
                { text: 'Ich kenne meine individuellen Kraftquellen und weiß, wie ich sie aktiviere.' },
                { text: 'Ich fühle mich regelmäßig tief mit meiner inneren Energie und Stärke verbunden.' },
                // Stabilität
                { text: 'Ich finde eine gute Balance zwischen Phasen der Aktivität und Phasen der Erholung.' },
                { text: 'Ich fühle mich emotional stabil, auch wenn es im Außen turbulent zugeht.' },
                // Selbstfürsorge
                { text: 'Ich nehme mir bewusst Zeit für Tätigkeiten, die meine inneren Batterien aufladen.' },
                { text: 'Selbstfürsorge hat für mich im Alltag eine hohe Priorität, nicht nur im Notfall.' },
                // Körperarbeit
                { text: 'Ich nutze einfache körperliche Übungen (z.B. Atmen, Dehnen), um mein System zu beruhigen.' },
                { text: 'Ich fühle mich gut mit meinem Körper verbunden und achte auf seine Signale.' },
                // Co-Regulation
                { text: 'Ich kann mich in stressigen Momenten durch den Kontakt mit anderen Menschen beruhigen.' },
                { text: 'Ich fühle mich sicher darin, meine eigene Ruhe auch an mein Umfeld (Partner/Kinder) weiterzugeben.' }
            ],
            answers: Array(22).fill(5), // Default value 5
            
            get progress() {
                return ((this.currentStep + 1) / 22) * 100;
            },

            next() {
                if (this.currentStep < 21) {
                    this.currentStep++;
                }
            },
            prev() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                }
            },
            async submitQuiz() {
                try {
                    const response = await fetch('{{ route("quiz.submit") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ answers: this.answers })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        window.location.href = data.redirect;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Es gab einen Fehler beim Speichern. Bitte versuche es erneut.');
                }
            }
        }
    }
</script>
@endsection
