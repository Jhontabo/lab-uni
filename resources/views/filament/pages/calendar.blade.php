<x-filament-panels::page>
    <div class="flex items-center justify-end mb-4">
        <form method="GET" action="{{ url()->current() }}">
            <select name="laboratory" onchange="this.form.submit()"
                class="fi-select-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75
                       focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500
                       dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500
                       text-sm py-2 px-3">
                @foreach ($this->getDropdownOptions() as $key => $label)
                    <option value="{{ $key }}"
                        {{ request()->query('laboratory', 'All') === (string) $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</x-filament-panels::page>
