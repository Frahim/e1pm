@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow rounded-lg border overflow-hidden">
        <div class="px-6 py-6 border-b">
            <div class="flex items-start justify-between space-x-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $project->description ?? 'No description provided.' }}</p>

                    <div class="mt-3 text-sm text-gray-500">
                        <span class="mr-4">Owner: <strong class="text-gray-700">{{ $project->owner->name ?? '—' }}</strong></span>
                        <span>Your role: <strong class="text-gray-700">{{ $project->roleFor(auth()->id()) ?? ($project->owner_id === auth()->id() ? 'owner' : 'not a member') }}</strong></span>
                    </div>
                </div>

                <div class="flex items-start space-x-2">
                    @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-sm shadow-sm hover:bg-indigo-700">Edit</a>
                    @endcan
                    @can('delete', $project)
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project?');" class="inline-block ml-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm shadow-sm hover:bg-red-700">
                            Delete Project
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-2">
                    <h3 class="text-sm font-medium text-gray-600 mb-2">Project Overview</h3>
                    <div class="prose prose-sm max-w-none">
                        <p class="text-gray-700">{{ $project->description ?? 'No extra details.' }}</p>
                    </div>

                    <div class="mt-6">
                        <h4 class="mt-6 text-lg font-medium">Tasks</h4>

                        @can('createTask', $project)
                        <a href="{{ route('projects.tasks.create', $project) }}" class="inline-block mt-3 mb-4 px-3 py-2 bg-emerald-600 text-white rounded">+ New Task</a>
                        @endcan

                        @if($project->tasks->isEmpty())
                        <p class="text-gray-600">No tasks yet.</p>
                        @else
                        <div class="space-y-3">
                            @php
                            // determine current user's permission to change status
                            $currentUserId = auth()->id();
                            $isOwner = isset($project->owner_id) && $project->owner_id === $currentUserId;
                            $role = $project->roleFor($currentUserId);
                            // treat 'member' and 'manager' as allowed; adjust roles array if you use different names
                            $isMemberRole = in_array($role, ['member','manager']);
                            $canChangeGlobally = $isOwner || $isMemberRole;
                            $projectId = $project->id;
                            @endphp

                            @foreach($project->tasks()->with('assignee')->orderBy('created_at','desc')->get() as $task)
                            <div class="p-3 border rounded flex justify-between items-start">
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-900">{{ $task->title }}</a>
                                    <div class="text-sm text-gray-600">{{ Str::limit($task->description, 120) }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        @if($task->assignee) Assigned to: {{ $task->assignee->name }} @else Unassigned @endif
                                        · {{ ucfirst($task->priority) }}
                                    </div>
                                </div>

                                <div class="text-right" style="min-width:170px;">
                                    <div class="text-sm text-gray-500">{{ optional($task->due_at)->format('M j, Y') ?? '' }}</div>

                                    {{-- Status label --}}
                                    <div id="status-label-{{ $task->id }}" class="mt-2">
                                        <span class="inline-block px-2 py-1 text-xs rounded {{ $task->status == 'done' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_',' ',$task->status)) }}
                                        </span>
                                    </div>

                                    {{-- Status select (visible only to owner & members) --}}
                                    @php
                                    // compute per-task permission — if your roleFor returns other role names adjust here
                                    $canChange = ($project->owner_id === auth()->id()) || in_array($project->roleFor(auth()->id()), ['member','manager']);
                                    @endphp

                                    @if($canChange)
                                    <form class="status-form mt-3" method="POST" action="{{ route('projects.tasks.updateStatus', ['project' => $project->id, 'task' => $task->id]) }}">
                                        @csrf
                                        @method('PATCH')

                                        <select name="status" class="status-select block w-full text-sm border rounded px-2 py-1"
                                            data-task-id="{{ $task->id }}" aria-label="Change status for {{ $task->title }}">
                                            @php
                                            $statuses = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done', 'blocked' => 'Blocked'];
                                            @endphp
                                            @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @if($task->status === $value) selected @endif>{{ $label }}</option>
                                            @endforeach
                                        </select>

                                        <noscript>
                                            <div class="mt-2">
                                                <button type="submit" class="inline-block px-3 py-1 bg-indigo-600 text-white text-xs rounded">Update</button>
                                            </div>
                                        </noscript>
                                    </form>
                                    @endif

                                    <div class="mt-3 space-x-2">
                                        @can('update', $project)
                                        <a href="{{ route('tasks.edit', $task) }}" class="text-sm text-blue-600">Edit</a>
                                        @endcan
                                        @can('delete', $project)
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Delete task?')" class="text-sm text-red-600">Delete</button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <aside class="bg-white border rounded p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Members</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-800">{{ $project->owner->name }}</div>
                                <div class="text-xs text-gray-500">Owner</div>
                            </div>
                        </li>

                        @foreach($project->members as $m)
                        <li class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-800">{{ $m->name }}</div>
                                <div class="text-xs text-gray-500">{{ $m->email }}</div>
                            </div>
                            <div class="text-xs text-gray-500">{{ ucfirst($m->pivot->role) }}</div>
                        </li>
                        @endforeach
                    </ul>

                    @can('manageMembers', $project)
                    <form action="{{ route('projects.add-member', $project) }}" method="POST" class="mt-4 space-y-2">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-600">Add member</label>
                            <select name="user_id" required class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">Select user</option>
                                @foreach(\App\Models\User::where('id','!=',auth()->id())->limit(20)->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-600">Role</label>
                            <select name="role" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="member">Member</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 bg-indigo-600 text-white rounded text-sm">Add</button>
                        </div>
                    </form>
                    @endcan
                </aside>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t text-xs text-gray-500">
            Created: {{ $project->created_at->format('M j, Y H:i') }} · Last updated: {{ $project->updated_at->diffForHumans() }}
            <span>Status:</span>
            <button class="badge 
            @if($project->status == 'active') bg-slate-200            
            @elseif($project->status == 'completed') bg-primary 
            @else bg-secondary @endif">
                {{ ucfirst($project->status) }}
            </button>
        </div>
    </div>
</div>

{{-- Inline JS for AJAX status update (uses Axios). Fallback is the PATCH form above --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // CSRF for Axios (requires that your layout includes a meta csrf-token)
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
        }
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        // project id for building API path
        const projectId = {
            {
                $projectId ?? $project - > id
            }
        };

        // delegate change events for .status-select elements
        document.body.addEventListener('change', function(e) {
            const el = e.target.closest('.status-select');
            if (!el) return;

            const taskId = el.dataset.taskId;
            const newStatus = el.value;
            const selectEl = el;

            // Optimistic UI: update label immediately
            const label = document.getElementById('status-label-' + taskId);
            const prevLabelHtml = label ? label.innerHTML : null;
            if (label) {
                label.innerHTML = `<span class="inline-block px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">${newStatus.replace('_',' ')}</span>`;
            }

            axios.patch(`/projects/${projectId}/tasks/${taskId}/status`, {
                    status: newStatus
                })
                .then(function(res) {
                    if (res.data && res.data.task) {
                        const pretty = res.data.task.status.replace('_', ' ');
                        if (label) {
                            // style based on status (simple)
                            let cls = 'inline-block px-2 py-1 text-xs rounded bg-gray-100 text-gray-800';
                            if (res.data.task.status === 'done') {
                                cls = 'inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800';
                            } else if (res.data.task.status === 'blocked') {
                                cls = 'inline-block px-2 py-1 text-xs rounded bg-red-100 text-red-800';
                            }
                            label.innerHTML = `<span class="${cls}">${pretty}</span>`;
                        }
                    }
                })
                .catch(function(err) {
                    // revert label and select if error
                    alert((err.response && err.response.data && err.response.data.message) ? err.response.data.message : 'Unable to update status. Please try again.');
                    if (label && prevLabelHtml !== null) label.innerHTML = prevLabelHtml;
                    // optional: revert select to previous value by reloading page
                    window.location.reload();
                });
        });
    });
</script>
@endsection