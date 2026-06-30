@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Create task</p>
                <h1 class="mt-2 text-3xl font-semibold">New task</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Create a task and link it to the relevant project.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('tasks.index') }}" class="btn-secondary">Back to tasks</a>
                <a href="{{ route('projects.index') }}" class="btn-secondary">Project board</a>
            </div>
        </div>

        <div class="panel-dark p-6">
            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('tasks.partials.form')

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Create task</button>
                    <a href="{{ route('tasks.index') }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </section>
@endsection
