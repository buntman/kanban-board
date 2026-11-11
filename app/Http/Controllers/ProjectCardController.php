<?php

namespace App\Http\Controllers;

use App\Models\ProjectCard;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ProjectCard::with('tasks')->get()
        ], 200);
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
    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $project->projectCards()->create([
            'name' => $validated['name'],
            'position' => ($project->projectCards()->max('position') ?? 0) + 1000,
        ]);

        return to_route('projects.show', $project);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectCard $projectCard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectCard $projectCard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectCard $projectCard)
    {
        Gate::authorize('update', $projectCard->project);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $projectCard->update($validated);

        return to_route('projects.show', $projectCard->project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectCard $projectCard)
    {
        Gate::authorize('delete', $projectCard->project);

        $project = $projectCard->project;
        $projectCard->delete();

        return to_route('projects.show', $project);
    }
}
