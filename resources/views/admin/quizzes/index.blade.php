@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-serif text-anthracite">Quiz Verwaltung</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gold">Zurück zum Dashboard</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-3">
                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-gold hover:text-anthracite font-bold">Matrix</a>
                        
                        <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('ACHTUNG: Quiz & ALLE Ergebnisse löschen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-900">Löschen</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8 text-right">
        <!-- Optional: Create Logic later -->
        <a href="{{ route('admin.quizzes.create') }}" class="bg-anthracite text-white px-6 py-3 rounded-full hover:bg-gold transition-colors text-sm uppercase tracking-widest">
            + Neues Quiz
        </a>
    </div>
</div>
@endsection
