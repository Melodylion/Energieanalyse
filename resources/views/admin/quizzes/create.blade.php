@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12">
    <div class="mb-8">
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-500 hover:text-gold">&larr; Zurück zur Übersicht</a>
        <h1 class="text-3xl font-serif text-anthracite mt-2">Neues Quiz erstellen</h1>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form action="{{ route('admin.quizzes.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Titel (Intern)</label>
                <input type="text" name="title" required class="w-full border-gray-200 rounded-lg focus:border-gold focus:ring-gold">
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Slug (URL)</label>
                <div class="flex items-center">
                    <span class="text-gray-400 text-sm mr-2">/quiz/</span>
                    <input type="text" name="slug" required placeholder="z.b. neu-2026" class="w-full border-gray-200 rounded-lg focus:border-gold focus:ring-gold">
                </div>
                <p class="text-xs text-gray-400 mt-1">Keine Leerzeichen, nur Kleinbuchstaben und Bindestriche.</p>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Beschreibung</label>
                <textarea name="description" rows="3" class="w-full border-gray-200 rounded-lg focus:border-gold focus:ring-gold"></textarea>
            </div>

            <button type="submit" class="w-full bg-anthracite text-white px-6 py-3 rounded-full hover:bg-gold transition-colors text-sm uppercase tracking-widest">
                Erstellen & Matrix öffnen
            </button>
        </form>
    </div>
</div>
@endsection
