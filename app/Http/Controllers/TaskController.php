<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\ProjectCard;
use Illuminate\Support\Facades\Gate;

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
            'position' => ($projectCard->tasks()->max('position') ?? 0 ) + 1000,
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
            'is_completed' => 'required|boolean'
        ]);

        $task->update(['is_completed' => $validated['is_completed']]);

        return to_route('projects.show', $task->projectCard->project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
