<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    //
    public function index(Request $request)
    {
        $page = $request->query('per_page', 4);
        $projects = Project::orderBy('created_at', 'desc')->paginate($page);
        return response()->json([
            "status" => "success",
            "data" => $projects
        ], 200);
    }

    public function getAllProjects()
    {
        $projects = Project::orderBy('year', 'desc')->limit(6)->get();
        return response()->json([
            "status" => "success",
            "data" => $projects
        ], 200);
    }
    public function store(StoreProjectRequest $projectRequest)
    {
        $input = $projectRequest->all();
        $project = Project::create($input);
        return response()->json([
            "status" => "success",
            "data" => $project
        ], 201);
    }

    public function show($id)
    {

    }

    public function update(UpdateProjectRequest $updateProject, Project $project)
    {
            $input = $updateProject->all();
            $project->update($input);
            return response()->json([
                "status" => "success",
                "data" => $project
            ], 200);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json([
            "status" => "success",
            "message" => "Project deleted successfully"
        ], 200);
    }
}
