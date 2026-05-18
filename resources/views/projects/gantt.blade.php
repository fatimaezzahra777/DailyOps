@extends('layouts.app')

@section('content')
    @php
        $weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'];
        $statusMeta = [
            'pending' => ['label' => 'Draft', 'class' => 'status-tag-pending', 'width' => 24, 'offset' => 1],
            'in_progress' => ['label' => 'In progress', 'class' => 'status-tag-progress', 'width' => 52, 'offset' => 2],
            'completed' => ['label' => 'Completed', 'class' => 'status-tag-completed', 'width' => 92, 'offset' => 1],
        ];
    @endphp

    <section>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[#e8007d] shadow-[0_0_8px_rgba(232,0,125,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold text-[#0a0a0a]">Projects - Gantt view</h2>
                    <p class="mt-1 text-[12.5px] text-[#888888]">Planning visuel des projets par semaine.</p>
                </div>
            </div>
            <a href="{{ route('projects.create') }}" class="btn-primary">
                <i class="ti ti-plus"></i>
                Add project
            </a>
        </div>

        <div class="view-toolbar">
            <a href="{{ route('projects.index') }}" class="btn-secondary"><i class="ti ti-layout-kanban mr-1"></i> Board</a>
            <a href="{{ route('projects.table') }}" class="btn-secondary"><i class="ti ti-table mr-1"></i> Table</a>
            <span class="ml-auto text-[12px] text-[#888888]">{{ $projects->count() }} scheduled projects</span>
        </div>

        <div class="gantt-shell custom-scroll overflow-x-auto">
            <div class="gantt-grid min-w-[1000px]">
                <div class="gantt-cell gantt-head">Project</div>
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
                        <div class="font-medium text-[#0a0a0a]">{{ $project->name }}</div>
                        <div class="mt-1 text-[11px] text-[#888888]">
                            {{ $project->start_date ? $project->start_date->format('d M') : 'No start' }}
                            -
                            {{ $project->end_date ? $project->end_date->format('d M') : 'No end' }}
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
                    <div class="col-span-7 px-6 py-10 text-center text-sm text-[#888888]">Aucun projet pour le moment.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
