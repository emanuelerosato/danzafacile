<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Report & Analytics
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione report della tua scuola
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Report</li>
    </x-slot>




@push('styles')
<style>
    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .metric-card.students {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .metric-card.courses {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .metric-card.payments {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .metric-card.staff {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }

    .chart-tabs button.active {
        background-color: rgb(59, 130, 246);
        color: white;
    }
</style>
@endpush

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Reports e Analytics</h1>
                <p class="text-gray-600">Dashboard completo delle performance della scuola</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <!-- Period Selector -->
                <select id="periodSelector" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Ultimi 7 giorni</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Ultime 12 settimane</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Ultimi 12 mesi</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Ultimi 5 anni</option>
                </select>

                <!-- Export Buttons -->
                <div class="flex space-x-2">
                    <button onclick="exportReport('pdf')" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </button>
                    <button onclick="exportReport('excel')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Studenti -->
        <div class="metric-card students rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm font-medium">Studenti</p>
                    <p class="text-3xl font-bold">{{ number_format($metrics['students']['total']) }}</p>
                    <p class="text-white/80 text-xs">
                        {{ $metrics['students']['new'] }} nuovi
                        <span class="text-white/60">questo periodo</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm">
                    <span>Attivi</span>
                    <span>{{ $metrics['students']['active'] }}</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2 mt-1">
                    <div class="bg-white h-2 rounded-full" style="width: {{ $metrics['students']['total'] > 0 ? ($metrics['students']['active'] / $metrics['students']['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Corsi -->
        <div class="metric-card courses rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm font-medium">Corsi</p>
                    <p class="text-3xl font-bold">{{ number_format($metrics['courses']['total']) }}</p>
                    <p class="text-white/80 text-xs">
                        {{ $metrics['courses']['active'] }} attivi
                        <span class="text-white/60">ora</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm">
                    <span>Capacità utilizzata</span>
                    <span>{{ number_format($metrics['courses']['capacity_usage'], 1) }}%</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2 mt-1">
                    <div class="bg-white h-2 rounded-full" style="width: {{ $metrics['courses']['capacity_usage'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Pagamenti -->
        <div class="metric-card payments rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm font-medium">Incassi Totali</p>
                    <p class="text-3xl font-bold">€{{ number_format($metrics['payments']['total_amount'], 0) }}</p>
                    <p class="text-white/80 text-xs">
                        €{{ number_format($metrics['payments']['this_period_amount'], 0) }}
                        <span class="text-white/60">questo periodo</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-euro-sign text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm">
                    <span>In sospeso</span>
                    <span>€{{ number_format($metrics['payments']['pending_amount'], 0) }}</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2 mt-1">
                    @php
                        $totalPending = $metrics['payments']['total_amount'] + $metrics['payments']['pending_amount'];
                        $completedPercentage = $totalPending > 0 ? ($metrics['payments']['total_amount'] / $totalPending) * 100 : 100;
                    @endphp
                    <div class="bg-white h-2 rounded-full" style="width: {{ $completedPercentage }}%"></div>
                </div>
            </div>
        </div>

        <!-- Staff -->
        <div class="metric-card staff rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm font-medium">Staff</p>
                    <p class="text-3xl font-bold">{{ number_format($metrics['staff']['total']) }}</p>
                    <p class="text-white/80 text-xs">
                        {{ $metrics['staff']['active'] }} attivi
                        <span class="text-white/60">{{ $metrics['staff']['instructors'] }} istruttori</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm">
                    <span>Istruttori</span>
                    <span>{{ $metrics['staff']['instructors'] }}</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2 mt-1">
                    <div class="bg-white h-2 rounded-full" style="width: {{ $metrics['staff']['total'] > 0 ? ($metrics['staff']['instructors'] / $metrics['staff']['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Presenze -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Presenze</h3>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check text-blue-600"></i>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Totali</span>
                    <span class="font-semibold">{{ number_format($metrics['attendance']['total']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Questo periodo</span>
                    <span class="font-semibold">{{ number_format($metrics['attendance']['this_period']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Tasso di presenza</span>
                    <span class="font-semibold text-green-600">{{ number_format($metrics['attendance']['rate'], 1) }}%</span>
                </div>
            </div>
        </div>

        <!-- Documenti -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Documenti</h3>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-purple-600"></i>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Totali</span>
                    <span class="font-semibold">{{ number_format($metrics['documents']['total']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">In attesa</span>
                    <span class="font-semibold text-yellow-600">{{ number_format($metrics['documents']['pending_approval']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Approvati</span>
                    <span class="font-semibold text-green-600">{{ number_format($metrics['documents']['approved']) }}</span>
                </div>
            </div>
        </div>

        <!-- Gallerie -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Media</h3>
                <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-images text-pink-600"></i>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Gallerie</span>
                    <span class="font-semibold">{{ number_format($metrics['galleries']['total']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">File multimediali</span>
                    <span class="font-semibold">{{ number_format($metrics['galleries']['total_media']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Media per galleria</span>
                    <span class="font-semibold">{{ $metrics['galleries']['total'] > 0 ? number_format($metrics['galleries']['total_media'] / $metrics['galleries']['total'], 1) : '0' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Grafici Analitici</h3>
            <p class="text-gray-600">Analisi dettagliata delle performance</p>
        </div>

        <!-- Chart Tabs -->
        <div class="chart-tabs mb-6">
            <div class="flex flex-wrap gap-2">
                <button onclick="switchChart('overview')" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200 active" id="tab-overview">
                    Panoramica
                </button>
                <button onclick="switchChart('students')" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200" id="tab-students">
                    Studenti
                </button>
                <button onclick="switchChart('courses')" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200" id="tab-courses">
                    Corsi
                </button>
                <button onclick="switchChart('payments')" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200" id="tab-payments">
                    Pagamenti
                </button>
                <button onclick="switchChart('attendance')" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200" id="tab-attendance">
                    Presenze
                </button>
                <button onclick="switchChart('staff')" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200" id="tab-staff">
                    Staff
                </button>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <canvas id="mainChart"></canvas>
        </div>

        <!-- Loading Indicator -->
        <div id="chartLoading" class="hidden flex items-center justify-center h-96">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<script>
let currentChart = null;
let currentChartType = 'overview';
let currentPeriod = '{{ $period }}';

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadChart('overview', currentPeriod);
});

// Period selector change
document.getElementById('periodSelector').addEventListener('change', function(e) {
    currentPeriod = e.target.value;
    window.location.href = `{{ route('admin.reports.index') }}?period=${currentPeriod}`;
});

// Switch between chart types
function switchChart(type) {
    // Update active tab
    document.querySelectorAll('.chart-tabs button').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(`tab-${type}`).classList.add('active');

    currentChartType = type;
    loadChart(type, currentPeriod);
}

// Load chart data
async function loadChart(type, period) {
    showLoading(true);

    try {
        const response = await fetch(`{{ route('admin.reports.charts-data') }}?type=${type}&period=${period}`);
        const data = await response.json();

        if (currentChart) {
            currentChart.destroy();
        }

        const ctx = document.getElementById('mainChart').getContext('2d');
        currentChart = new Chart(ctx, getChartConfig(type, data));

        showLoading(false);
    } catch (error) {
        console.error('Error loading chart data:', error);
        showLoading(false);
    }
}

// Get chart configuration based on type
function getChartConfig(type, data) {
    const baseConfig = {
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: getChartTitle(type)
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    }
                }
            }
        }
    };

    // Set chart type based on data type
    switch (type) {
        case 'overview':
        case 'students':
        case 'payments':
        case 'attendance':
            baseConfig.type = 'line';
            baseConfig.options.elements = {
                point: {
                    radius: 4,
                    hoverRadius: 6
                },
                line: {
                    tension: 0.2
                }
            };
            break;

        case 'courses':
        case 'staff':
            baseConfig.type = 'doughnut';
            baseConfig.options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: getChartTitle(type)
                    }
                }
            };
            break;

        default:
            baseConfig.type = 'bar';
    }

    return baseConfig;
}

// Get chart title
function getChartTitle(type) {
    const titles = {
        'overview': 'Panoramica Performance',
        'students': 'Andamento Studenti',
        'courses': 'Distribuzione Iscrizioni per Corso',
        'payments': 'Andamento Pagamenti',
        'attendance': 'Andamento Presenze',
        'staff': 'Distribuzione Staff per Ruolo'
    };
    return titles[type] || 'Grafico';
}

// Show/hide loading indicator
function showLoading(show) {
    const chart = document.getElementById('mainChart');
    const loading = document.getElementById('chartLoading');

    if (show) {
        chart.style.display = 'none';
        loading.classList.remove('hidden');
    } else {
        chart.style.display = 'block';
        loading.classList.add('hidden');
    }
}

// Export functions
function exportReport(format) {
    const url = format === 'pdf'
        ? `{{ route('admin.reports.export-pdf') }}?period=${currentPeriod}`
        : `{{ route('admin.reports.export-excel') }}?period=${currentPeriod}`;

    // Show loading notification
    alert(`Esportazione ${format.toUpperCase()} in corso...`);

    // Create a hidden link to trigger download
    const link = document.createElement('a');
    link.href = url;
    link.download = `report-${currentPeriod}-${Date.now()}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Auto-refresh charts every 5 minutes
setInterval(() => {
    if (currentChart) {
        loadChart(currentChartType, currentPeriod);
    }
}, 300000);
</script>
@endpush</x-app-layout>
