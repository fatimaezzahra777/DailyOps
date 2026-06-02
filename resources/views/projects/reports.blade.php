@extends('layouts.app')

@section('content')
    @php
        $navigationQuery = array_filter(request()->only(['search', 'status']), fn ($value) => filled($value));
        $total = max($projects->count(), 1);
        $completed = $projects->where('status', 'completed')->count();
        $inProgress = $projects->where('status', 'in_progress')->count();
        $pending = $projects->where('status', 'pending')->count();
        $overdue = $projects
            ->filter(fn ($project) => $project->end_date && $project->end_date->isPast() && $project->status !== 'completed')
            ->count();
        $rows = [
            ['label' => 'Completed', 'value' => $completed, 'color' => '#00a86b'],
            ['label' => 'In progress', 'value' => $inProgress, 'color' => '#d97706'],
            ['label' => 'Draft', 'value' => $pending, 'color' => '#e8007d'],
            ['label' => 'Overdue', 'value' => $overdue, 'color' => '#dc2626'],
        ];
    @endphp

    <section>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[#e8007d] shadow-[0_0_8px_rgba(232,0,125,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold text-[#0a0a0a]">Projects - Reports</h2>
                    <p class="mt-1 text-[12.5px] text-[#888888]">Synthese du workspace.</p>
                </div>
            </div>
            <a href="{{ route('projects.table', $navigationQuery) }}" class="btn-secondary">
                <i class="ti ti-table mr-1"></i>
                Open table
            </a>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="metric-card metric-card-featured">
                <p class="metric-label">Total projects</p>
                <p class="metric-value mt-3">{{ $projects->count() }}</p>
                <p class="mt-2 text-xs text-[#888888]">All workspace projects</p>
            </article>
            <article class="metric-card">
                <p class="metric-label">Completed</p>
                <p class="metric-value mt-3">{{ $completed }}</p>
                <p class="mt-2 text-xs text-[#00a86b]">{{ round(($completed / $total) * 100) }}% of total</p>
            </article>
            <article class="metric-card">
                <p class="metric-label">In progress</p>
                <p class="metric-value mt-3">{{ $inProgress }}</p>
                <p class="mt-2 text-xs text-[#d97706]">Active work</p>
            </article>
            <article class="metric-card">
                <p class="metric-label">Overdue</p>
                <p class="metric-value mt-3">{{ $overdue }}</p>
                <p class="mt-2 text-xs text-[#dc2626]">Needs attention</p>
            </article>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
            <article class="report-card p-5 lg:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="font-['Syne'] text-[14px] font-bold text-[#0a0a0a]">Status breakdown</h3>
                        <p class="mt-1 text-[12.5px] text-[#888888]">Distribution des projets par statut.</p>
                    </div>
                    <i class="ti ti-chart-bar text-xl text-[#e8007d]"></i>
                </div>

                <div class="space-y-4">
                    @foreach ($rows as $row)
                        @php $percent = round(($row['value'] / $total) * 100); @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between text-[13px]">
                                <span class="font-medium text-[#555555]">{{ $row['label'] }}</span>
                                <span class="font-['Syne'] font-semibold text-[#0a0a0a]">{{ $percent }}%</span>
                            </div>
                            <div class="report-meter">
                                <div class="report-meter-fill" style="width: {{ $percent }}%; background: {{ $row['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="report-card p-5">
                <h3 class="font-['Syne'] text-[14px] font-bold text-[#0a0a0a]">Recent activity</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($projects->take(5) as $project)
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between rounded-md border border-black/10 px-3 py-3 text-[13px] transition hover:border-[#e8007d]/20 hover:bg-[#e8007d]/10">
                            <span class="truncate text-[#555555]">{{ $project->name }}</span>
                            <span class="ml-3 text-[11px] text-[#999999]">{{ $project->created_at->format('d M') }}</span>
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm text-[#888888]">Aucune activite.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
@endsection
