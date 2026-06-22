@extends('layouts.app')

@section('content')
    @php
        $queryWithoutStatus = request()->except(['status', 'page']);
        $statusMeta = [
            'pending' => ['label' => 'Brouillon', 'class' => 'status-tag-pending', 'progress' => 18, 'priority' => 'bg-[#c50064]'],
            'in_progress' => ['label' => 'En cours', 'class' => 'status-tag-progress', 'progress' => 64, 'priority' => 'bg-[#d97706]'],
            'completed' => ['label' => 'Terminé', 'class' => 'status-tag-completed', 'progress' => 100, 'priority' => 'bg-[#00a86b]'],
        ];
    @endphp

    <section>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-[#c50064] shadow-[0_0_8px_rgba(197,0,100,0.5)]"></span>
                <div>
                    <h2 class="font-['Syne'] text-base font-bold text-[#0a0a0a]">Tâches - Vue tableau</h2>
                    <p class="mt-1 text-[12.5px] text-[#888888]">Vue compacte de tous les projets.</p>
                </div>
            </div>

        </div>

        <div class="view-toolbar">
            <a href="{{ route('projects.table', $queryWithoutStatus) }}" class="filter-pill {{ request('status') ? '' : 'filter-pill-active' }}">Tous</a>
            <a href="{{ route('projects.table', array_merge($queryWithoutStatus, ['status' => 'pending'])) }}" class="filter-pill {{ request('status') === 'pending' ? 'filter-pill-active' : '' }}">Brouillon</a>
            <a href="{{ route('projects.table', array_merge($queryWithoutStatus, ['status' => 'in_progress'])) }}" class="filter-pill {{ request('status') === 'in_progress' ? 'filter-pill-active' : '' }}">En cours</a>
            <a href="{{ route('projects.table', array_merge($queryWithoutStatus, ['status' => 'completed'])) }}" class="filter-pill {{ request('status') === 'completed' ? 'filter-pill-active' : '' }}">Terminé</a>
            <div class="ml-auto text-[12px] text-[#888888]">{{ $allFilteredProjects->count() }} projets</div>
            <a href="{{ route('projects.index', request()->only(['search', 'status'])) }}" class="btn-secondary">
                <i class="ti ti-layout-kanban mr-1"></i>
                Kanban
            </a>
        </div>

        <div class="view-table-wrap">
            <table class="view-table min-w-full">
                <thead>
                    <tr>
                        <th>Nom du projet <i class="ti ti-chevron-up text-[11px]"></i></th>
                        <th>Colonne</th>
                        <th>Statut</th>
                        <th>Progression</th>
                        <th>Date d’échéance</th>
                        <th>Responsable</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        @php
                            $meta = $statusMeta[$project->status] ?? $statusMeta['pending'];
                            $managerName = $project->manager?->name ?? $project->assigned_to;
                            $initial = strtoupper(substr($managerName ?: $project->name, 0, 1));
                            $column = match ($project->status) {
                                'completed' => 'Validation',
                                'in_progress' => 'Production',
                                default => 'Préparation',
                            };
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
                            <td>{{ $column }}</td>
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
                                {{ $project->end_date ? $project->end_date->format('d M') : 'Aucune échéance' }}
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="assignee-dot">{{ $initial }}</div>
                                    <span class="text-[12px] text-[#888888]">{{ $managerName ?: 'Aucun responsable' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('projects.show', $project) }}" class="icon-button h-8 w-8 p-0"
                                        aria-label="Voir le projet" title="Voir le projet">
                                        <span class="material-symbols-rounded text-[18px]">visibility</span>
                                    </a>
                                    <a href="{{ route('projects.edit', $project) }}" class="icon-button h-8 w-8 p-0"
                                        aria-label="Modifier le projet" title="Modifier le projet">
                                        <span class="material-symbols-rounded text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                        onsubmit="return confirm('Supprimer ce projet ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-button h-8 w-8 p-0"
                                            aria-label="Supprimer le projet" title="Supprimer le projet">
                                            <span class="material-symbols-rounded text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-[#888888]">Aucun projet trouvé.</td>
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
