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
        
        // --- ADDED FIELDS VALIDATION ---
        'status' => 'required|string|in:Active,Pending,Completed,Cancelled', // Validate against allowed statuses
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date', // Ensure end date is not before start date
        // -------------------------------
        
        'client-name' => 'nullable|string|max:255', // Added max length for safety
        'candidate-name' => 'nullable|string|max:255', // Added max length for safety
        'phone' => 'nullable|string|max:20', // Using string for flexibility, added max length
        'email' => 'nullable|email|max:255', // Used email rule for format validation
        'position' => 'nullable|string|max:255',
    ]);

    $project = Project::create([
        'owner_id' => auth()->id(), // Place owner_id first as it's typically required
        'name' => $validated['name'],
        'description' => $validated['description'] ?? '',
        
        // --- ADDED FIELDS ASSIGNMENT ---
        'status' => $validated['status'],
        'start_date' => $validated['start_date'] ?? null,
        'end_date' => $validated['end_date'] ?? null,
        // -------------------------------
        
        // Note: Hyphenated fields use the array key $validated['client-name']
        'client-name' => $validated['client-name'] ?? '', 
        'candidate-name' => $validated['candidate-name'] ?? '',
        'phone' => $validated['phone'] ?? '',
        'email' => $validated['email'] ?? '',
        'position' => $validated['position'] ?? '',
    ]);
    
    // Don't forget to return a response after creation!
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
        
        // --- ADDED FIELDS VALIDATION ---
        'status' => 'required|string|in:Active,Pending,Completed,Cancelled', // Validate against allowed statuses
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date', // Ensure end date is not before start date
        // -------------------------------
        
        'client-name' => 'nullable|string|max:255', // Added max length for safety
        'candidate-name' => 'nullable|string|max:255', // Added max length for safety
        'phone' => 'nullable|string|max:20', // Using string for flexibility, added max length
        'email' => 'nullable|email|max:255', // Used email rule for format validation
        'position' => 'nullable|string|max:255',
    ]);

    $project = Project::create([
        'owner_id' => auth()->id(), // Place owner_id first as it's typically required
        'name' => $validated['name'],
        'description' => $validated['description'] ?? '',
        
        // --- ADDED FIELDS ASSIGNMENT ---
        'status' => $validated['status'],
        'start_date' => $validated['start_date'] ?? null,
        'end_date' => $validated['end_date'] ?? null,
        // -------------------------------
        
        // Note: Hyphenated fields use the array key $validated['client-name']
        'client-name' => $validated['client-name'] ?? '', 
        'candidate-name' => $validated['candidate-name'] ?? '',
        'phone' => $validated['phone'] ?? '',
        'email' => $validated['email'] ?? '',
        'position' => $validated['position'] ?? '',
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
