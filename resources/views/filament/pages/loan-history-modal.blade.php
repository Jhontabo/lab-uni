<div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
    @if($loans->isEmpty())
        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
            <x-heroicon-o-arrows-right-left class="w-12 h-12 mx-auto mb-2 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium">No hay préstamos registrados</h3>
            <p class="mt-1 text-sm">Este equipo no tiene historial de préstamos.</p>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha Préstamo</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha Devolución</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Observaciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                @foreach($loans as $loan)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                    <span class="text-xs font-medium text-primary-700 dark:text-primary-300">
                                        {{ substr($loan->user->name ?? 'U', 0, 1) }}{{ substr($loan->user->last_name ?? '', 0, 1) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium">{{ $loan->user->name ?? 'Usuario' }} {{ $loan->user->last_name ?? '' }}</div>
                                    <div class="text-xs text-gray-500">{{ $loan->user->document_number ?? 'Sin documento' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200 text-sm">
                            {{ $loan->loan_date?->format('d/m/Y') ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200 text-sm">
                            @if($loan->return_date)
                                <span class="{{ $loan->return_date->isPast() && $loan->status === 'active' ? 'text-danger-600 font-medium' : '' }}">
                                    {{ $loan->return_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-gray-400">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'returned' => 'info',
                                    'overdue' => 'danger',
                                    'cancelled' => 'gray',
                                ];
                                $statusLabels = [
                                    'active' => 'Activo',
                                    'returned' => 'Devuelto',
                                    'overdue' => 'Vencido',
                                    'cancelled' => 'Cancelado',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $statusColors[$loan->status] ?? 'gray' }}-100 text-{{ $statusColors[$loan->status] ?? 'gray' }}-800 dark:bg-{{ $statusColors[$loan->status] ?? 'gray' }}-900 dark:text-{{ $statusColors[$loan->status] ?? 'gray' }}-300">
                                {{ $statusLabels[$loan->status] ?? $loan->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200 text-sm">
                            {{ $loan->pivot->quantity ?? 1 }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200 text-sm max-w-xs truncate">
                            {{ $loan->observations ?? 'Sin observaciones' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
