@extends('layouts.app')

@section('content')
    @php
        $navigationQuery = array_filter(request()->only(['search', 'status']), fn ($value) => filled($value));
        $weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'];
        $statusMeta = [
            'pending' => ['label' => 'Brouillon', 'class' => 'status-tag-pending', 'width' => 24, 'offset' => 1],
            'in_progress' => ['label' => 'En cours', 'class' => 'status-tag-progress', 'width' => 52, 'offset' => 2],
            'completed' => ['label' => 'Terminé', 'class' => 'status-tag-completed', 'width' => 92, 'offset' => 1],
        ];
    @endphp

    <section>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[var(--accent)] shadow-[0_0_8px_rgba(197,0,100,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold text-[var(--text-strong)]">Projets - Vue Gantt</h2>
                    <p class="mt-1 text-[12.5px] text-[var(--muted)]">Planning visuel des projets par semaine.</p>
                </div>
            </div>

        </div>

        <div class="view-toolbar">
            <a href="{{ route('projects.index', $navigationQuery) }}" class="btn-secondary"><i class="ti ti-layout-kanban mr-1"></i> Kanban</a>
            <a href="{{ route('projects.table', $navigationQuery) }}" class="btn-secondary"><i class="ti ti-table mr-1"></i> Table</a>
            <span class="ml-auto text-[12px] text-[var(--muted)]">{{ $projects->count() }} projets planifiés</span>
        </div>

        <div class="gantt-shell custom-scroll overflow-x-auto">
            <div class="gantt-grid min-w-[1000px]">
                <div class="gantt-cell gantt-head">Projet</div>
                @foreach ($weeks as $week)
                    <div class="gantt-cell gantt-head">{{ $week }}</div>
                @endforeach

                @forelse ($projects as $project)
                    @php
                        $meta = $statusMeta[$project->status] ?? $statusMeta['pending'];
                        $start = min(($loop->index % 4) + $meta['offset'], 5);
                        $span = $project->status === 'completed' ? 3 : ($project->status === 'in_progress' ? 2 : 1);
                    @endphp
                    <div class="gantt-cell">
                        <div class="font-medium text-[var(--text-strong)]">{{ $project->name }}</div>
                        <div class="mt-1 text-[11px] text-[var(--muted)]">
                            {{ $project->start_date ? $project->start_date->format('d M') : 'Aucun début' }}
                            -
                            {{ $project->end_date ? $project->end_date->format('d M') : 'Aucune fin' }}
                        </div>
                    </div>
                    @for ($i = 1; $i <= 6; $i++)
                        <div class="gantt-cell flex items-center">
                            @if ($i === $start)
                                <div class="gantt-bar" style="width: {{ min($meta['width'], $span * 36) }}%;"></div>
                            @endif
                        </div>
                    @endfor
                @empty
                    <div class="col-span-7 px-6 py-10 text-center text-sm text-[var(--muted)]">Aucun projet pour le moment.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
