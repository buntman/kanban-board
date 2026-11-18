<?php

namespace App\Http\Controllers;

use App\Models\ProjectCard;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProjectCard $projectCard)
    {
        Gate::authorize('update', $projectCard->project);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $projectCard->tasks()->create([
            'name' => $validated['name'],
            'position' => ($projectCard->tasks()->max('position') ?? 0) + 1000,
        ]);

        return to_route('projects.show', $projectCard->project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task->projectCard->project);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'sometimes|nullable|string|max:2000',
            'is_completed' => 'sometimes|required|boolean',
        ]);

        $task->update($validated);

        return to_route('projects.show', $task->projectCard->project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Gate::authorize('update', $task->projectCard->project);

        $project = $task->projectCard->project;
        $task->delete();

        return to_route('projects.show', $project);
    }

    public function move(Request $request, Task $task)
    {
        $sourceCard = $task->projectCard;
        $project = $sourceCard->project;

        Gate::authorize('update', $task->projectCard->project);

        $validated = $request->validate([
            'project_card_id' => [
                'required',
                'integer',
                Rule::exists('project_cards', 'id'),
            ],
            'position' => ['required', 'integer', 'min:0'],
        ]);

        $destinationCard = ProjectCard::findOrFail($validated['project_card_id']);

        Gate::authorize('update', $destinationCard->project);

        if ($destinationCard->project_id !== $project->id) {
            throw ValidationException::withMessages([
                'project_card_id' => 'The selected project card must belong to this project.',
            ]);
        }

        $sourceTaskIds = $sourceCard->tasks()
            ->whereKeyNot($task->id)
            ->orderBy('position')
            ->pluck('id')
            ->all();

        $destinationTaskIds = $destinationCard->tasks()
            ->whereKeyNot($task->id)
            ->orderBy('position')
            ->pluck('id')
            ->all();

        $position = min($validated['position'], count($destinationTaskIds));

        array_splice($destinationTaskIds, $position, 0, [$task->id]);

        DB::transaction(function () use (
            $sourceCard,
            $destinationCard,
            $sourceTaskIds,
            $destinationTaskIds,
        ) {
            foreach ($destinationTaskIds as $index => $taskId) {
                Task::whereKey($taskId)->update([
                    'project_card_id' => $destinationCard->id,
                    'position' => ($index + 1) * 1000,
                ]);
            }

            if ($sourceCard->isNot($destinationCard)) {
                foreach ($sourceTaskIds as $index => $taskId) {
                    Task::whereKey($taskId)->update([
                        'position' => ($index + 1) * 1000,
                    ]);
                }
            }
        });

        return to_route('projects.show', $project);
    }
}
