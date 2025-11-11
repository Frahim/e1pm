@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white shadow-md rounded p-6">
    <h1 class="text-2xl font-semibold mb-4">Edit Project: {{ $project->name }}</h1>

    @if(session('success'))
        <div class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-red-600">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Project Name --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium mb-1">Project Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $project->name) }}" class="w-full border rounded px-3 py-2" required>
        </div>


        {{-- Status Field (Using a select dropdown) --}}
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium mb-1">Status</label>
            <select id="status" name="status" class="w-full border rounded px-3 py-2" required>
                <option value="" disabled>Select Status</option>
                @php $currentStatus = old('status', $project->status); @endphp
                <option value="Active" {{ $currentStatus == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Pending" {{ $currentStatus == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Completed" {{ $currentStatus == 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Cancelled" {{ $currentStatus == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        {{-- Start and End Date Fields --}}
        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium mb-1">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium mb-1">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        {{-- Client and Candidate Fields --}}
        <div class="mb-4">
            <label for="client-name" class="block text-sm font-medium mb-1">Client Name</label>
            <input type="text" id="client-name" name="client-name" value="{{ old('client-name', $project->{'client-name'}) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label for="candidate-name" class="block text-sm font-medium mb-1">Candidate Name</label>
            <input type="text" id="candidate-name" name="candidate-name" value="{{ old('candidate-name', $project->{'candidate-name'}) }}" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Contact Information --}}
        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $project->phone) }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $project->email) }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        
        {{-- Position Field --}}
        <div class="mb-4">
            <label for="position" class="block text-sm font-medium mb-1">Position</label>
            <input type="text" id="position" name="position" value="{{ old('position', $project->position) }}" class="w-full border rounded px-3 py-2">
        </div>

         {{-- Description --}}
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium mb-1">Description</label>
            <textarea id="description" name="description" rows="3" class="w-full border rounded px-3 py-2">{{ old('description', $project->description) }}</textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center space-x-3 mt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Update Project
            </button>

            <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 border rounded text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection