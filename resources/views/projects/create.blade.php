@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Create task</p>
            <h1 class="mt-2 text-3xl font-semibold">New project card</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">Ajoute une nouvelle carte avec le même style que le board principal.</p>
        </div>

        <form action="{{ route('projects.store') }}" method="POST" class="panel-dark space-y-5 p-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Name</label>
                    <input id="name" name="name" type="text" class="w-full px-4 py-3" required>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-3"></textarea>
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-3">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div>
                    <label for="assigned_to" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Assigned to</label>
                    <input id="assigned_to" name="assigned_to" type="text" class="w-full px-4 py-3">
                </div>

                <div>
                    <label for="start_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Start date</label>
                    <input id="start_date" name="start_date" type="date" class="w-full px-4 py-3">
                </div>

                <div>
                    <label for="end_date" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">End date</label>
                    <input id="end_date" name="end_date" type="date" class="w-full px-4 py-3">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">Save project</button>
                <a href="{{ route('projects.index') }}" class="btn-secondary">Back to board</a>
            </div>
        </form>
    </section>
@endsection
