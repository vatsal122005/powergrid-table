<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script async src="https://basicons.xyz/embed.js"> </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Scripts -->
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <livewire:layout.navigation />

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.22.3/sweetalert2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Bind Livewire events globally

        // Confirm dialog
        Livewire.on('confirm', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;

            Swal.fire({
                title: data.title || 'Are you sure?',
                text: data.message || 'Do you want to proceed?',
                icon: data.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: data.confirmButtonText || 'Yes',
                cancelButtonText: data.cancelButtonText || 'Cancel',
                customClass: {
                    popup: 'rounded-lg shadow-lg bg-white dark:bg-gray-800',
                    title: 'font-semibold text-lg text-gray-800 dark:text-gray-100',
                    confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded',
                    cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch(data.confirmEvent, data.confirmPayload || {});
                }
            });
        });

        // Delete completed notification
        Livewire.on('deleteCompleted', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;

            Swal.fire({
                title: data.success ? 'Deleted!' : 'Error!',
                text: data.message,
                icon: data.success ? 'success' : 'error',
                timer: 2500,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-lg shadow-lg bg-white dark:bg-gray-800',
                    title: 'font-semibold text-lg text-gray-800 dark:text-gray-100',
                },
                buttonsStyling: false,
                backdrop: true,
            });
        });
    });
</script>

</html>