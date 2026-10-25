<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sudah Diisi - SI-BK Digital</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gradient-to-br from-amber-50 to-purple-50 min-h-screen flex items-center justify-center px-4">
<div class="bg-white rounded-xl shadow-lg p-8 text-center max-w-sm">
    <div class="text-4xl mb-3">⚠️</div>
    <h1 class="text-lg font-bold text-amber-700 mb-1">Sudah pernah diisi</h1>
    <p class="text-sm text-slate-500">{{ $siswa->nama ?? 'Kamu' }} sudah mengisi asesmen ini sebelumnya. Satu siswa hanya bisa isi 1 kali.</p>
</div>
</body></html>
