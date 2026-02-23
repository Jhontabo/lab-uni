<?php

?>

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-xl p-6 text-white">
            <h2 class="text-2xl font-bold">Reportes del Sistema</h2>
            <p class="text-primary-100 mt-1">Genera reportes en PDF de las estadísticas del sistema</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exportar Reportes</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Descarga un reporte completo en formato PDF con todas las estadísticas del sistema, incluyendo:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mb-6 space-y-1">
                <li>Resumen de productos, laboratorios, usuarios y reservas</li>
                <li>Reservas por laboratorio</li>
                <li>Estado de préstamos</li>
                <li>Últimas reservas y préstamos</li>
                <li>Inventario de productos</li>
            </ul>
            
            <a 
                href="{{ route('reports.dashboard.download') }}"
                class="fi-btn fi-btn-size-lg fi-color-success inline-flex items-center gap-2 justify-center px-6 py-3 bg-success-600 text-white hover:bg-success-500 focus:ring-success-500/50 rounded-lg fi-size-lg"
            >
                <x-heroicon-o-document-arrow-down class="w-6 h-6" />
                Descargar Reporte General (PDF)
            </a>
        </div>
    </div>
</x-filament-panels::page>
