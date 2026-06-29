@extends('layouts.app')

@section('content')
    @php
        $selectedFolderName = $selectedFolder
            ? $folders->firstWhere('slug', $selectedFolder)?->name
            : null;
    @endphp

    <section class="space-y-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="kanban-eyebrow">File manager</p>
                <h1 class="font-['Syne'] text-3xl font-extrabold text-[var(--text-strong)]">Files</h1>
                <p class="mt-2 max-w-2xl text-sm text-[var(--muted)]">
                    All task attachments, automatically grouped by file type.
                </p>
            </div>

            <a href="{{ route('tasks.index') }}" class="btn-primary inline-flex items-center gap-2">
                <span class="material-symbols-rounded text-[18px]">upload_file</span>
                Upload from a task
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($folders as $folder)
                @php($isActive = $selectedFolder === $folder->slug)
                <a href="{{ route('creadations.index', ['folder' => $folder->slug]) }}"
                    class="asset-folder-card {{ $isActive ? 'asset-folder-card-active' : '' }}"
                    style="--folder-color: {{ $folder->color }}; animation-delay: {{ $loop->index * 80 }}ms;">
                    <span class="asset-folder-icon">
                        <span class="material-symbols-rounded text-[25px]">{{ $folder->icon }}</span>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-base font-bold text-[var(--text-strong)]">{{ $folder->name }}</span>
                        <span class="mt-1 block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--muted)]">
                            {{ $folder->files_count }} files
                        </span>
                    </span>
                    <span class="material-symbols-rounded text-[20px] text-[var(--muted)] transition group-hover:translate-x-1">chevron_right</span>
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-['Syne'] text-lg font-bold text-[var(--text-strong)]">
                    {{ $selectedFolderName ? "Files {$selectedFolderName}" : 'Recent files' }}
                </h2>
                <p class="mt-1 text-xs text-[var(--muted)]">
                    {{ $selectedFolderName ? $attachments->count().' files in this folder' : $totalAttachments.' files total' }}
                </p>
            </div>

            @if ($selectedFolder)
                <a href="{{ route('creadations.index') }}" class="text-xs font-bold uppercase tracking-[0.12em] text-[#c50064] hover:text-[#9f0050]">
                    View tout
                </a>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
            @forelse ($attachments as $attachment)
                <article class="asset-file-card" style="animation-delay: {{ $loop->index * 65 }}ms;">
                    <div class="asset-file-preview">
                        @if ($attachment->isImage())
                            <img src="{{ route('task-attachments.preview', $attachment) }}" alt="{{ $attachment->original_name }}">
                        @else
                            <span class="material-symbols-rounded text-[50px] text-[#c50064]">
                                {{ $attachment->categorySlug() === 'videos' ? 'play_circle' : ($attachment->categorySlug() === 'archives' ? 'folder_zip' : 'description') }}
                            </span>
                        @endif

                        <span class="asset-file-extension">{{ $attachment->extension() }}</span>
                    </div>

                    <div class="p-4">
                        <a href="{{ route('task-attachments.download', $attachment) }}"
                            class="block truncate text-sm font-bold text-[var(--text-strong)] hover:text-[#c50064]"
                            title="{{ $attachment->original_name }}">
                            {{ $attachment->original_name }}
                        </a>
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs text-[var(--muted)]">
                            <span>{{ $attachment->humanSize() }}</span>
                            <span>{{ $attachment->created_at?->format('d M Y') }}</span>
                        </div>

                        <div class="mt-3 border-t border-[var(--line)] pt-3 text-xs text-[var(--muted)]">
                            <p class="truncate">Project : {{ $attachment->task?->project?->name ?? 'Not set' }}</p>
                            <p class="mt-1 truncate">Task : {{ $attachment->task?->title ?? 'Not set' }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-column-card min-h-52 sm:col-span-2 lg:col-span-3 2xl:col-span-5">
                    <p>No file found. Add attachments from a task detail page.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
