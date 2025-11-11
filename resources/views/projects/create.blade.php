@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white shadow-md rounded p-6">
    <h1 class="text-2xl font-semibold mb-4">Create New Project</h1>

    @if ($errors->any())
        <div class="mb-4 text-red-600">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST">
        @csrf
        {{-- Existing Fields --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium mb-1">Project Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
        </div>      

        
        {{-- Status Field (Using a select dropdown) --}}
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium mb-1">Status</label>
            <select id="status" name="status" class="w-full border rounded px-3 py-2" required>
                <option value="" disabled selected>Select Status</option>
                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        {{-- Date Fields --}}
        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium mb-1">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium mb-1">End Date</label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        {{-- Client and Candidate Fields --}}
        <div class="mb-4">
            <label for="client-name" class="block text-sm font-medium mb-1">Client Name</label>
            <input type="text" id="client-name" name="client-name" value="{{ old('client-name') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label for="candidate-name" class="block text-sm font-medium mb-1">Candidate Name</label>
            <input type="text" id="candidate-name" name="candidate-name" value="{{ old('candidate-name') }}" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Contact Information --}}
        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        
        {{-- Position Field --}}
        <div class="mb-4">
            <label for="position" class="block text-sm font-medium mb-1">Position</label>
            <input type="text" id="position" name="position" value="{{ old('position') }}" class="w-full border rounded px-3 py-2">
        </div>
 <div class="mb-4">
            <label for="description" class="block text-sm font-medium mb-1">Description</label>
            <textarea id="description" name="description" rows="3" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Save Project
        </button>
    </form>
</div>
@endsection