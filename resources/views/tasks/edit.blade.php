@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Edit task</p>
                <h1 class="mt-2 text-3xl font-semibold">{{ $task->title }}</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Update the task status, due date, assignee, and description.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('tasks.show', $task) }}" class="btn-secondary">View details</a>
                <a href="{{ route('tasks.index') }}" class="btn-secondary">Back to tasks</a>
            </div>
        </div>

        <div class="panel-dark p-6">
            <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                @include('tasks.partials.form')

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Update task</button>
                    <a href="{{ route('tasks.show', $task) }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </section>
@endsection
