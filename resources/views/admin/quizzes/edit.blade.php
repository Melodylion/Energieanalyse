@extends('layouts.app')

@section('content')
<div class="max-w-[95%] mx-auto py-8">
    <div class="flex justify-between items-start mb-8">
        <div>
            <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-500 hover:text-gold">&larr; Zurück</a>
            <h1 class="text-3xl font-serif text-anthracite mt-1">Matrix Editor: {{ $quiz->title }}</h1>
        </div>
        <button form="matrixForm" type="submit" class="bg-green-600 text-white px-8 py-3 rounded-full hover:bg-green-700 shadow-lg text-sm uppercase tracking-widest sticky top-4 z-50">
            Speichern
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-8">{{ session('success') }}</div>
    @endif

    <form id="matrixForm" action="{{ route('admin.quizzes.update', $quiz) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Basic Settings -->
        <div class="bg-white p-6 rounded-xl border border-gray-100 mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Titel</label>
                <input type="text" name="title" value="{{ $quiz->title }}" class="w-full border-gray-200 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Slug / URL</label>
                <input type="text" name="slug" value="{{ $quiz->slug }}" class="w-full border-gray-200 rounded text-sm bg-gray-50">
            </div>
            <div class="flex items-center mt-6">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" id="active" {{ $quiz->active ? 'checked' : '' }} class="mr-2 text-gold focus:ring-gold">
                <label for="active" class="text-sm font-bold text-anthracite">Quiz Öffentlich Aktiv</label>
            </div>
        </div>

        <!-- THE MATRIX -->
        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-200">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-anthracite text-white">
                    <tr>
                        <th class="p-4 min-w-[300px] sticky left-0 bg-anthracite z-10 border-r border-gray-600">Frage</th>
                        @foreach($quiz->categories as $cat)
                            <th class="p-2 w-24 text-center border-l border-gray-600" title="{{ $cat->label }}">
                                <div class="truncate w-20 mx-auto">{{ $cat->label }}</div>
                                <div class="text-[9px] opacity-60">{{ $cat->key }}</div>
                            </th>
                        @endforeach
                        <th class="p-2 w-10 text-center bg-red-900">X</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($quiz->questions as $q)
                    <tr class="hover:bg-gray-50 group">
                        <!-- Question Text -->
                        <td class="p-2 sticky left-0 bg-white group-hover:bg-gray-50 border-r border-gray-100">
                            <textarea name="questions[{{ $q->id }}][text]" rows="2" class="w-full border-gray-200 rounded text-xs focus:border-gold focus:ring-0 resize-y">{{ $q->text }}</textarea>
                        </td>

                        <!-- Weights -->
                        @foreach($quiz->categories as $cat)
                            @php
                                $weight = $q->categories->find($cat->id)?->pivot->weight ?? 0;
                                $bgClass = $weight > 0 ? 'bg-green-50' : ($weight < 0 ? 'bg-red-50' : '');
                                $colorClass = $weight > 0 ? 'text-green-700' : ($weight < 0 ? 'text-red-700' : 'text-gray-300');
                            @endphp
                            <td class="p-1 border-l border-gray-100 text-center {{ $bgClass }}">
                                <input type="number" 
                                       name="questions[{{ $q->id }}][weights][{{ $cat->id }}]" 
                                       value="{{ $weight }}" 
                                       min="-10" max="10" 
                                       class="w-12 text-center text-xs border-0 bg-transparent focus:ring-0 font-bold {{ $colorClass }}">
                            </td>
                        @endforeach

                        <!-- Delete -->
                        <td class="text-center p-2 bg-gray-50 hover:bg-red-50 transition-colors">
                            <label class="cursor-pointer group flex flex-col items-center">
                                <span class="text-[10px] text-gray-400 group-hover:text-red-600 font-bold uppercase mb-1">Löschen?</span>
                                <input type="checkbox" name="questions[{{ $q->id }}][delete]" value="1" class="text-red-600 focus:ring-red-500 w-5 h-5 cursor-pointer">
                            </label>
                        </td>
                    </tr>
                    @endforeach

                    <!-- NEW QUESTION ROW -->
                    <tr class="bg-blue-50 border-t-2 border-blue-100">
                        <td class="p-4 sticky left-0 bg-blue-50 border-r border-blue-200">
                            <span class="block text-[10px] uppercase text-blue-600 font-bold mb-1">Neue Frage hinzufügen:</span>
                            <textarea name="new_questions[0][text]" rows="2" placeholder="Text für neue Frage..." class="w-full border-blue-200 rounded text-xs focus:border-blue-500 focus:ring-0"></textarea>
                        </td>
                        @foreach($quiz->categories as $cat)
                            <td class="p-1 border-l border-blue-200 text-center">
                                <input type="number" 
                                       name="new_questions[0][weights][{{ $cat->id }}]" 
                                       value="0" 
                                       min="-10" max="10" 
                                       class="w-12 text-center text-xs border-0 bg-transparent focus:ring-0 text-gray-400 focus:text-blue-700">
                            </td>
                        @endforeach
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p class="text-xs text-gray-500 mt-4">Tipp: Werte von -10 bis +10. '0' bedeutet kein Einfluss. Speichern nicht vergessen.</p>
    </form>


    <!-- --- CATEGORY MANAGER (COLUMNS) --- -->
    <div class="mt-16 border-t pt-12">
        <h2 class="text-2xl font-serif text-anthracite mb-6">Kategorien (Säulen) Verwalten</h2>
        
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Logic for Existing Categories -->
                @foreach($quiz->categories as $cat)
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <form action="{{ route('admin.categories.update', $cat) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Label (Sichtbar)</label>
                            <input type="text" name="label" value="{{ $cat->label }}" class="w-full text-sm border-gray-200 rounded px-2 py-1">
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Key (Intern)</label>
                            <input type="text" name="key" value="{{ $cat->key }}" class="w-full text-xs font-mono bg-gray-50 border-gray-200 rounded px-2 py-1">
                        </div>

                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-50">
                            <button type="submit" class="text-xs font-bold text-blue-600 hover:text-blue-800">Speichern</button>
                            
                            <!-- Delete Button (Separate Form trigger would be cleaner, but simple button ok here) -->
                            <!-- We use a dirty trick for layout or just a second form below -->
                        </div>
                    </form>
                    
                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="mt-2 text-right" onsubmit="return confirm('ACHTUNG: Wenn du diese Kategorie löschst, werden auch alle Gewichte in der Matrix für diese Spalte gelöscht! Sicher?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[10px] text-red-400 hover:text-red-700 uppercase tracking-widest font-bold">Löschen</button>
                    </form>
                </div>
                @endforeach

                <!-- CREATE NEW -->
                <div class="bg-blue-50 p-4 rounded-lg border-2 border-dashed border-blue-200 flex flex-col justify-center">
                    <form action="{{ route('admin.categories.store', $quiz) }}" method="POST">
                        @csrf
                        <h3 class="text-blue-800 font-bold mb-3 text-sm">Neue Kategorie</h3>
                        
                        <input type="text" name="label" placeholder="Label (z.B. Klarheit)" class="w-full text-sm border-blue-200 rounded px-2 py-1 mb-2 placeholder-blue-300">
                        <input type="text" name="key" placeholder="Key (z.B. klarheit)" class="w-full text-xs font-mono border-blue-200 rounded px-2 py-1 mb-3 placeholder-blue-300">
                        
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-xs font-bold uppercase tracking-widest hover:bg-blue-700">
                            Hinzufügen
                        </button>
                    </form>
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mt-6">
                <strong>Hinweis:</strong> Wenn du eine neue Kategorie hinzufügst, erscheint sie oben in der Matrix als neue Spalte. 
                Du musst dann oben die Gewichte für die Fragen eintragen (Standard ist 0).
            </p>
        </div>
    </div>
</div>
@endsection
