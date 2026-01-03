@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-serif text-anthracite mb-8">Admin Dashboard</h1>

    <!-- Actions Toolbar -->
    <div class="mb-8 flex justify-between items-center">
        <div class="flex gap-4">
            <a href="{{ route('admin.quizzes.index') }}" class="bg-anthracite text-white px-6 py-2 rounded-lg hover:bg-gold transition-colors text-sm uppercase tracking-widest">
                CMS / Quiz
            </a>
            <a href="{{ route('admin.users.index') }}" class="bg-white border border-gray-300 text-anthracite px-4 py-2 rounded-lg hover:border-gold hover:text-gold transition-colors text-sm uppercase tracking-widest">
                Admins
            </a>
            <a href="{{ route('admin.clear-cache') }}" class="bg-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-red-100 transition-colors text-xs font-bold uppercase tracking-wide flex items-center">
                Cache Leeren
            </a>
        </div>
        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded text-sm font-bold animate-pulse">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expand</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-Mail</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marketing</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontakt?</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiefster Wert</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aktion</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($respondents as $respondent)
                    @foreach($respondent->assessments as $assessment)
                        <tbody x-data="{ open: false }" class="border-b border-gray-100">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <button @click="open = !open" class="text-gray-500 hover:text-gold focus:outline-none">
                                        <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $assessment->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $respondent->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($respondent->marketing_opt_in) <span class="text-green-600 font-bold">JA</span> @else <span class="text-gray-400">Nein</span> @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($respondent->contact_request) 
                                        <span class="bg-gold text-white px-2 py-1 rounded text-xs font-bold uppercase tracking-wide">Ja bitte</span> 
                                    @else 
                                        <span class="text-gray-400">-</span> 
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gold text-white">
                                        {{ $assessment->lowest_category_score }}
                                    </span>
                                    <span class="ml-2 font-medium text-anthracite">
                                        {{ ucfirst(str_replace('_', ' ', $assessment->lowest_category_key)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form method="POST" action="{{ route('admin.respondents.destroy', $respondent) }}" onsubmit="return confirm('Wirklich löschen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-900 font-bold hover:underline">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                            
                            <!-- Detail Row -->
                            <tr x-show="open" x-transition class="bg-gray-50">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <h4 class="font-bold text-anthracite mb-2 uppercase text-xs tracking-widest">Antworten im Detail</h4>
                                            <ul class="space-y-1">
                                                @if(is_array($assessment->answers))
                                                    @foreach($assessment->answers as $index => $value)
                                                        <li class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-600 truncate w-3/4" title="{{ $questions[$index] ?? 'Frage '.($index+1) }}">
                                                                <span class="text-xs text-gold font-bold mr-1">{{ $index + 1 }}.</span>
                                                                {{ $questions[$index] ?? 'Frage '.($index+1) }}
                                                            </span>
                                                            <span class="font-bold text-anthracite">{{ $value }}</span>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li class="text-gray-400 italic">Keine Details gespeichert (Alte Daten).</li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-anthracite mb-2 uppercase text-xs tracking-widest">Scores (Kategorien)</h4>
                                            <ul class="space-y-1">
                                                @if(is_array($assessment->scores))
                                                    @foreach($assessment->scores as $cat => $score)
                                                        <li class="flex justify-between border-b border-gray-200 pb-1">
                                                            <span class="text-gray-600">{{ ucfirst($cat) }}</span>
                                                            <span class="font-bold text-gold">{{ number_format($score, 1) }}</span>
                                                        </li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
