<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - SI-BK Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-rose-50 to-pink-50 min-h-screen flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-sm">
        <div class="text-center mb-6">
            <div class="text-4xl mb-2">🧭</div>
            <h1 class="text-xl font-bold text-rose-700">SI-BK Digital</h1>
            <p class="text-sm text-slate-500">Sistem Administrasi Digital Terpadu Guru BK</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>
            <button type="submit"
                class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 rounded transition">
                Masuk
            </button>
        </form>

        <p class="text-xs text-slate-400 text-center mt-6">
            Akun demo tersedia setelah <code>php artisan db:seed</code>
        </p>
    </div>
</body>
</html>
