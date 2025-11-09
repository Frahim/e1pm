@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-lg font-semibold">Tasks for {{ $project->name }}</h2>

    <a href="{{ route('projects.tasks.create', $project) }}" class="inline-block mt-3 mb-4 px-3 py-2 bg-green-600 text-white rounded">Create Task</a>

    <table class="w-full table-auto">
        <thead class="text-left text-sm text-gray-600">
            <tr><th>Title</th><th>Assignee</th><th>Priority</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($tasks as $t)
                <tr class="border-t">
                    <td><a href="{{ route('tasks.show', $t) }}">{{ $t->title }}</a></td>
                    <td>{{ $t->assignee->name ?? '—' }}</td>
                    <td>{{ ucfirst($t->priority) }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$t->status)) }}</td>
                    <td>
                        <a href="{{ route('tasks.edit', $t) }}" class="text-sm text-blue-600">Edit</a>
                        <form action="{{ route('tasks.destroy', $t) }}" method="POST" class="inline-block ms-2">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete task?')" class="text-sm text-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
