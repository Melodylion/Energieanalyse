@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-serif text-anthracite">Admin Bearbeiten</h1>
        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-gold">
           &larr; Zurück
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
            </div>

            <div class="mb-6">
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">E-Mail</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
            </div>

            <div class="mb-6 pt-6 border-t border-gray-100">
                <h3 class="text-sm font-bold text-anthracite mb-4">Passwort Ändern (Optional)</h3>
                <p class="text-xs text-gray-400 mb-4">Lasse diese Felder leer, wenn du das Passwort behalten willst.</p>
                
                <div class="space-y-4">
                    <input type="password" name="password" placeholder="Neues Passwort" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
                    <input type="password" name="password_confirmation" placeholder="Bestätigen" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-4 py-2">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-anthracite text-white px-8 py-3 rounded hover:bg-gold transition-colors font-bold uppercase text-xs tracking-widest">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
