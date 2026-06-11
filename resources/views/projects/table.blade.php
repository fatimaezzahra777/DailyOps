@extends('layouts.app')

@section('content')
    @php
        $queryWithoutStatus = request()->except(['status', 'page']);
        $tableQuery = array_filter(request()->only(['search', 'status', 'company']), fn ($value) => filled($value));
        $statusMeta = [
            'pending' => ['label' => 'Draft', 'class' => 'status-tag-pending', 'progress' => 18, 'priority' => 'bg-[#e8007d]'],
            'in_progress' => ['label' => 'In progress', 'class' => 'status-tag-progress', 'progress' => 64, 'priority' => 'bg-[#d97706]'],
            'completed' => ['label' => 'Completed', 'class' => 'status-tag-completed', 'progress' => 100, 'priority' => 'bg-[#00a86b]'],
        ];
    @endphp

    <section>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[#e8007d] shadow-[0_0_8px_rgba(232,0,125,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold text-[#0a0a0a]">Tasks - Table view</h2>
                    <p class="mt-1 text-[12.5px] text-[#888888]">Vue compacte de tous les projets.</p>
                </div>
            </div>

        </div>

        <div class="view-toolbar">
            <a href="{{ route('projects.table', $queryWithoutStatus) }}" class="filter-pill {{ request('status') ? '' : 'filter-pill-active' }}">All</a>
            <a href="{{ route('projects.table', array_merge($queryWithoutStatus, ['status' => 'pending'])) }}" class="filter-pill {{ request('status') === 'pending' ? 'filter-pill-active' : '' }}">Draft</a>
            <a href="{{ route('projects.table', array_merge($queryWithoutStatus, ['status' => 'in_progress'])) }}" class="filter-pill {{ request('status') === 'in_progress' ? 'filter-pill-active' : '' }}">In progress</a>
            <a href="{{ route('projects.table', array_merge($queryWithoutStatus, ['status' => 'completed'])) }}" class="filter-pill {{ request('status') === 'completed' ? 'filter-pill-active' : '' }}">Completed</a>

            <form method="GET" action="{{ route('projects.table') }}" class="ml-0 sm:ml-2">
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <label for="company-filter" class="sr-only">Filtrer par entreprise</label>
                <select id="company-filter" name="company" class="min-w-44 py-2 pl-3 pr-9 text-xs"
                    onchange="this.form.submit()">
                    <option value="">Toutes les entreprises</option>
                    <option value="softart" @selected(request('company') === 'softart')>SoftArt</option>
                    <option value="company_name" @selected(request('company') === 'company_name')>Company Name</option>
                </select>
            </form>

            <div class="ml-auto text-[12px] text-[#888888]">{{ $allFilteredProjects->count() }} tasks</div>
            <a href="{{ route('projects.index', $tableQuery) }}" class="btn-secondary">
                <i class="ti ti-layout-kanban mr-1"></i>
                Board
            </a>
        </div>

        <div class="view-table-wrap">
            <table class="view-table min-w-full">
                <thead>
                    <tr>
                        <th>Task name <i class="ti ti-chevron-up text-[11px]"></i></th>
                        <th>Entreprise</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Due date</th>
                        <th>Manager</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        @php
                            $meta = $statusMeta[$project->status] ?? $statusMeta['pending'];
                            $managerName = $project->manager?->name ?? $project->assigned_to;
                            $initial = strtoupper(substr($managerName ?: $project->name, 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="project-check"></div>
                                    <div class="priority-dot {{ $meta['priority'] }}"></div>
                                    <div>
                                        <div class="font-medium text-[#0a0a0a]">{{ $project->name }}</div>
                                        @if ($project->description)
                                            <div class="mt-1 max-w-[360px] truncate text-[12px] text-[#888888]">{{ $project->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($project->companyLogo())
                                    <span class="project-company-circle" title="{{ $project->companyLabel() }}">
                                        <img src="{{ asset($project->companyLogo()) }}" alt="{{ $project->companyLabel() }}">
                                    </span>
                                @else
                                    <span class="text-xs text-[var(--muted)]">Non définie</span>
                                @endif
                            </td>
                            <td><span class="status-tag {{ $meta['class'] }}">{{ $meta['label'] }}</span></td>
                            <td>
                                <div class="progress-mini">
                                    <div class="progress-mini-track">
                                        <div class="progress-mini-fill" style="width: {{ $meta['progress'] }}%;"></div>
                                    </div>
                                    <span class="text-[11px] text-[#999999]">{{ $meta['progress'] }}%</span>
                                </div>
                            </td>
                            <td class="{{ $project->end_date && $project->end_date->isPast() && $project->status !== 'completed' ? 'text-[#dc2626]' : '' }}">
                                {{ $project->end_date ? $project->end_date->format('d M') : 'No deadline' }}
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="assignee-dot">{{ $initial }}</div>
                                    <span class="text-[12px] text-[#888888]">{{ $managerName ?: 'No manager' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('projects.show', $project) }}" class="icon-button h-8 w-8 p-0"
                                        aria-label="View project" title="View project">
                                        <span class="material-symbols-rounded text-[18px]">visibility</span>
                                    </a>
                                    <a href="{{ route('projects.edit', $project) }}" class="icon-button h-8 w-8 p-0"
                                        aria-label="Edit project" title="Edit project">
                                        <span class="material-symbols-rounded text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                        onsubmit="return confirm('Delete this project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-button h-8 w-8 p-0"
                                            aria-label="Delete project" title="Delete project">
                                            <span class="material-symbols-rounded text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-[#888888]">Aucun projet trouve.</td>
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
