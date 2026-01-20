@extends('admin.dashboard')
@section('content')

<div class="w-full h-full px-6 py-5">
    
    <div class="w-full bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        
        <div class="px-8 py-6 border-b border-border-color">
            <h2 class="text-xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-user-settings-line text-primary text-2xl"></i> 
                {{ __('messages.user_information') }}
            </h2>
        </div>

        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" 
              class="p-8"
              x-data="{ isLoading: false }" 
              @submit="isLoading = true">
              
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                <div class="lg:col-span-1 flex flex-col items-center">
                    <div x-data="{ photoName: null, photoPreview: null }" class="text-center w-full flex flex-col items-center">
                        
                        <input type="file" class="hidden" x-ref="photo" name="avatar"
                               x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => { photoPreview = e.target.result; };
                                    reader.readAsDataURL($refs.photo.files[0]);
                               ">

                        <label class="block text-sm font-bold text-text-color mb-4">{{ __('messages.profile_picture') }}</label>

                        <div class="relative mx-auto w-40 h-40 rounded-full ring-primary ring-4 border-4 border-border-color overflow-hidden cursor-pointer shadow-md group transition-all duration-300 hover:ring-primary/50"
                                x-on:click.prevent="$refs.photo.click()">
                            
                            <div x-show="!photoPreview" class="w-full h-full">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-primary flex items-center justify-center text-white text-5xl font-bold">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <div x-show="photoPreview" style="display: none;" class="w-full h-full bg-cover bg-center"
                                    x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                            </div>
                            
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <i class="ri-camera-fill text-white text-3xl"></i>
                            </div>
                        </div>

                        <button type="button" x-on:click.prevent="$refs.photo.click()" 
                                class="mt-6 py-2 px-4 bg-input-bg border border-input-border rounded-lg shadow-sm text-sm font-medium text-text-color hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                            {{ __('messages.select_new_photo') }}
                        </button>
                        
                        @error('avatar') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6 flex flex-col justify-center">
                    
                    <div>
                        <label class="block text-sm font-medium text-text-color mb-2">{{ __('messages.username') }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-secondary">
                                <i class="ri-user-line"></i>
                            </span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary"
                                   placeholder="{{ __('messages.placeholder_username') }}">
                        </div>
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-color mb-2">{{ __('messages.email') }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-secondary">
                                <i class="ri-mail-line"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary"
                                   placeholder="{{ __('messages.placeholder_email') }}">
                        </div>
                        @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                          
                        <button type="submit" 
                                class="bg-primary hover:opacity-90 text-white font-medium py-2.5 px-8 rounded-lg transition-all flex items-center gap-2 shadow-lg shadow-blue-500/30 disabled:opacity-70 disabled:cursor-not-allowed"
                                :disabled="isLoading">
                            
                            <i class="ri-save-line text-lg" x-show="!isLoading"></i>
                            <i class="ri-loader-4-line text-lg animate-spin" x-show="isLoading" style="display: none;"></i>
                            
                            <span x-text="isLoading ? '{{ __('messages.saving') }}' : '{{ __('messages.save_changes') }}'"></span>
                        </button>

                    </div>

                </div>
            </div>
        </form>
    </div>
</div>
@endsection