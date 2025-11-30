@props(['label', 'key'])

<div class="mb-5 last:mb-0">
    <div class="flex justify-between items-center mb-1">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
        <span class="text-[10px] font-mono text-gray-400" 
              x-text="'RGB: ' + $store.theme.hexToRgb($store.theme.settings[activeTab]['{{ $key }}'])">
        </span>
    </div>
    
    <div class="flex items-center gap-3">
        <div class="relative w-12 h-10 flex-shrink-0 overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
            <input type="color" 
                   x-model="$store.theme.settings[activeTab]['{{ $key }}']"
                   class="absolute -top-2 -left-2 w-20 h-20 cursor-pointer p-0 border-0">
        </div>

        <input type="text" 
               x-model="$store.theme.settings[activeTab]['{{ $key }}']"
               class="w-24 px-2 py-2 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md focus:ring-blue-500 focus:border-blue-500 uppercase"
               maxlength="7">

        <div class="flex-1 flex flex-col justify-center">
            <div class="flex justify-between text-[10px] text-gray-400 mb-1">
                <span>Opacity</span>
                <span x-text="$store.theme.settings[activeTab]['{{ $key }}Opacity'] + '%'"></span>
            </div>
            <input type="range" min="1" max="100" step="1"
                   x-model="$store.theme.settings[activeTab]['{{ $key }}Opacity']"
                   class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-blue-600">
        </div>
    </div>
</div>