<div>
    {{-- Example Component View --}}
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">Crear Usuario</h2>

        <form wire:submit="save">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nombre
                </label>
                <input
                    type="text"
                    id="name"
                    wire:model="form.name"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingresa tu nombre"
                >
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email
                </label>
                <input
                    type="email"
                    id="email"
                    wire:model="form.email"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingresa tu email"
                >
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Contraseña
                </label>
                <input
                    type="password"
                    id="password"
                    wire:model="form.password"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ingresa tu contraseña"
                >
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirmar Contraseña
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    wire:model="form.password_confirmation"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Confirma tu contraseña"
                >
            </div>

            <div class="flex space-x-4">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="save">Crear Usuario</span>
                    <span wire:loading wire:target="save">Creando...</span>
                </button>

                <button
                    type="button"
                    wire:click="saveWithTrait"
                    wire:loading.attr="disabled"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="saveWithTrait">Con Trait</span>
                    <span wire:loading wire:target="saveWithTrait">Procesando...</span>
                </button>
            </div>
        </form>

        {{-- Success/Error Messages --}}
        <div class="mt-4">
            <div
                x-data="{ show: false }"
                x-on:success.window="show = true; setTimeout(() => show = false, 3000)"
                x-show="show"
                x-transition
                class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"
            >
                <span x-text="$event.detail"></span>
            </div>

            <div
                x-data="{ show: false }"
                x-on:error.window="show = true; setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
            >
                <span x-text="$event.detail"></span>
            </div>
        </div>
    </div>

    {{-- Include Alpine.js for the notifications --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</div>
