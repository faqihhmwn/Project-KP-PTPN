<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Profil') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-white dark:bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-10">

            {{-- Informasi Profil --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b pb-2">
                    📄 Informasi Profil
                </h3>
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Ubah Password --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b pb-2">
                    🔐 Ubah Password
                </h3>
                @include('profile.partials.update-password-form')
            </div>

            {{-- Hapus Akun --}}
            <div class="bg-red-50 dark:bg-red-900 border border-red-300 dark:border-red-700 rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-red-700 dark:text-red-200 mb-4 border-b pb-2">
                    ⚠️ Hapus Akun
                </h3>
                @include('profile.partials.delete-user-form')
            </div>

            {{-- Kembali ke Dashboard --}}
            <div class="text-center pt-6">
                <a href="{{ route('dashboard') }}"
                   class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition-all">
                    ← Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
