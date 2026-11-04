<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_task_can_be_created_for_a_card(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);

        $response = $this->post(route('tasks.store', $card), [
            'name' => 'Fix Login',
        ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'project_card_id' => $card->id,
            'name' => 'Fix Login',
            'position' => 1000,
        ]);
    }

    public function test_task_positions_increment_by_1000(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);

        $this->post(route('tasks.store', $card), ['name' => 'First Task']);
        $this->post(route('tasks.store', $card), ['name' => 'Second Task']);

        $this->assertDatabaseHas('tasks', ['name' => 'First Task', 'position' => 1000]);
        $this->assertDatabaseHas('tasks', ['name' => 'Second Task', 'position' => 2000]);
    }

    public function test_a_task_name_is_required(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);

        $this->post(route('tasks.store', $card), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_a_task_belongs_to_the_card_in_the_url(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $otherCard = $project->projectCards()->create(['name' => 'Done']);

        $this->post(route('tasks.store', $card), ['name' => 'Fix Login']);

        $this->assertDatabaseHas('tasks', [
            'name' => 'Fix Login',
            'project_card_id' => $card->id,
        ]);
        $this->assertDatabaseMissing('tasks', [
            'name' => 'Fix Login',
            'project_card_id' => $otherCard->id,
        ]);
    }
}
