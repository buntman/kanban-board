<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ProjectCard;
use App\Models\Project;

class ProjectCardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_project_cards(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        ProjectCard::create(['name' => 'MVP', 'project_id' => $project->id]);

        $response = $this->getJson('/api/project-cards');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['name' => 'MVP']);
    }

    public function test_can_create_a_project_card(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $response = $this->postJson('/api/project-cards', [
            'name' => 'MVP',
            'project_id' => $project->id
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('project_cards', ['name' => 'MVP']);
    }
}
