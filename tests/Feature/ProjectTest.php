<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_projects_index_lists_only_the_authenticated_users_projects(): void
    {
        $project = $this->user->projects()->create(['name' => 'Project Beta']);
        User::factory()->create()->projects()->create(['name' => 'Someone Else']);

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Index')
                ->has('projects', 1)
                ->where('projects.0.id', $project->id)
                ->where('projects.0.name', 'Project Beta')
            );
    }

    public function test_a_project_can_be_created(): void
    {
        $response = $this->post('/projects', [
            'name' => 'Kanban Board',
            'description' => 'Testing a feature',
        ]);

        $project = $this->user->projects()->where('name', 'Kanban Board')->firstOrFail();

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Kanban Board',
            'description' => 'Testing a feature',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_a_project_is_shown_with_its_cards_and_tasks(): void
    {
        $project = $this->user->projects()->create(['name' => 'Project Beta']);
        $card = $project->projectCards()->create(['name' => 'MVP']);
        $card->tasks()->create(['name' => 'Fix Login']);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects/Show')
                ->where('project.id', $project->id)
                ->where('project.name', 'Project Beta')
                ->has('project.project_cards', 1)
                ->where('project.project_cards.0.name', 'MVP')
                ->has('project.project_cards.0.tasks', 1)
                ->where('project.project_cards.0.tasks.0.name', 'Fix Login')
            );
    }

    public function test_a_project_name_is_required(): void
    {
        $this->post('/projects', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_user_cannot_view_another_users_project(): void
    {
        $theirs = User::factory()->create()->projects()->create(['name' => 'Theirs']);

        $this->get(route('projects.show', $theirs))
            ->assertForbidden();
    }
}
