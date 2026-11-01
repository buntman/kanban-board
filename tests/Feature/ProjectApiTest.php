<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;
use App\Models\ProjectCard;

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

    public function test_can_get_a_project(): void
    {
        $project = Project::create(['name' => 'Project Beta']);
        $project_card = ProjectCard::create([
            'name' => 'MVP',
            'project_id' => $project->id
        ]);

        $task = $this->postJson('/api/tasks', [
            'name' => 'Fix Login',
            'project_card_id' => $project_card->id
        ]);

        $response = $this->getJson("api/projects/{$project->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $project->id)
                 ->assertJsonPath('data.name', 'Project Beta')
                 ->assertJsonPath('data.project_cards.0.name', 'MVP')
                 ->assertJsonPath('data.project_cards.0.tasks.0.name', 'Fix Login');
    }
}
