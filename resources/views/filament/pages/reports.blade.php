<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Reportes del Sistema
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Genera reportes en PDF o Excel con las estadísticas completas del sistema
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <x-heroicon-o-document-arrow-down class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reporte en PDF</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Documento para imprimir</p>
                    </div>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 mb-6 space-y-1">
                    <li>• Resumen de productos, laboratorios y usuarios</li>
                    <li>• Reservas por laboratorio y estado</li>
                    <li>• Estado de préstamos</li>
                    <li>• Inventario completo</li>
                </ul>
                <a 
                    href="{{ route('reports.dashboard.download') }}"
                    class="fi-btn fi-btn-size-lg inline-flex items-center gap-2 justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                >
                    <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                    Descargar PDF
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <x-heroicon-o-table-cells class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reporte en Excel</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Para editar y analizar</p>
                    </div>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 mb-6 space-y-1">
                    <li>• Múltiples hojas de cálculo</li>
                    <li>• Datos crudos para análisis</li>
                    <li>• Compatible con Excel y Google Sheets</li>
                    <li>• Filtrado y ordenamiento</li>
                </ul>
                <a 
                    href="{{ route('reports.excel.download') }}"
                    class="fi-btn fi-btn-size-lg inline-flex items-center gap-2 justify-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors"
                >
                    <x-heroicon-o-table-cells class="w-5 h-5" />
                    Descargar Excel
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estadísticas Rápidas</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $productsCount = \App\Models\Product::count();
                    $laboratoriesCount = \App\Models\Laboratory::count();
                    $bookingsCount = \App\Models\Booking::count();
                    $loansCount = \App\Models\Loan::count();
                @endphp
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $productsCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Productos</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $laboratoriesCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Laboratorios</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $bookingsCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Reservas</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $loansCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Préstamos</div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
