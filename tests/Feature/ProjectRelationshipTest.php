<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_has_many_projects(): void
    {
        $user = User::factory()->create();

        $user->projects()->createMany([
            ['name' => 'Kanban Board'],
            ['name' => 'Portfolio'],
        ]);

        $this->assertCount(2, $user->fresh()->projects);
        $this->assertEqualsCanonicalizing(
            ['Kanban Board', 'Portfolio'],
            $user->projects()->pluck('name')->all()
        );
    }

    public function test_a_project_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create(['name' => 'Kanban Board']);

        $this->assertSame($user->id, $project->user_id);
        $this->assertTrue($project->user->is($user));
    }

    public function test_a_user_only_queries_their_own_projects(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ownerProject = $owner->projects()->create(['name' => 'Mine']);
        $otherProject = $other->projects()->create(['name' => 'Theirs']);

        $this->assertTrue($owner->projects()->whereKey($ownerProject->id)->exists());
        $this->assertFalse($owner->projects()->whereKey($otherProject->id)->exists());
    }

    public function test_deleting_a_user_cascades_to_their_projects(): void
    {
        $user = User::factory()->create();
        $project = $user->projects()->create(['name' => 'Kanban Board']);

        $user->delete();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_a_project_cannot_be_created_without_a_user(): void
    {
        $this->expectException(QueryException::class);

        Project::create(['name' => 'Orphan Board']);
    }

    public function test_a_project_has_cards_and_cards_have_tasks(): void
    {
        $project = User::factory()->create()
            ->projects()
            ->create([
                'name' => 'Test Project',
                'description' => 'Testing purposes',
            ]);

        $card = $project->projectCards()->create([
            'name' => 'MVP',
        ]);

        $task = $card->tasks()->create([
            'name' => 'Write Test',
            'description' => 'Test database tables relationship',
        ]);

        $this->assertCount(1, $project->projectCards);
        $this->assertEquals('MVP', $project->projectCards->first()->name);

        $this->assertCount(1, $card->tasks);
        $this->assertEquals('Write Test', $card->tasks->first()->name);

        $this->assertEquals($card->id, $task->projectCard->id);
        $this->assertEquals($project->id, $task->projectCard->project->id);
    }
}
