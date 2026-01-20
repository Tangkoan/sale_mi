@extends('admin.dashboard')

@section('content')
<div class="w-full h-full px-6 py-5">
    
    <div class="w-full bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        
        <div class="px-8 py-6 border-b border-border-color">
            <h2 class="text-xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-lock-password-line text-primary text-2xl"></i> 
                {{ __('messages.change_password') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 p-8">

            <div class="lg:col-span-1 border-r border-input-border pr-8 hidden lg:block">
                <div class="flex flex-col items-center text-center p-6 bg-page-bg/50 rounded-2xl border border-input-border border-dashed">
                    <div class="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                        <i class="ri-shield-keyhole-line text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-bold text-text-color mb-2">{{ __('messages.secure_account') }}</h3>
                    <p class="text-sm text-secondary mb-6 leading-relaxed">
                        {{ __('messages.secure_account_desc') }}
                    </p>
                    
                    <div class="text-left w-full space-y-3">
                        <p class="text-xs font-bold text-text-color uppercase tracking-wider">{{ __('messages.password_requirements') }}:</p>
                        <ul class="text-sm text-secondary space-y-2">
                            <li class="flex items-center gap-2">
                                <i class="ri-checkbox-circle-fill text-green-500"></i> {{ __('messages.req_min_chars') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="ri-checkbox-circle-fill text-green-500"></i> {{ __('messages.req_special_char') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="ri-checkbox-circle-fill text-green-500"></i> {{ __('messages.req_number') }}
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="ri-checkbox-circle-fill text-green-500"></i> {{ __('messages.req_not_same') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 flex flex-col justify-center">

                <form action="{{ route('admin.password.update') }}" method="POST" 
                      x-data="{ isLoading: false }" 
                      @submit="isLoading = true">
                    
                    @csrf
                    @method('PUT')

                    <div class="space-y-6 max-w-lg">
                        
                        <div x-data="{ show: false }">
                            <label class="block text-sm font-medium text-text-color mb-2">{{ __('messages.current_password') }}</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-secondary">
                                    <i class="ri-key-2-line"></i>
                                </span>
                                
                                <input :type="show ? 'text' : 'password'" name="current_password" 
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary"
                                       placeholder="{{ __('messages.placeholder_current_password') }}">

                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-secondary hover:text-text-color cursor-pointer transition-colors focus:outline-none">
                                    <i :class="show ? 'ri-eye-line' : 'ri-eye-off-line'"></i>
                                </button>
                            </div>
                            @error('current_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div x-data="{ show: false }">
                            <label class="block text-sm font-medium text-text-color mb-2">{{ __('messages.new_password') }}</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-secondary">
                                    <i class="ri-lock-line"></i>
                                </span>
                                
                                <input :type="show ? 'text' : 'password'" name="password" 
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary"
                                       placeholder="{{ __('messages.placeholder_new_password') }}">

                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-secondary hover:text-text-color cursor-pointer transition-colors focus:outline-none">
                                    <i :class="show ? 'ri-eye-line' : 'ri-eye-off-line'"></i>
                                </button>
                            </div>
                            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div x-data="{ show: false }">
                            <label class="block text-sm font-medium text-text-color mb-2">{{ __('messages.confirm_new_password') }}</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-secondary">
                                    <i class="ri-lock-check-line"></i>
                                </span>
                                
                                <input :type="show ? 'text' : 'password'" name="password_confirmation" 
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary"
                                       placeholder="{{ __('messages.placeholder_retype_password') }}">

                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-secondary hover:text-text-color cursor-pointer transition-colors focus:outline-none">
                                    <i :class="show ? 'ri-eye-line' : 'ri-eye-off-line'"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pt-6 flex items-center gap-4 border-t border-input-border mt-4">
                            <button type="submit" 
                                    class="bg-primary hover:opacity-90 text-white font-medium py-2.5 px-8 rounded-lg transition-all flex items-center gap-2 shadow-lg disabled:opacity-70 disabled:cursor-not-allowed"
                                    :disabled="isLoading">
                                <i class="ri-key-2-line text-lg" x-show="!isLoading"></i>
                                <i class="ri-loader-4-line text-lg animate-spin" x-show="isLoading" style="display: none;"></i>
                                <span x-text="isLoading ? '{{ __('messages.updating') }}' : '{{ __('messages.update_password') }}'"></span>
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection