@props(['title', 'type' => 'line', 'data' => [], 'height' => '300'])

<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        <div class="flex space-x-2">
            <button type="button" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors">
                1M
            </button>
            <button type="button" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors">
                3M
            </button>
            <button type="button" class="text-sm bg-rose-100 text-rose-700 px-3 py-1 rounded-lg">
                6M
            </button>
        </div>
    </div>
    
    <div class="relative">
        <canvas 
            id="chart-{{ Str::random(8) }}" 
            style="height: {{ $height }}px;"
            x-data="chartComponent('{{ $type }}', @js($data))"
            x-init="initChart($el)"
        ></canvas>
    </div>
</div>

<script nonce="@cspNonce">
function chartComponent(type, data) {
    return {
        chart: null,
        initChart(canvas) {
            const ctx = canvas.getContext('2d');
            
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(236, 72, 153, 0.3)');
            gradient.addColorStop(1, 'rgba(236, 72, 153, 0.05)');
            
            this.chart = new Chart(ctx, {
                type: type,
                data: {
                    labels: data.labels || ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu'],
                    datasets: [{
                        label: data.label || 'Dataset',
                        data: data.values || [12, 19, 3, 5, 2, 3],
                        backgroundColor: type === 'line' ? gradient : [
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: 'rgba(236, 72, 153, 1)',
                        borderWidth: 2,
                        fill: type === 'line',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6B7280'
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(107, 114, 128, 0.1)'
                            },
                            ticks: {
                                color: '#6B7280'
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 6,
                            backgroundColor: '#EC4899',
                            borderColor: '#FFFFFF',
                            borderWidth: 2
                        }
                    }
                }
            });
        }
    }
}
</script>