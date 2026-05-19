<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-orange-50 min-h-screen">

    <!-- Navigasyon Bar (Basic) -->
    <nav class="bg-white shadow-sm border-b border-orange-100 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold text-orange-600">Yönetim Paneli</h1>
            <div class="flex items-center gap-4">
                <span class="text-gray-600 text-sm">Merhaba, <strong>{{ Auth::user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-xs bg-gray-100 hover:bg-red-50 hover:text-red-600 text-gray-500 px-3 py-1 rounded transition">
                        Çıkış Yap
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- İçerik Alanı -->
    <main class="container mx-auto mt-10 p-6">
        <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-8">
            <h2 class="text-2xl font-semibold text-gray-800">Hoş Geldiniz! 🚀</h2>
            <p class="text-gray-500 mt-2">Giriş işlemi başarıyla tamamlandı ve Session oluşturuldu.</p>

            <div class="mt-6 p-4 bg-orange-100/50 rounded-xl border border-orange-200">
                <h3 class="text-sm font-bold text-orange-800 uppercase tracking-wider">Oturum Bilgileri:</h3>
                <pre class="mt-2 text-xs text-orange-900 bg-white/50 p-3 rounded">
User ID: {{ Auth::id() }}
E-posta: {{ Auth::user()->email }}
Guard: Web (Session)
                </pre>
            </div>
        </div>
    </main>

</body>

</html>
