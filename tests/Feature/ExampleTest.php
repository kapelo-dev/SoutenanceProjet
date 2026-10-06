<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Visiteur non connecté : la racine renvoie vers la page de connexion
        $this->get('/')->assertRedirect(route('login'));
    }
}
