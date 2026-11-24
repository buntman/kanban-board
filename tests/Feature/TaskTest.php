<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_a_task_can_be_created_for_a_card(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
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
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);

        $this->post(route('tasks.store', $card), ['name' => 'First Task']);
        $this->post(route('tasks.store', $card), ['name' => 'Second Task']);

        $this->assertDatabaseHas('tasks', ['name' => 'First Task', 'position' => 1000]);
        $this->assertDatabaseHas('tasks', ['name' => 'Second Task', 'position' => 2000]);
    }

    public function test_a_task_name_is_required(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);

        $this->post(route('tasks.store', $card), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_a_task_belongs_to_the_card_in_the_url(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
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
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
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
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
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
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
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
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
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

    public function test_a_task_description_can_be_added_without_changing_other_fields(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create([
            'name' => 'Fix Login',
            'position' => 1000,
        ]);

        $this->patch(route('tasks.update', $task), [
            'description' => 'Investigate the login flow',
        ])->assertRedirect(route('projects.show', $project));

        $task->refresh();

        $this->assertSame('Investigate the login flow', $task->description);
        $this->assertSame('Fix Login', $task->name);
        $this->assertFalse($task->is_completed);
        $this->assertEquals(1000, $task->position);
        $this->assertSame($card->id, $task->project_card_id);
    }

    public function test_a_task_name_and_description_can_be_updated_together(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create([
            'name' => 'Fix Login',
            'description' => 'Old description',
        ]);

        $this->patch(route('tasks.update', $task), [
            'name' => 'Fix Registration',
            'description' => 'Registration is broken too',
        ])->assertRedirect(route('projects.show', $project));

        $task->refresh();

        $this->assertSame('Fix Registration', $task->name);
        $this->assertSame('Registration is broken too', $task->description);
        $this->assertFalse($task->is_completed);
    }

    public function test_a_task_description_can_be_cleared(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create([
            'name' => 'Fix Login',
            'description' => 'Investigate the login flow',
        ]);

        $this->patch(route('tasks.update', $task), [
            'description' => '',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertNull($task->fresh()->description);
        $this->assertSame('Fix Login', $task->fresh()->name);
    }

    public function test_a_task_name_cannot_be_emptied(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login']);

        $this->patch(route('tasks.update', $task), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertSame('Fix Login', $task->fresh()->name);
    }

    public function test_updating_a_missing_task_returns_not_found(): void
    {
        $this->patch(route('tasks.update', 999999), [
            'is_completed' => true,
        ])->assertNotFound();
    }

    public function test_a_task_can_be_deleted(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login']);

        $this->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('project_cards', ['id' => $card->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_deleting_a_missing_task_returns_not_found(): void
    {
        $this->delete(route('tasks.destroy', 999999))->assertNotFound();
    }

    public function test_a_user_cannot_delete_another_users_task(): void
    {
        $task = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'MVP'])
            ->tasks()
            ->create(['name' => 'Fix Login']);

        $this->delete(route('tasks.destroy', $task))->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_a_user_cannot_add_a_task_to_another_users_card(): void
    {
        $card = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'MVP']);

        $this->post(route('tasks.store', $card), ['name' => 'Hack'])
            ->assertForbidden();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_a_user_cannot_toggle_another_users_task(): void
    {
        $task = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'MVP'])
            ->tasks()
            ->create(['name' => 'Fix Login']);

        $this->patch(route('tasks.update', $task), ['is_completed' => true])
            ->assertForbidden();

        $this->assertFalse($task->fresh()->is_completed);
    }

    public function test_a_user_cannot_edit_another_users_task_details(): void
    {
        $task = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'MVP'])
            ->tasks()
            ->create(['name' => 'Fix Login']);

        $this->patch(route('tasks.update', $task), [
            'name' => 'Hacked',
            'description' => 'Hacked',
        ])->assertForbidden();

        $task->refresh();

        $this->assertSame('Fix Login', $task->name);
        $this->assertNull($task->description);
    }

    public function test_a_task_can_be_reordered_within_a_card(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $firstTask = $card->tasks()->create(['name' => 'First Task', 'position' => 1000]);
        $secondTask = $card->tasks()->create(['name' => 'Second Task', 'position' => 2000]);
        $thirdTask = $card->tasks()->create(['name' => 'Third Task', 'position' => 3000]);

        $this->patch(route('tasks.move', $thirdTask), [
            'project_card_id' => $card->id,
            'position' => 0,
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'id' => $thirdTask->id,
            'project_card_id' => $card->id,
            'position' => 1000,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $firstTask->id,
            'project_card_id' => $card->id,
            'position' => 2000,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $secondTask->id,
            'project_card_id' => $card->id,
            'position' => 3000,
        ]);
    }

    public function test_a_task_can_be_moved_to_another_card_in_the_same_project(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $sourceCard = $project->projectCards()->create(['name' => 'To Do']);
        $destinationCard = $project->projectCards()->create(['name' => 'Done']);
        $movedTask = $sourceCard->tasks()->create(['name' => 'Move Me', 'position' => 1000]);
        $remainingTask = $sourceCard->tasks()->create(['name' => 'Stay Here', 'position' => 2000]);
        $firstDestinationTask = $destinationCard->tasks()->create(['name' => 'Done First', 'position' => 1000]);
        $secondDestinationTask = $destinationCard->tasks()->create(['name' => 'Done Second', 'position' => 2000]);

        $this->patch(route('tasks.move', $movedTask), [
            'project_card_id' => $destinationCard->id,
            'position' => 1,
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'id' => $movedTask->id,
            'project_card_id' => $destinationCard->id,
            'position' => 2000,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $firstDestinationTask->id,
            'project_card_id' => $destinationCard->id,
            'position' => 1000,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $secondDestinationTask->id,
            'project_card_id' => $destinationCard->id,
            'position' => 3000,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $remainingTask->id,
            'project_card_id' => $sourceCard->id,
            'position' => 1000,
        ]);
        $this->assertDatabaseMissing('tasks', [
            'id' => $movedTask->id,
            'project_card_id' => $sourceCard->id,
        ]);
    }

    public function test_a_task_cannot_be_moved_to_another_users_card(): void
    {
        $project = $this->user->projects()->create(['name' => 'Mine']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login', 'position' => 1000]);
        $theirCard = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'Done']);

        $this->patch(route('tasks.move', $task), [
            'project_card_id' => $theirCard->id,
            'position' => 0,
        ])->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'project_card_id' => $card->id,
            'position' => 1000,
        ]);
        $this->assertDatabaseCount('tasks', 1);
    }

    public function test_a_task_cannot_be_moved_to_a_card_in_another_project(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login', 'position' => 1000]);
        $otherProject = $this->user->projects()->create(['name' => 'Another Board']);
        $otherCard = $otherProject->projectCards()->create(['name' => 'Done']);

        $this->patch(route('tasks.move', $task), [
            'project_card_id' => $otherCard->id,
            'position' => 0,
        ])->assertSessionHasErrors('project_card_id');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'project_card_id' => $card->id,
            'position' => 1000,
        ]);
        $this->assertDatabaseCount('tasks', 1);
    }
}
