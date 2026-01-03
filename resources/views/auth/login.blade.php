@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-20">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
        <h1 class="font-serif text-2xl text-anthracite mb-6 text-center">Admin Login</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs uppercase tracking-widest text-gray-500 mb-1">E-Mail Adresse</label>
                <input id="email" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-gold focus:ring-1 focus:ring-gold outline-none transition-all" 
                       type="email" name="email" value="{{ old('email') }}" required autofocus />
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Passwort</label>
                <input id="password" class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-gold focus:ring-1 focus:ring-gold outline-none transition-all" 
                       type="password" name="password" required />
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full bg-anthracite text-white px-8 py-3 rounded-full hover:bg-gold transition-colors duration-300 text-sm tracking-widest uppercase shadow-lg">
                Anmelden
            </button>
        </form>
    </div>
</div>
@endsection
