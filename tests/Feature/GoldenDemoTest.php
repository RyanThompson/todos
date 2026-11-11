<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoldenDemoTest extends TestCase
{
    public function test_public_page_and_api_round_trip(): void
    {
        $this->withoutVite();

        $this->get('/')->assertOk()->assertSee('Todos');

        $this->postJson('/api/streams/todos/entries', [
            'title' => 'Buy milk',
            'completed' => false,
        ])->assertSuccessful();

        $this->getJson('/api/streams/todos/entries')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Buy milk']);

        $this->getJson('/api/streams/secrets/entries')->assertNotFound();
    }

    public function test_env_admin_login_gates_the_panel(): void
    {
        $this->post('/admin/login', [
            'email' => 'wrong@todos.test',
            'password' => 'nope',
        ])->assertSessionHasErrors('email');

        $this->post('/admin/login', [
            'email' => 'admin@todos.test',
            'password' => 'password',
        ])->assertRedirect('/admin');
    }
}
