<x-filament::page>
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Calendario de Reservas
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Vista de solo lectura. Aquí puedes ver los espacios libres y ocupados.
                    </p>
                </div>
                
                <form method="get" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <select 
                        name="laboratory" 
                        onchange="this.form.submit()"
                        class="fi-select fi-select-md"
                    >
                        <option value="">Todos los laboratorios</option>
                        @php
                            $laboratories = \App\Models\Laboratory::all();
                        @endphp
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ request('laboratory') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        
        <x-filament::card class="p-0">
            @livewire(\App\Filament\Widgets\ReadOnlyCalendarWidget::class, ['laboratoryId' => request()->query('laboratory')])
        </x-filament::card>
    </div>
</x-filament::page>
