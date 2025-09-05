<x-filament-widgets::widget>
    <x-filament::card>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($stats as $stat)
                @php
                    $colors = [
                        'primary' => 'from-red-700 via-red-600 to-red-800',
                        'warning' => 'from-gray-600 via-gray-500 to-gray-700',
                        'info'    => 'from-black via-gray-900 to-gray-800',
                        'success' => 'from-gray-300 via-gray-200 to-gray-400',
                    ];
                    $gradient = $colors[$stat['color']] ?? 'from-gray-700 to-gray-900';
                    $id = \Illuminate\Support\Str::slug($stat['label']); // ID dinámico
                @endphp

                <div 
                    class="relative flex flex-col items-center justify-center text-center
                           rounded-xl shadow-lg overflow-hidden
                           bg-gradient-to-br {{ $gradient }}
                           transform transition duration-500 hover:scale-105 hover:-translate-y-1 hover:shadow-2xl animate-fade-in-up"
                >
                    <!-- Overlay elegante al hacer hover -->
                    <div class="absolute inset-0 bg-white/5 opacity-0 hover:opacity-10 transition-opacity"></div>

                    <!-- Ícono -->
                    <div class="mt-6 w-14 h-14 flex items-center justify-center rounded-full 
                                bg-white/10 text-white shadow-inner shadow-red-900/40
                                animate-pulse-slow">
                        <x-filament::icon :name="$stat['icon']" size="xl" class="drop-shadow-md" />
                    </div>

                    <!-- Valor con animación CountUp -->
                    <div 
                        id="{{ $id }}" 
                        class="counter mt-4 text-4xl font-extrabold text-white drop-shadow-lg tracking-wide"
                    >
                        {{ $stat['value'] }}
                    </div>

                    <!-- Label -->
                    <div class="mt-2 text-base font-semibold text-gray-100 uppercase tracking-wide">
                        {{ $stat['label'] }}
                    </div>

                    <!-- Descripción -->
                    <div class="mt-1 mb-6 text-xs text-gray-300 italic">
                        {{ $stat['description'] }}
                    </div>

                    <!-- Línea animada abajo -->
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-white/10">
                        <div 
                            class="h-full bg-red-600 animate-[grow-bar_2s_ease-in-out]"
                            style="width: {{ min(100, ($stat['value'] * 20)) }}%">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
