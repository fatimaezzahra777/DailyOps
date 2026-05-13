@extends('layouts.app')

@section('content')
    @php
        $statusClasses = [
            'pending' => 'tag-chip tag-chip-violet',
            'in_progress' => 'tag-chip tag-chip-amber',
            'completed' => 'tag-chip tag-chip-emerald',
        ];
    @endphp

    <section class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Project details</p>
                <h1 class="mt-2 text-3xl font-semibold">{{ $project->name }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="{{ $statusClasses[$project->status] ?? 'tag-chip' }}">
                        {{ str($project->status)->replace('_', ' ')->title() }}
                    </span>
                    @if ($project->assigned_to)
                        <span class="tag-chip">{{ $project->assigned_to }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('projects.edit', $project) }}" class="btn-primary">Edit project</a>
                <a href="{{ route('projects.index') }}" class="btn-secondary">Back to board</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.35fr_0.75fr]">
            <article class="panel-dark p-6">
                <h2 class="text-lg font-semibold">Overview</h2>
                <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                    {{ $project->description ?: 'No description has been added for this project yet.' }}
                </p>
            </article>

            <aside class="space-y-4">
                <div class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Timeline</h2>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Start date</dt>
                            <dd class="text-[var(--text-strong)]">{{ $project->start_date?->format('d M Y') ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">End date</dt>
                            <dd class="text-[var(--text-strong)]">{{ $project->end_date?->format('d M Y') ?? 'Not set' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Created at</dt>
                            <dd class="text-[var(--text-strong)]">{{ $project->created_at?->format('d M Y') ?? 'Unknown' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Actions</h2>
                    <div class="mt-4 flex flex-col gap-3">
                        <a href="{{ route('projects.edit', $project) }}" class="btn-primary">Update project</a>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST"
                            onsubmit="return confirm('Delete this project?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary w-full">Delete project</button>
                        </form>
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
