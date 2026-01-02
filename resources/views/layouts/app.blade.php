<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nervensystem-Kompass | Sophie Philipp</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#F9F7F2',
                        gold: '#C5A065',
                        anthracite: '#2D2D2D',
                        softgray: '#E5E5E5'
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'serif'],
                        sans: ['"Montserrat"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F9F7F2;
            color: #2D2D2D;
        }
        /* Custom Range Slider Styling */
        input[type=range] {
            -webkit-appearance: none; 
            background: transparent; 
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 24px;
            width: 24px;
            border-radius: 50%;
            background: #C5A065;
            cursor: pointer;
            margin-top: -8px; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 8px;
            cursor: pointer;
            background: #E5E5E5;
            border-radius: 4px;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">
    <header class="py-12 text-center">
        <img src="{{ asset('Logo_Final_Solo.png') }}" class="h-32 mx-auto mb-6 w-auto" alt="Sophie Philipp Logo">
        <h1 class="font-serif text-3xl text-gold tracking-widest uppercase">Nervensystem Kompass</h1>
        <p class="text-sm text-anthracite mt-2 uppercase tracking-widest">by Sophie Philipp</p>
    </header>

    <main class="flex-grow container mx-auto px-4">
        @yield('content')
    </main>

    <footer class="py-6 text-center text-xs text-gray-400 mt-12">
        &copy; {{ date('Y') }} Sophie Philipp. Alle Rechte vorbehalten. | <a href="#" class="underline">Impressum</a> | <a href="#" class="underline">Datenschutz</a>
    </footer>
</body>
</html>
