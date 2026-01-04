@extends('layouts.app')

@section('content')
<div x-data="quiz()" x-cloak class="pb-12 max-w-3xl mx-auto">
    <!-- Quiz Title moved to Header -->

    <!-- Progress Bar -->
    <div class="h-1 w-full bg-gray-200 rounded-full mb-8 overflow-hidden">
        <div class="h-full bg-gold transition-all duration-500 ease-out" :style="'width: ' + progress + '%'"></div>
    </div>

    <form @submit.prevent="submitQuiz">
        <!-- INTRO SLIDE -->
        <div x-show="currentStep === -1" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 min-h-[400px] flex flex-col justify-center text-center">
            
            <h2 class="font-serif text-3xl text-anthracite mb-8">{{ $quiz->title }}</h2>
            <div class="prose prose-gold mx-auto mb-12 text-gray-600">
                {!! nl2br(e($quiz->intro_text)) !!}
            </div>

            <button type="button" @click="start()" class="bg-anthracite text-white px-12 py-4 rounded-full hover:bg-gold transition-colors duration-300 text-sm tracking-widest uppercase font-bold shadow-lg transform hover:scale-105 mx-auto">
                Loslegen
            </button>
        </div>

        <template x-for="(q, index) in questions" :key="index">
            <div x-show="currentStep === index" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 min-h-[400px] flex flex-col justify-center text-center">
                
                <p class="text-xs text-gold font-bold tracking-widest uppercase mb-4" x-text="'Frage ' + (index + 1) + ' von ' + questions.length"></p>
                <h2 class="font-serif text-2xl md:text-3xl text-anthracite mb-12 leading-relaxed" x-text="q.text"></h2>
                
                <!-- ... existing content ... -->

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
                    
                    <button type="button" @click="next()" x-show="currentStep < questions.length - 1" class="bg-anthracite text-white px-8 py-3 rounded-full hover:bg-gold transition-colors duration-300 text-sm tracking-widest uppercase">
                        Weiter
                    </button>
                    
                    <button type="submit" x-show="currentStep === questions.length - 1" class="bg-gold text-white px-8 py-3 rounded-full hover:bg-anthracite transition-colors duration-300 text-sm tracking-widest uppercase shadow-lg transform hover:scale-105">
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
            // If intro text exists, start at -1 (Intro Slide). Else 0 (First Question).
            currentStep: {{ !empty($quiz->intro_text) ? -1 : 0 }},
            // Injected from Controller
            questions: @json($jsQuestions),
            
            // Dynamic Answer Array based on Question Count
            answers: Array({{ count($jsQuestions) }}).fill(5), 

            start() {
                this.currentStep = 0;
            }, 
            
            get progress() {
                return ((this.currentStep + 1) / this.questions.length) * 100;
            },

            next() {
                if (this.currentStep < this.questions.length - 1) {
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
                    // Route now defaults to 'energieanalyse' if no slug provided, but cleaner to be explicit if we can.
                    // For now, simple POST to submit. Logic handles the slug serverside if we keep it simple.
                    // Or we could inject the submit URL.
                    const response = await fetch('{{ route("quiz.submit", ["slug" => $quiz->slug]) }}', {
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
