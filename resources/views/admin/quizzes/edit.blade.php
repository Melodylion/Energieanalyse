@extends('layouts.app')

@section('content')
<div class="max-w-[95%] mx-auto py-8">
    <div class="flex justify-between items-start mb-8">
        <div>
            <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-500 hover:text-gold">&larr; Zurück</a>
            <h1 class="text-3xl font-serif text-anthracite mt-1">Quiz Editor: {{ $quiz->title }}</h1>
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

        <!-- 1. Basic Settings & Metadata -->
        <div class="bg-white p-6 rounded-xl border border-gray-100 mb-8 shadow-sm">
            <h2 class="text-sm font-bold text-anthracite mb-4 uppercase tracking-widest border-b pb-2">Basis-Einstellungen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-4">
                    <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Interner Titel</label>
                    <input type="text" name="title" value="{{ $quiz->title }}" class="w-full border-gray-200 rounded text-sm focus:border-gold focus:ring-gold">
                </div>
                <div class="lg:col-span-4">
                    <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">URL Slug</label>
                    <input type="text" name="slug" value="{{ $quiz->slug }}" class="w-full border-gray-200 rounded text-sm bg-gray-50 font-mono text-gray-600 focus:border-gold focus:ring-gold">
                </div>
                <!-- Active/Public Toggle (More Prominent) -->
                <div class="lg:col-span-4 bg-gray-50 rounded-lg p-3 border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="block text-sm font-bold text-anthracite">Quiz Öffentlich?</span>
                        <span class="text-[10px] text-gray-500 block leading-tight">Wenn deaktiviert, ist das Quiz für Besucher nicht erreichbar durch direkte Links.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" name="active" value="1" class="sr-only peer" {{ $quiz->active ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-gold rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>
                
                <div class="lg:col-span-12">
                    <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Interne Notizen / Beschreibung</label>
                    <textarea name="description" rows="1" class="w-full rounded-md border-gray-200 text-sm placeholder-gray-300 focus:border-gold focus:ring-gold">{{ old('description', $quiz->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- INFO BOX: Placeholders (Between Settings and Content) -->
        <div class="bg-yellow-50 border border-yellow-200 p-6 rounded-xl mb-8 shadow-sm">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <strong class="block text-yellow-800 uppercase tracking-widest mb-2">💡 Spickzettel: Dynamische Platzhalter</strong>
                    <p class="text-xs text-yellow-700 max-w-2xl">
                        Nutze diese Codes in deinen Texten unten. Sie werden automatisch mit den Ergebnissen des Teilnehmers ersetzt.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-xs text-yellow-900">
                    <div>
                        <code class="bg-white px-1 rounded border border-yellow-100 font-bold">@{{best-category}}</code> 
                        <span class="opacity-70">➔ Name Stärkste Kat.</span>
                    </div>
                    <div>
                        <code class="bg-white px-1 rounded border border-yellow-100 font-bold">@{{best-category-description}}</code> 
                        <span class="opacity-70">➔ Text Stärkste Kat.</span>
                    </div>
                    
                    <div>
                        <code class="bg-white px-1 rounded border border-yellow-100 font-bold">@{{worst-category}}</code> 
                        <span class="opacity-70">➔ Name Schwächste Kat.</span>
                    </div>
                    <div>
                        <code class="bg-white px-1 rounded border border-yellow-100 font-bold">@{{worst-category-description}}</code> 
                        <span class="opacity-70">➔ Text Schwächste Kat.</span>
                    </div>

                    <div class="col-span-1 md:col-span-2 mt-2 pt-2 border-t border-yellow-200/50">
                        <code class="bg-white px-1 rounded border border-yellow-100 font-bold">@{{fokus_area}}</code> 
                        <span class="opacity-70">➔ Komplette "Fokus" Box (Design)</span> <br>
                        <code class="bg-white px-1 rounded border border-yellow-100 font-bold">@{{development_area}}</code> 
                        <span class="opacity-70">➔ Komplette "Entwicklungs" Box (Design)</span>
                    </div>
                    
                    <div class="col-span-1 md:col-span-2 mt-1">
                        <code class="bg-white px-1 rounded border border-yellow-100 font-bold">@{{url}}</code> 
                        <span class="opacity-70">➔ Link zum PDF Download (Nur für E-Mails)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Visual Content Editor (Full Width) -->
        <div class="bg-white p-8 rounded-xl border border-gray-100 mb-8 shadow-sm relative">
            
            <!-- STICKY HELPER: Placeholders (moved) --> 
            <!-- (Removed from here, will insert above) -->

            <h2 class="text-sm font-bold text-anthracite mb-6 uppercase tracking-widest border-b pb-2">Inhalte & Texte (Visueller Editor)</h2>
            
            <!-- Intro Text -->
            <div class="mb-10 max-w-3xl mx-auto">
                <label class="block text-xs uppercase tracking-widest text-center text-gray-500 mb-2">Start-Seite: Begrüßungstext</label>
                <div class="relative">
                    <textarea name="intro_text" rows="3" class="w-full rounded-lg border-gray-300 text-center text-lg font-serif text-gray-700 shadow-sm focus:border-gold focus:ring-gold p-4" placeholder="Willkommen zum Nervensystem Kompass...">{{ old('intro_text', $quiz->intro_text) }}</textarea>
                    <div class="absolute -top-3 -right-3 text-4xl text-gray-100 transform rotate-12 pointer-events-none">❝</div>
                </div>
            </div>

            <!-- RESULT PAGE VISUAL BUILDER -->
            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 bg-gray-50/50 flex flex-col items-center space-y-12">
                <div class="w-full text-center border-b border-gray-200 pb-2 mb-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Vorschau: Ergebnisseite</span>
                </div>

                <!-- 1 & 2 Header -->
                <div class="w-full max-w-2xl text-center space-y-4">
                    <div class="group relative">
                        <span class="absolute -left-24 top-2 text-[10px] text-blue-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">1. Haupt-Titel</span>
                        <input type="text" name="analysis_title" value="{{ old('analysis_title', $quiz->analysis_title) }}" 
                               class="w-full text-center text-4xl font-serif text-anthracite border-transparent border-b border-dashed border-b-gray-300 bg-transparent focus:border-gold focus:ring-0 placeholder-gray-300 transition-colors hover:bg-white" 
                               placeholder="Deine Analyse ist bereit">
                    </div>

                    <div class="group relative">
                        <span class="absolute -left-24 top-2 text-[10px] text-blue-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">2. Unter-Text</span>
                        <textarea name="analysis_text" rows="2" 
                                  class="w-full text-center text-gray-600 border-transparent border-b border-dashed border-b-gray-300 bg-transparent focus:border-gold focus:ring-0 resize-none placeholder-gray-300 transition-colors hover:bg-white"
                                  placeholder="Hier ist der erste Blick auf dein Nervensystem-Profil.">{{ old('analysis_text', $quiz->analysis_text) }}</textarea>
                    </div>
                </div>

                <!-- 3. Chart Container -->
                <div class="w-full max-w-xl bg-white p-8 rounded-xl shadow-lg border border-[#d4af37]/20 relative group">
                    <span class="absolute -left-24 top-8 text-[10px] text-blue-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">3. Diagramm Bereich</span>
                    
                    <input type="text" name="analysis_graph_text" value="{{ old('analysis_graph_text', $quiz->analysis_graph_text) }}" 
                           class="w-full text-center text-xl font-serif text-anthracite border-transparent border-b border-dashed border-b-gray-200 bg-transparent focus:border-gold focus:ring-0 mb-6 placeholder-gray-300" 
                           placeholder="Diagramm Überschrift">
                    
                    <div class="aspect-[4/3] bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100">
                        <div class="text-center opacity-40">
                            <svg class="w-16 h-16 mx-auto mb-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-gray-500">Diagramm Platzhalter</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Report Box (Web & Default) -->
                <div class="w-full max-w-lg bg-[#fcfbf9] border border-[#d4af37]/30 p-8 rounded-xl relative group">
                    <span class="absolute -left-24 top-8 text-[10px] text-blue-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">4. Report Bereich</span>

                    <div class="mb-6 text-center">
                         <input type="text" name="report_title" value="{{ old('report_title', $quiz->report_title) }}" 
                           class="w-full text-center text-2xl font-serif text-[#d4af37] border-transparent border-b border-dashed border-b-[#d4af37]/20 bg-transparent focus:border-gold focus:ring-0 placeholder-gray-400" 
                           placeholder="Dein persönlicher Report">
                    </div>

                    <div class="text-center">
                        <textarea name="analysis_report_text" rows="4" 
                                  class="w-full text-center text-sm text-gray-600 border-transparent border-b border-dashed border-b-gray-200 bg-transparent focus:border-gold focus:ring-0 placeholder-gray-400"
                                  placeholder="Um deine Auswertung zu erhalten...">{{ old('analysis_report_text', $quiz->analysis_report_text) }}</textarea>
                    </div>
                </div>

                <!-- 4b. PDF Specific Overrides (NEW) -->
                <div class="w-full max-w-lg bg-[#fffdf5] border border-orange-100 p-8 rounded-xl relative group mt-8">
                    <span class="absolute -left-24 top-8 text-[10px] text-orange-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">PDF Spezial</span>
                    
                    <div class="mb-4 text-center">
                        <strong class="text-xs uppercase tracking-widest text-orange-800 block mb-2">PDF Seite 2: Titel (Optional)</strong>
                        <input type="text" name="pdf_page2_title" value="{{ old('pdf_page2_title', $quiz->pdf_page2_title) }}" 
                               class="w-full text-center text-xl font-serif text-anthracite border-transparent border-b border-dashed border-b-orange-200 bg-transparent focus:border-orange-400 focus:ring-0 placeholder-orange-200"
                               placeholder="Überschreibt '{{ $quiz->report_title ?? 'Report Titel' }}'">
                    </div>

                    <div class="text-center">
                        <strong class="text-xs uppercase tracking-widest text-orange-800 block mb-2">PDF Seite 2: Text (Optional)</strong>
                        <textarea name="pdf_page2_text" rows="4" 
                                  class="w-full text-center text-sm text-gray-600 border-transparent border-b border-dashed border-b-orange-200 bg-transparent focus:border-orange-400 focus:ring-0 placeholder-orange-200"
                                  placeholder="Überschreibt den Web-Report Text oben, falls ausgefüllt.">{{ old('pdf_page2_text', $quiz->pdf_page2_text) }}</textarea>
                        
                        <div class="mt-4 text-[10px] text-orange-500 text-left">
                            <strong>Info:</strong> Dieser Text erscheint exklusiv auf PDF Seite 2. Du kannst das Layout komplett frei gestalten.
                        </div>
                    </div>
                </div>

                <!-- 5. Email Definition -->
                <div class="w-full max-w-lg bg-[#f0f9ff] border border-blue-200 p-8 rounded-xl relative group mt-8">
                    <span class="absolute -left-24 top-8 text-[10px] text-blue-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">5. E-Mail Inhalt</span>

                    <div class="text-center">
                        <span class="block text-xs uppercase tracking-widest text-blue-800 mb-4 font-bold">E-Mail an Teilnehmer</span>
                        
                        <!-- Subject -->
                        <div class="mb-4 text-left">
                            <label class="text-[10px] text-blue-400 uppercase font-bold">Betreffzeile</label>
                            <input type="text" name="email_subject" value="{{ old('email_subject', $quiz->email_subject) }}" 
                                   class="w-full text-sm border-blue-100 rounded focus:border-blue-400 focus:ring-blue-400 placeholder-blue-300"
                                   placeholder="Dein persönliches Nervensystem-Profil (Standard)">
                        </div>

                        <!-- Body -->
                        <textarea name="email_body" rows="6" 
                                  class="w-full text-sm text-gray-700 border border-blue-100 rounded-lg p-3 focus:border-blue-400 focus:ring-blue-400 placeholder-blue-300 bg-white"
                                  placeholder="Hallo, vielen Dank... (Standard Text wird verwendet wenn leer)">{{ old('email_body', $quiz->email_body) }}</textarea>
                        
                        <div class="mt-4 text-left text-[10px] text-blue-500">
                            Tipp: Nutze <code>@{{url}}</code> für den Download-Link und <code>@{{best-category}}</code> für eine persönliche Ansprache.
                        </div>
                    </div>
                </div>
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
    <!-- --- CATEGORY MANAGER (COLUMNS) --- -->
    <div class="mt-16 border-t pt-12">
        <h2 class="text-2xl font-serif text-anthracite mb-6">Kategorien (Säulen) Verwalten</h2>
        
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Logic for Existing Categories -->
                @foreach($quiz->categories as $cat)
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <!-- Category Fields (Integrated into Main Form) -->
                    <div class="mb-3">
                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Label (Sichtbar)</label>
                        <input type="text" name="categories[{{ $cat->id }}][label]" value="{{ $cat->label }}" class="w-full text-sm border-gray-200 rounded px-2 py-1">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Key (Intern)</label>
                        <input type="text" name="categories[{{ $cat->id }}][key]" value="{{ $cat->key }}" class="w-full text-xs font-mono bg-gray-50 border-gray-200 rounded px-2 py-1">
                    </div>

                    <div class="mb-3">
                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Beschreibung Positiv (Best Case)</label>
                        <textarea name="categories[{{ $cat->id }}][description_positive]" rows="2" class="w-full text-xs border-gray-200 rounded px-2 py-1 placeholder-gray-300" placeholder="Wird angezeigt wenn diese Kategorie am stärksten ist...">{{ $cat->description_positive }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="block text-[10px] uppercase text-gray-400 font-bold mb-1">Beschreibung Negativ (Worst Case)</label>
                        <textarea name="categories[{{ $cat->id }}][description_negative]" rows="2" class="w-full text-xs border-gray-200 rounded px-2 py-1 placeholder-gray-300" placeholder="Wird angezeigt wenn diese Kategorie am schwächsten ist...">{{ $cat->description_negative }}</textarea>
                    </div>
                    
                    <!-- Hidden generic description fallback if needed -->
                    <input type="hidden" name="categories[{{ $cat->id }}][description]" value="{{ $cat->description }}">

                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-50">
                        <div class="text-[10px] text-gray-400 italic">Wird mit "Speichern" aktualisiert</div>
                        
                        <!-- Delete Checkbox -->
                        <label class="cursor-pointer group flex items-center space-x-2">
                             <input type="checkbox" name="categories[{{ $cat->id }}][delete]" value="1" class="text-red-600 focus:ring-red-500 w-4 h-4 cursor-pointer">
                             <span class="text-[10px] text-red-400 group-hover:text-red-600 font-bold uppercase tracking-widest">Löschen</span>
                        </label>
                    </div>
                </div>
                @endforeach

                <!-- CREATE NEW -->
                <div class="bg-blue-50 p-4 rounded-lg border-2 border-dashed border-blue-200 flex flex-col justify-center">
                    <h3 class="text-blue-800 font-bold mb-3 text-sm">Neue Kategorie</h3>
                    
                    <input type="text" name="new_categories[0][label]" placeholder="Label (z.B. Klarheit)" class="w-full text-sm border-blue-200 rounded px-2 py-1 mb-2 placeholder-blue-300">
                    <input type="text" name="new_categories[0][key]" placeholder="Key (z.B. klarheit)" class="w-full text-xs font-mono border-blue-200 rounded px-2 py-1 mb-3 placeholder-blue-300">
                    
                    <div class="text-center text-xs text-blue-400 italic mt-2">
                        Wird beim Klick auf "Speichern" <br> oben rechts angelegt.
                    </div>
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mt-6">
                <strong>Hinweis:</strong> Wenn du eine neue Kategorie hinzufügst, erscheint sie oben in der Matrix als neue Spalte. 
                Du musst dann oben die Gewichte für die Fragen eintragen (Standard ist 0).
            </p>
        </div>
    </div>
    <!-- END FORM Closing tag was at line 207, now moved to end of page -->
    </form>   
</div>
@endsection
