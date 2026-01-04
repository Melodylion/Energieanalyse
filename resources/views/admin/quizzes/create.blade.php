@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-serif text-anthracite">Neues Quiz erstellen</h1>
        <a href="{{ route('admin.quizzes.index') }}" class="text-gray-400 hover:text-gold uppercase text-xs tracking-widest font-bold">&larr; Zurück</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.quizzes.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Titel</label>
                <input type="text" name="title" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
            </div>

            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Slug (URL)</label>
                <input type="text" name="slug" placeholder="z.b. mein-quiz" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
            </div>

            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Beschreibung (Intern)</label>
                <textarea name="description" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2"></textarea>
            </div>
            
            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Einleitungstext (Optional)</label>
                <textarea name="intro_text" rows="3" placeholder="Wird vor der ersten Frage angezeigt..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-anthracite text-white px-8 py-3 rounded hover:bg-gold transition-colors font-bold uppercase text-xs tracking-widest">
                    Erstellen & Matrix Öffnen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
