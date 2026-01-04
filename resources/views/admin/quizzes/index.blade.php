@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-serif text-anthracite">Quiz Verwaltung</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gold">Zurück zum Dashboard</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug (URL)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fragen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($quizzes as $quiz)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $quiz->title }}</div>
                            <div class="text-xs text-gray-500">{{ Str::limit($quiz->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">/quiz/{{ $quiz->slug }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $quiz->questions_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $quiz->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $quiz->active ? 'Aktiv' : 'Inaktiv' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-3 items-center">
                            <!-- CSV Export Options -->
                            <div class="flex flex-col gap-1 text-left">
                                <a href="{{ route('admin.export', ['quiz' => $quiz, 'separator' => ';']) }}" class="text-[10px] text-gray-400 hover:text-gold uppercase tracking-wider" title="Export für Excel (Deutsche Region, Semikolon)">
                                    CSV (;)
                                </a>
                                <a href="{{ route('admin.export', ['quiz' => $quiz, 'separator' => ',']) }}" class="text-[10px] text-gray-400 hover:text-gold uppercase tracking-wider" title="Export International (Komma)">
                                    CSV (,)
                                </a>
                            </div>
                            <div class="h-8 w-px bg-gray-200 mx-1"></div>
                            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-gold hover:text-anthracite font-bold uppercase tracking-wider text-xs">Bearbeiten</a>
                            
                            <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('ACHTUNG: Quiz & ALLE Ergebnisse löschen?');" class="ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-900 text-xs uppercase tracking-wider">Löschen</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 text-right flex justify-end gap-4">
        <a href="{{ route('admin.import.form') }}" class="bg-white border border-gray-300 text-gray-600 px-6 py-3 rounded-full hover:border-gold hover:text-gold transition-colors text-sm uppercase tracking-widest">
            Import (CSV)
        </a>
        <a href="{{ route('admin.quizzes.create') }}" class="bg-anthracite text-white px-6 py-3 rounded-full hover:bg-gold transition-colors text-sm uppercase tracking-widest">
            + Neues Quiz
        </a>
    </div>
</div>
@endsection
