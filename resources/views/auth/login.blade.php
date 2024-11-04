<x-guest-layout>
    <div class="min-h-screen bg-gray-100"> <!-- Changed bg-white-100 to bg-gray-100 for better contrast -->
        <x-authentication-card>
            <x-slot name="logo">
               
            </x-slot>
   
            <x-validation-errors class="mb-4" />
   
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600"> <!-- Removed dark:text-green-400 -->
                    {{ session('status') }}
                </div>
            @endif
   
            <form method="POST" action="{{ route('login') }}">
                @csrf
   
                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                </div>
   
                <div class="mt-4">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                </div>
   
                <div class="block mt-4">
                    <label for="remember_me" class="flex items-center">
                        <x-checkbox id="remember_me" name="remember" />
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span> <!-- Removed dark:text-gray-400 -->
                    </label>
                </div>
   
                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
                        {{ __('Belum Daftar?') }}
                    </a>
   
                    <x-button class="ms-4">
                        {{ __('Log in') }}
                    </x-button>
                </div>
            </form>
        </x-authentication-card>
    </div>
</x-guest-layout>