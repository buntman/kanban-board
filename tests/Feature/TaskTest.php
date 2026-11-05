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

    public function test_a_task_can_be_marked_as_complete(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login']);

        $response = $this->patch(route('tasks.update', $task), [
            'is_completed' => true,
        ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => true,
        ]);
    }

    public function test_a_task_can_be_marked_as_incomplete_again(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create([
            'name' => 'Fix Login',
            'is_completed' => true,
        ]);

        $this->patch(route('tasks.update', $task), [
            'is_completed' => false,
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => false,
        ]);
    }

    public function test_is_completed_must_be_a_boolean(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login']);

        $this->patch(route('tasks.update', $task), [
            'is_completed' => 'not-a-boolean',
        ])->assertSessionHasErrors('is_completed');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => false,
        ]);
    }

    public function test_marking_a_task_complete_does_not_change_other_fields(): void
    {
        $project = Project::create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create([
            'name' => 'Fix Login',
            'description' => 'Investigate the login flow',
            'position' => 1000,
        ]);

        $this->patch(route('tasks.update', $task), [
            'is_completed' => true,
        ]);

        $task->refresh();

        $this->assertTrue($task->is_completed);
        $this->assertSame('Fix Login', $task->name);
        $this->assertSame('Investigate the login flow', $task->description);
        $this->assertEquals(1000, $task->position);
        $this->assertSame($card->id, $task->project_card_id);
    }

    public function test_updating_a_missing_task_returns_not_found(): void
    {
        $this->patch(route('tasks.update', 999999), [
            'is_completed' => true,
        ])->assertNotFound();
    }
}
