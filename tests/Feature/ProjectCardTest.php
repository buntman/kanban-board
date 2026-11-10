<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_a_card_can_be_created_for_a_project(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);

        $response = $this->post(route('project-cards.store', $project), [
            'name' => 'MVP',
        ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_cards', [
            'project_id' => $project->id,
            'name' => 'MVP',
            'position' => 1000,
        ]);
    }

    public function test_card_positions_increment_by_1000(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);

        $this->post(route('project-cards.store', $project), ['name' => 'Backlog']);
        $this->post(route('project-cards.store', $project), ['name' => 'In Progress']);

        $this->assertDatabaseHas('project_cards', ['name' => 'Backlog', 'position' => 1000]);
        $this->assertDatabaseHas('project_cards', ['name' => 'In Progress', 'position' => 2000]);
    }

    public function test_a_card_name_is_required(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);

        $this->post(route('project-cards.store', $project), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('project_cards', 0);
    }

    public function test_a_card_belongs_to_the_project_in_the_url(): void
    {
        $project = $this->user->projects()->create(['name' => 'Project A']);
        $other = $this->user->projects()->create(['name' => 'Project B']);

        $this->post(route('project-cards.store', $project), ['name' => 'MVP']);

        $this->assertDatabaseHas('project_cards', [
            'name' => 'MVP',
            'project_id' => $project->id,
        ]);
        $this->assertDatabaseMissing('project_cards', [
            'name' => 'MVP',
            'project_id' => $other->id,
        ]);
    }

    public function test_a_card_can_be_renamed(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);

        $response = $this->patch(route('project-cards.update', $card), [
            'name' => 'In Progress',
        ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_cards', [
            'id' => $card->id,
            'name' => 'In Progress',
        ]);
    }

    public function test_a_card_name_is_required_when_renaming(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);

        $this->patch(route('project-cards.update', $card), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('project_cards', [
            'id' => $card->id,
            'name' => 'MVP',
        ]);
    }
}
