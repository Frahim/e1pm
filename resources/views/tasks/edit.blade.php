@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Edit Task: {{ $task->title }}</h2>

    <form action="{{ route('tasks.update', $task) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="block text-sm">Title</label>
            <input name="title" value="{{ old('title', $task->title) }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded px-3 py-2">{{ old('description', $task->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-sm">Assignee</label>
                <select name="assignee_id" class="w-full border rounded px-2 py-1">
                    <option value="">Unassigned</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}" @selected($task->assignee_id == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm">Priority</label>
                <select name="priority" class="w-full border rounded px-2 py-1">
                    <option value="low" @selected($task->priority=='low')>Low</option>
                    <option value="medium" @selected($task->priority=='medium')>Medium</option>
                    <option value="high" @selected($task->priority=='high')>High</option>
                    <option value="urgent" @selected($task->priority=='urgent')>Urgent</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm">Status</label>
            <select name="status" class="w-full border rounded px-2 py-1">
                <option value="todo" @selected($task->status=='todo')>To Do</option>
                <option value="in_progress" @selected($task->status=='in_progress')>In Progress</option>
                <option value="review" @selected($task->status=='review')>Review</option>
                <option value="done" @selected($task->status=='done')>Done</option>
                <option value="blocked" @selected($task->status=='blocked')>Blocked</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="block text-sm">Due date</label>
            <input type="date" name="due_at" value="{{ optional($task->due_at)->format('Y-m-d') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex items-center space-x-3">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Update Task</button>
            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection
