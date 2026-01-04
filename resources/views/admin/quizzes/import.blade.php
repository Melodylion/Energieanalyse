@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-serif text-anthracite">Quiz Importieren</h1>
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm font-bold uppercase tracking-widest text-gray-400 hover:text-gold">&larr; Zurück</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Titel des neuen Quiz</label>
                <input type="text" name="title" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
            </div>

            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Slug (URL)</label>
                <input type="text" name="slug" placeholder="z.b. mein-neues-quiz" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
            </div>

            <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">CSV Datei wählen</label>
                <input type="file" name="csv_file" accept=".csv, .txt" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-anthracite file:text-white hover:file:bg-gold transition-colors">
                <p class="text-xs text-gray-400 mt-2">Format: Spalten = Kategorien, Zeilen = Fragen. Trennzeichen: Semikolon (;) oder Komma (,).</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-anthracite text-white px-8 py-3 rounded hover:bg-gold transition-colors font-bold uppercase text-xs tracking-widest">
                    Import Starten
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
