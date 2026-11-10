<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_project_list(): void
    {
        $this->get('/projects')->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_from_a_project(): void
    {
        $project = User::factory()->create()->projects()->create(['name' => 'Board']);

        $this->get(route('projects.show', $project))
            ->assertRedirect(route('login'));
    }

    public function test_guests_cannot_mutate_boards(): void
    {
        $project = User::factory()->create()->projects()->create(['name' => 'Board']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login']);

        $this->post('/projects', ['name' => 'Hack'])->assertRedirect(route('login'));
        $this->post(route('project-cards.store', $project), ['name' => 'Hack'])->assertRedirect(route('login'));
        $this->patch(route('project-cards.update', $card), ['name' => 'Hack'])->assertRedirect(route('login'));
        $this->post(route('tasks.store', $card), ['name' => 'Hack'])->assertRedirect(route('login'));
        $this->patch(route('tasks.update', $task), ['is_completed' => true])->assertRedirect(route('login'));

        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseCount('project_cards', 1);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => false,
        ]);
    }
}
