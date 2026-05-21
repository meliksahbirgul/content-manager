<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli Girişi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-orange-100 flex items-center justify-center min-h-screen">

    <!-- Login Modal -->
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md transform transition-all">

        <!-- Logo veya Başlık -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">{{ __('panel/login.welcome') }}</h2>
            <p class="text-gray-500 mt-2">{{ __('panel/login.login_label') }}</p>
        </div>

        <!-- Form -->
        <form action="/login" method="POST">
            @csrf
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Email -->
            <div class="mb-5">
                <label for="email"
                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('panel/login.email') }}</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none transition duration-200"
                    placeholder="ornek@mail.com">
            </div>

            <!-- Şifre -->
            <div class="mb-6">
                <label for="password"
                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('panel/login.password') }}</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none transition duration-200"
                    placeholder="••••••••">
            </div>

            <!-- Giriş Yap Butonu -->
            <button type="submit"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 rounded-lg shadow-md hover:shadow-lg transition duration-300 transform active:scale-95">
                {{ __('panel/login.login') }}
            </button>
        </form>

        <!-- Alt Linkler -->
        <div class="mt-6 text-center">
            <a href="#"
                class="text-sm text-orange-600 hover:underline">{{ __('panel/login.forgot_password') }}</a>
        </div>
    </div>

</body>

</html>
