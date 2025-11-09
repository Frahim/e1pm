<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all projects the user owns or is a member of.
     */
    public function index()
    {
        $user = auth()->user();

        $projects = Project::where('owner_id', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show form to create new project.
     */
    public function create()
    {
        $this->authorize('create', Project::class);
        return view('projects.create');
    }
    /**
     * Store new project in database.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'owner_id' => auth()->id(),
        ]);

        if (method_exists($project, 'members')) {
            $project->members()->attach(auth()->id(), ['role' => 'owner']);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully!');
    }

    /**
     * Display single project.
     */
    public function show(Project $project)
{
    $this->authorize('view', $project);

    // --- NEW LOGIC FOR ADD MEMBER DROPDOWN ---
    $allUsers = \App\Models\User::select('id', 'name', 'email')
        // Filter out the current project owner
        ->where('id', '!=', $project->owner_id) 
        // Filter out existing members (eager loaded members)
        ->whereNotIn('id', $project->members->pluck('id'))
        // Optional: limit to 50 for a large list, but fetch more than 20
        ->limit(50) 
        ->get();
    
    // Pass $availableMembers to the view instead of $allUsers
    $availableMembers = $allUsers;
    // ------------------------------------------

    return view('projects.show', compact('project', 'availableMembers'));
}
    /**
     * Show form for editing the project.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    /**
     * Update the project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully!');
    }


public function updateStatus(Request $request, Project $project)
{
    $this->authorize('update', $project); // Optional: only allow manager/admin

    $request->validate([
        'status' => 'required|in:active,archived,completed',
    ]);

    $project->status = $request->status;
    $project->save();

    return redirect()->back()->with('success', 'Project status updated successfully!');
}


    /**
     * Remove the project from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Add a member to the project (attach to pivot).
     * Route: POST projects/{project}/add-member
     */
    public function addMember(Request $request, Project $project)
    {
        // authorize: only owner or manager allowed (uses ProjectPolicy->manageMembers)
        $this->authorize('manageMembers', $project);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,manager,member',
        ]);

        // Prevent adding the owner again or duplicate owners via 'owner' role
        if ($data['user_id'] == $project->owner_id && $data['role'] !== 'owner') {
            return back()->withErrors(['user_id' => 'Cannot change the owner role.']);
        }

        if (! method_exists($project, 'members')) {
            return back()->withErrors(['members' => 'Project members relation not configured.']);
        }

        // syncWithoutDetaching keeps existing members and updates role if present
       // ProjectController@addMember
$project->members()->syncWithoutDetaching([$data['user_id'] => ['role' => $data['role']]]);

        // If role is owner, update owner_id on project (only if caller is current owner)
        if ($data['role'] === 'owner' && auth()->id() === $project->owner_id) {
            $project->owner_id = $data['user_id'];
            $project->save();
        }

        return back()->with('success', 'Member added/updated successfully.');
    }
}
