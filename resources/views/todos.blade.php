<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Todos</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @vite(['resources/js/todos.js'])
</head>
<body class="font-sans antialiased">
    <main class="max-w-xl mx-auto p-6" x-data="todosPage()" x-init="load()">
        <h1 class="text-2xl font-semibold">Todos</h1>
        <form class="mt-4 flex gap-2" @submit.prevent="create()">
            <input class="border rounded px-3 py-2 flex-1" x-model="title" placeholder="What needs doing?" required>
            <button class="border rounded px-3 py-2" type="submit">Add</button>
        </form>
        <ul class="mt-6 space-y-2">
            <template x-for="todo in todos" :key="todo.id">
                <li class="flex items-center gap-2">
                    <input type="checkbox" :checked="todo.completed" @change="toggle(todo)">
                    <span :class="todo.completed ? 'line-through' : ''" x-text="todo.title"></span>
                </li>
            </template>
        </ul>
        <p class="mt-6 text-sm"><a href="/admin">Panel</a></p>
    </main>
</body>
</html>