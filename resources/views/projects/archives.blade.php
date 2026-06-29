@extends('layouts.app')

@section('content')
    <section>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-md bg-[var(--accent-soft)] text-[var(--accent)]">
                    <i class="ti ti-archive text-xl"></i>
                </span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold text-[var(--text-strong)]">Archived projects</h2>
                    <p class="mt-1 text-[12.5px] text-[var(--muted)]">
                        Projects completed for at least 5 days are archived automatically.
                    </p>
                </div>
            </div>

            <div class="text-[12px] text-[var(--muted)]">{{ $projects->total() }} archived projects</div>
        </div>

        <div class="view-table-wrap">
            <table class="view-table min-w-full">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Assignee</th>
                        <th>Completed at</th>
                        <th>Archived at</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>
                                <div class="font-medium text-[var(--text-strong)]">{{ $project->name }}</div>
                                @if ($project->description)
                                    <div class="mt-1 max-w-[420px] truncate text-[12px] text-[var(--muted)]">
                                        {{ $project->description }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $project->manager?->name ?? $project->assigned_to ?? 'No owner' }}</td>
                            <td>{{ $project->completed_at?->format('d/m/Y H:i') ?? 'Not provided' }}</td>
                            <td>{{ $project->archived_at?->format('d/m/Y H:i') ?? 'Not provided' }}</td>
                            <td>
                                <div class="flex justify-end">
                                    @if ($project->isManagedBy(auth()->user()))
                                        <form action="{{ route('projects.restore', $project) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-secondary" title="Restore project">
                                                <i class="ti ti-restore"></i>
                                                Restaurer
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[12px] text-[var(--muted)]">Consultation uniquement</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <i class="ti ti-archive-off mb-2 text-3xl text-[var(--muted)]"></i>
                                <p class="text-sm font-medium text-[var(--text-strong)]">No archived project</p>
                                <p class="mt-1 text-[12px] text-[var(--muted)]">
                                    Projects will appear here five days after they move to Completed.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($projects->hasPages())
            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        @endif
    </section>
@endsection
