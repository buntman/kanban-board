<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_projects(): void
    {
        Project::create(['name' => 'Project Beta']);

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['name' => 'Project Beta']);
    }

    public function test_can_create_a_project(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Kanban Board',
            'description' => 'Testing a feature'
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('projects', ['name' => 'Kanban Board']);
    }
}
