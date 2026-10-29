<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;
use App\Models\ProjectCard;
use App\Models\Task;

class ProjectRelationshipTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_a_project_has_cards_and_cards_have_tasks(): void
    {

        $project = Project::create([
            'name' => 'Test Project',
            'description' => 'Testing purposes'
        ]);

        $card = $project->projectCards()->create([
            'name' => 'MVP'
        ]);

        $task = $card->tasks()->create([
            'name' => 'Write Test',
            'description' => 'Test database tables relationship'
        ]);

        $this->assertCount(1, $project->projectCards);
        $this->assertEquals('MVP', $project->projectCards->first()->name);

        $this->assertCount(1, $card->tasks);
        $this->assertEquals('Write Test', $card->tasks->first()->name);

        $this->assertEquals($card->id, $task->projectCard->id);
        $this->assertEquals($project->id, $task->projectCard->project->id);
    }
}
