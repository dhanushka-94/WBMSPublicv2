<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-semibold" />
            <x-text-input id="email" 
                          class="block mt-2 w-full rounded-lg border-gray-300 bg-white/50 backdrop-blur-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all duration-200" 
                          type="email" 
                          name="email" 
                          :value="old('email')" 
                          required 
                          autofocus 
                          autocomplete="username"
                          placeholder="Enter your email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-semibold" />
            <x-text-input id="password" 
                          class="block mt-2 w-full rounded-lg border-gray-300 bg-white/50 backdrop-blur-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all duration-200"
                          type="password"
                          name="password"
                          required 
                          autocomplete="current-password"
                          placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" 
                       type="checkbox" 
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 transition-colors" 
                       name="remember">
                <span class="ms-3 text-sm text-gray-600 font-medium">{{ __('Remember me for 30 days') }}</span>
            </label>
        </div>

        <!-- Login Button and Forgot Password -->
        <div class="space-y-4">
            <x-primary-button class="w-full justify-center py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl rounded-lg font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                {{ __('Sign In to Dashboard') }}
            </x-primary-button>

            @if (Route::has('password.request'))
                <div class="text-center">
                    <a class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
                       href="{{ route('password.request') }}">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-11.639 2.755C5.684 11.061 4 9.165 4 7a6 6 0 1112 0z"></path>
                        </svg>
                        {{ __('Forgot your password?') }}
                    </a>
                </div>
            @endif
        </div>
    </form>
</x-guest-layout>
