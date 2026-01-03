@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-serif text-anthracite">Administratoren Verwalten</h1>
        <div class="flex gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-anthracite hover:text-gold transition-colors font-bold uppercase text-xs tracking-widest flex items-center">
                <span class="mr-2">&larr;</span> Zurück zum Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded text-sm font-bold mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded text-sm font-bold mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="text-lg font-bold text-anthracite mb-6 uppercase text-xs tracking-widest">Neuen Admin anlegen</h2>
        <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Name</label>
                <input type="text" name="name" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">E-Mail</label>
                <input type="email" name="email" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Passwort</label>
                <input type="password" name="password" placeholder="min. 8 Zeichen" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Bestätigung</label>
                <input type="password" name="password_confirmation" placeholder="Wiederholen" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-20 px-3 py-2">
            </div>
            <button type="submit" class="w-full bg-anthracite text-white px-4 py-2 rounded hover:bg-gold transition-colors font-bold uppercase text-xs tracking-widest h-[42px]">
                Speichern
            </button>
        </form>
        @if ($errors->any())
            <div class="mt-4 text-red-600 text-xs">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-Mail</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aktion</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-3 items-center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-gold hover:text-anthracite font-bold mr-2">Bearbeiten</a>
                            
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Sicher?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-900 font-bold">Löschen</button>
                                </form>
                            @else
                                <span class="text-gray-300 text-xs">(Du selbst)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
