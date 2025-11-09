<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // show tasks for a project (optional if you list tasks on project.show)
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()->with(['assignee','creator'])->orderBy('position')->get();

        return view('tasks.index', compact('project','tasks'));
    }

    public function create(Project $project)
    {
        $this->authorize('createTask', $project);

        // list project members to assign tasks (owner + members)
        $members = $project->members()->get();
        // include owner explicitly if not in pivot
        if ($project->owner) {
            $ownerInPivot = $members->contains('id', $project->owner->id);
            if (! $ownerInPivot) {
                $members->prepend($project->owner);
            }
        }

        return view('tasks.create', compact('project','members'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('createTask', $project);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_at' => 'nullable|date',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'creator_id' => auth()->id(),
            'assignee_id' => $data['assignee_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'],
            'due_at' => $data['due_at'] ?? null,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Task created.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task->project);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task->project);

        $project = $task->project;
        $members = $project->members()->get();
        if ($project->owner) {
            $ownerInPivot = $members->contains('id', $project->owner->id);
            if (! $ownerInPivot) {
                $members->prepend($project->owner);
            }
        }

        return view('tasks.edit', compact('task','project','members'));
    }

    /**
     * Full update (title, assignee, priority, status, etc.)
     * Kept restricted to your existing 'update' authorization (owner/admin).
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task->project);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,done,blocked',
            'due_at' => 'nullable|date',
        ]);

        $task->update($data);

        return redirect()->route('projects.show', $task->project)->with('success','Task updated.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task->project);

        $project = $task->project;
        $task->delete();

        return redirect()->route('projects.show', $project)->with('success','Task deleted.');
    }

    /**
     * New: allow project members OR owner to change ONLY the task status.
     * Route: PATCH /projects/{project}/tasks/{task}/status
     */
    public function updateStatus(Request $request, Project $project, Task $task)
    {
        // ensure correct project-task relation
        if ($task->project_id !== $project->id) {
            return redirect()->back()->withErrors('Task does not belong to this project.');
        }

        $user = $request->user();

        // owner check
        $isOwner = ($project->owner_id ?? null) === $user->id;

        // member check (adjust if your members relationship uses a different pivot / column)
        $isMember = $project->members()->where('user_id', $user->id)->exists();

        if (! ($isOwner || $isMember) ) {
            // If AJAX/JSON request, return JSON 403 for better UX
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            abort(403, 'You are not allowed to update task status.');
        }

        $data = $request->validate([
            'status' => 'required|in:todo,in_progress,review,done,blocked',
        ]);

        $task->status = $data['status'];
        $task->save();

        // respond appropriately depending on request type
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Task status updated.', 'task' => $task]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Task status updated.');
    }
}
