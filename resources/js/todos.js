import { Client, CsrfMiddleware } from '@laravel-streams/api-client';

const client = new Client({
    baseURL: '/api/',
    middlewares: [new CsrfMiddleware()],
});

window.todosPage = function todosPage() {
    return {
        todos: [],
        title: '',
        async load() {
            const response = await client.entries.get('todos');
            const body = response.data || response;
            this.todos = body.data || body;
        },
        async create() {
            await client.entries.post('todos', { title: this.title, completed: false });
            this.title = '';
            await this.load();
        },
        async toggle(todo) {
            await client.entries.patch('todos', todo.id, { completed: ! todo.completed });
            await this.load();
        },
    };
};
