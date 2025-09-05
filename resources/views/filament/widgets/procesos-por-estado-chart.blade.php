<x-filament-widgets::widget>
    <x-filament::card class="animate-fade-in transition-all duration-500 ease-in-out bg-white rounded-lg shadow-md p-4 hover:shadow-lg">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ $this->getHeading() }}</h3>
        <div x-data="{ chartData: @js($this->getData()) }" x-init="new Chart($refs.canvas, {
            type: '{{ $this->getType() }}',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuad'
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        })">
            <canvas x-ref="canvas" class="h-64 w-full"></canvas>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>