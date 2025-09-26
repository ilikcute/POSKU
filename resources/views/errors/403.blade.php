<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 h-screen flex items-center justify-center">
    <div class="bg-gray-800 p-8 rounded-lg shadow-lg max-w-md mx-auto text-center border border-gray-700">
        <div class="mb-6">
            <div class="text-6xl mb-4">🚫</div>
            <h1 class="text-4xl font-bold text-white mb-2">403</h1>
            <h2 class="text-xl font-semibold text-gray-300 mb-4">Akses Ditolak</h2>
            <p class="text-gray-400 mb-6">
                {{ $exception->getMessage() ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}</p>
        </div>
        <div class="flex flex-col space-y-3">
            <a href="{{ route('dashboard') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 shadow-md">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>

</html>
