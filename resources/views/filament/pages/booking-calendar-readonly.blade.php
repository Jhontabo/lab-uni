<x-filament::page>
    @php
        $laboratoryId = request()->query('laboratory');
    @endphp
    
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Calendario de Disponibilidad
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        🟢 = Libre &nbsp;|&nbsp; 🔴 = Ocupado &nbsp;|&nbsp; Selecciona un laboratorio para filtrar
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
        
        <x-filament::card class="p-0 overflow-hidden">
            <div id="calendar-container" style="min-height: 600px;">
                @livewire(\App\Filament\Widgets\ReadOnlyCalendarWidget::class, ['laboratoryId' => $laboratoryId])
            </div>
        </x-filament::card>
    </div>
</x-filament::page>
