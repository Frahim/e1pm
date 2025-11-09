@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Your Projects</h1>

        <div class="flex items-center space-x-2">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-indigo-700">
                + Create New Project
            </a>
        </div>
    </div>

    @if($projects->isEmpty())
    <div class="bg-white shadow rounded p-6 text-center">
        <p class="text-gray-600">You don't have any projects yet.</p>
        <a href="{{ route('projects.create') }}" class="mt-4 inline-block text-indigo-600 hover:underline">Create your first project</a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($projects as $project)
        <div class="bg-white shadow-sm rounded-lg overflow-hidden border">
            <div class="p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <a href="{{ route('projects.show', $project) }}" class="text-lg font-semibold text-gray-900 hover:text-indigo-600">
                            {{ \Illuminate\Support\Str::limit($project->name, 50) }}
                        </a>
                        <p class="text-sm text-gray-500 mt-1">
                            Owner: {{ $project->owner->name ?? '—' }}
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="badge inline-flex items-center rounded-md  px-2 py-1 text-xs font-medium  inset-ring 
            @if($project->status == 'active') active            
            @elseif($project->status == 'completed') completed 
            @else bg-secondary @endif">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mt-3 line-clamp-3">
                    {{ $project->description ?? 'No description provided.' }}
                </p>

                <div class="mt-4 flex items-center space-x-2">
                    @can('view', $project)
                    <a href="{{ route('projects.show', $project) }}" class="px-3 py-1.5 text-sm bg-gray-50 border rounded text-gray-700 hover:bg-gray-100">View</a>
                    @endcan

                    @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 text-sm bg-blue-50 border rounded text-blue-700 hover:bg-blue-100">Edit</a>
                    @endcan
                    @can('delete', $project)
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project?');" class="inline-block ml-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm shadow-sm hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                    @endcan
                </div>
                <div class="mt-4 flex items-center space-x-2">
                    
 @can('update', $project)
                    <form action="{{ route('projects.updateStatus', $project) }}" method="POST" style="display:inline-block; margin-left:10px;">
                        @csrf
                        @method('PATCH')
                        <label>Change Status</label>
                        <select name="status" onchange="this.form.submit()" class="form-select form-select-sm d-inline-block" style="width:auto;">
                            <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>Active</option>                          
                            <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="archived" {{ $project->status == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </form>
                    @endcan
                </div>
            </div>
            <div class="border-t px-4 py-2 bg-gray-50 text-xs text-gray-500 flex items-center justify-between">
                <span>Members: {{ $project->members()->count() + 1 }}</span>
                <span>Created: {{ $project->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection