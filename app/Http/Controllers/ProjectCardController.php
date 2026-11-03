<?php

namespace App\Http\Controllers;

use App\Models\ProjectCard;
use App\Models\Project;
use Illuminate\Http\Request;

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectCard $projectCard)
    {
        //
    }
}
