<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_a_user_cannot_add_a_card_to_another_users_project(): void
    {
        $theirs = User::factory()->create()->projects()->create(['name' => 'Theirs']);

        $this->post(route('project-cards.store', $theirs), ['name' => 'Hack'])
            ->assertForbidden();

        $this->assertDatabaseCount('project_cards', 0);
    }

    public function test_a_user_cannot_rename_another_users_card(): void
    {
        $card = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'Original']);

        $this->patch(route('project-cards.update', $card), ['name' => 'Hacked'])
            ->assertForbidden();

        $this->assertDatabaseHas('project_cards', [
            'id' => $card->id,
            'name' => 'Original',
        ]);
    }

    public function test_a_card_and_its_tasks_are_deleted(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $task = $card->tasks()->create(['name' => 'Fix Login']);

        $this->delete(route('project-cards.destroy', $card))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_cards', ['id' => $card->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_a_user_cannot_delete_another_users_card(): void
    {
        $card = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'MVP']);

        $this->delete(route('project-cards.destroy', $card))
            ->assertForbidden();

        $this->assertDatabaseHas('project_cards', ['id' => $card->id]);
    }

    public function test_a_card_can_be_reordered_within_a_project(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $backlog = $project->projectCards()->create(['name' => 'Backlog', 'position' => 1000]);
        $inProgress = $project->projectCards()->create(['name' => 'In Progress', 'position' => 2000]);
        $done = $project->projectCards()->create(['name' => 'Done', 'position' => 3000]);

        $this->patch(route('project-cards.move', $done), ['position' => 0])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_cards', ['id' => $done->id, 'position' => 1000]);
        $this->assertDatabaseHas('project_cards', ['id' => $backlog->id, 'position' => 2000]);
        $this->assertDatabaseHas('project_cards', ['id' => $inProgress->id, 'position' => 3000]);
    }

    public function test_a_position_past_the_last_card_moves_the_card_to_the_end(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $backlog = $project->projectCards()->create(['name' => 'Backlog', 'position' => 1000]);
        $inProgress = $project->projectCards()->create(['name' => 'In Progress', 'position' => 2000]);
        $done = $project->projectCards()->create(['name' => 'Done', 'position' => 3000]);

        $this->patch(route('project-cards.move', $backlog), ['position' => 10])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_cards', ['id' => $inProgress->id, 'position' => 1000]);
        $this->assertDatabaseHas('project_cards', ['id' => $done->id, 'position' => 2000]);
        $this->assertDatabaseHas('project_cards', ['id' => $backlog->id, 'position' => 3000]);
    }

    public static function invalidPositions(): array
    {
        return [
            'negative' => [-1],
            'non-integer' => ['first'],
        ];
    }

    #[DataProvider('invalidPositions')]
    public function test_rejects_an_invalid_card_position(mixed $position): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $backlog = $project->projectCards()->create(['name' => 'Backlog', 'position' => 1000]);
        $done = $project->projectCards()->create(['name' => 'Done', 'position' => 2000]);

        $this->patch(route('project-cards.move', $backlog), ['position' => $position])
            ->assertSessionHasErrors('position');

        $this->assertDatabaseHas('project_cards', ['id' => $backlog->id, 'position' => 1000]);
        $this->assertDatabaseHas('project_cards', ['id' => $done->id, 'position' => 2000]);
    }

    public function test_requires_a_card_position(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $card = $project->projectCards()->create(['name' => 'MVP', 'position' => 1000]);

        $this->patch(route('project-cards.move', $card), [])
            ->assertSessionHasErrors('position');

        $this->assertDatabaseHas('project_cards', ['id' => $card->id, 'position' => 1000]);
    }

    public function test_reordering_cards_leaves_another_projects_cards_unchanged(): void
    {
        $project = $this->user->projects()->create(['name' => 'Kanban Development']);
        $otherProject = $this->user->projects()->create(['name' => 'Another Board']);
        $backlog = $project->projectCards()->create(['name' => 'Backlog', 'position' => 1000]);
        $done = $project->projectCards()->create(['name' => 'Done', 'position' => 2000]);
        $otherCard = $otherProject->projectCards()->create(['name' => 'Other', 'position' => 1000]);

        $this->patch(route('project-cards.move', $backlog), ['position' => 1])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_cards', ['id' => $done->id, 'position' => 1000]);
        $this->assertDatabaseHas('project_cards', ['id' => $backlog->id, 'position' => 2000]);
        $this->assertDatabaseHas('project_cards', ['id' => $otherCard->id, 'position' => 1000]);
    }

    public function test_a_user_cannot_reorder_another_users_cards(): void
    {
        $card = User::factory()->create()
            ->projects()
            ->create(['name' => 'Theirs'])
            ->projectCards()
            ->create(['name' => 'MVP', 'position' => 1000]);

        $this->patch(route('project-cards.move', $card), ['position' => 0])
            ->assertForbidden();

        $this->assertDatabaseHas('project_cards', ['id' => $card->id, 'position' => 1000]);
    }
}
