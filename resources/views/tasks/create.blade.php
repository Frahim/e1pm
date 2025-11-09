@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Create Task for: {{ $project->name }}</h2>

    <form action="{{ route('projects.tasks.store', $project) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="block text-sm">Title</label>
            <input name="title" value="{{ old('title') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-sm">Assignee</label>
                <select name="assignee_id" class="w-full border rounded px-2 py-1">
                    <option value="">Unassigned</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm">Priority</label>
                <select name="priority" class="w-full border rounded px-2 py-1">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm">Due date</label>
            <input type="date" name="due_at" value="{{ old('due_at') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex items-center space-x-3">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Create Task</button>
            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection
