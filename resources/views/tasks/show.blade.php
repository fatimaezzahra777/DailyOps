@extends('layouts.app')

@section('content')
    @php
        $statusClasses = [
            'todo' => 'tag-chip',
            'in_progress' => 'tag-chip tag-chip-amber',
            'done' => 'tag-chip tag-chip-emerald',
        ];

        $priorityClasses = [
            'low' => 'tag-chip',
            'medium' => 'tag-chip tag-chip-amber',
            'high' => 'tag-chip tag-chip-violet',
        ];

        $assigneeName = $task->assignedUser?->name ?? $task->assigned_to;
        $canManageTask = $task->project?->isManagedBy(auth()->user()) ?? false;
    @endphp

    <section class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Task details</p>
                <h1 class="mt-2 text-3xl font-semibold">{{ $task->title }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="{{ $statusClasses[$task->status] ?? 'tag-chip' }}">{{ str($task->status)->replace('_', ' ')->title() }}</span>
                    <span class="{{ $priorityClasses[$task->priority] ?? 'tag-chip' }}">{{ ucfirst($task->priority) }} priority</span>
                    @if ($assigneeName)
                        <span class="tag-chip">{{ $assigneeName }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                @if ($canManageTask)
                    <a href="{{ route('tasks.edit', $task) }}" class="btn-primary">Edit task</a>
                @endif
                <a href="{{ route('tasks.index') }}" class="btn-secondary">Back to tasks</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="space-y-6">
                <article class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Overview</h2>
                    <p class="mt-4 text-sm leading-7 text-[var(--text)]">
                        {{ $task->description ?: 'No description has been added for this task yet.' }}
                    </p>
                </article>

                <section class="panel-dark p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">Files</h2>
                            <p class="mt-1 text-sm text-[var(--muted)]">Add images, documents, or deliverables related to this task.</p>
                        </div>
                        <span class="tag-chip">{{ $task->attachments->count() }} files</span>
                    </div>

                    <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data"
                        class="mt-6 rounded-2xl border border-dashed border-[var(--line)] bg-[var(--card-soft)] p-4">
                        @csrf
                        <label for="task-attachments" class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl bg-white/70 px-4 py-7 text-center transition hover:bg-white">
                            <span class="material-symbols-rounded text-[34px] text-[#e8007d]">upload_file</span>
                            <span>
                                <span class="block text-sm font-semibold text-[var(--text-strong)]">Glissez vos files ou cliquez ici</span>
                                <span class="mt-1 block text-xs text-[var(--muted)]">Images, PDF, Office, ZIP — max 10 Mo par fichier.</span>
                            </span>
                            <input id="task-attachments" name="attachments[]" type="file" multiple class="sr-only">
                        </label>
                        @error('attachments')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                        @error('attachments.*')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button type="submit" class="btn-primary">Add file</button>
                            <span class="text-xs text-[var(--muted)]">You can select multiple files.</span>
                        </div>
                    </form>

                    <div class="mt-6 grid gap-3 md:grid-cols-2">
                        @forelse ($task->attachments as $attachment)
                            <article class="group rounded-2xl border border-[var(--line)] bg-[var(--card-soft)] p-4 transition hover:border-[#e8007d]/30 hover:bg-white">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-[#e8007d] shadow-sm">
                                        <span class="material-symbols-rounded text-[24px]">
                                            {{ $attachment->isImage() ? 'image' : 'description' }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('task-attachments.download', $attachment) }}"
                                            class="block truncate text-sm font-semibold text-[var(--text-strong)] hover:text-[#e8007d]">
                                            {{ $attachment->original_name }}
                                        </a>
                                        <p class="mt-1 text-xs text-[var(--muted)]">
                                            {{ $attachment->humanSize() }}
                                            @if ($attachment->user)
                                                · added by {{ $attachment->user->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <a href="{{ route('task-attachments.download', $attachment) }}" class="btn-secondary py-2 text-xs">
                                        Download
                                    </a>

                                    @if ($canManageTask || $attachment->user_id === auth()->id())
                                        <form action="{{ route('task-attachments.destroy', $attachment) }}" method="POST"
                                            onsubmit="return confirm('Delete ce fichier ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-rose-400 hover:text-rose-300">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="empty-column-card min-h-32 md:col-span-2">
                                <p>No files for this task. Add the first attachment.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="panel-dark p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">Comments</h2>
                            <p class="mt-1 text-sm text-[var(--muted)]">Keep task context in one place.</p>
                        </div>
                        <span class="tag-chip">{{ $task->comments->count() }} commentaire{{ $task->comments->count() > 1 ? 's' : '' }}</span>
                    </div>

                    <form action="{{ route('comments.store') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="task_id" value="{{ $task->id }}">

                        <div class="grid gap-4 md:grid-cols-[1fr_220px]">
                            <div>
                                <label for="comment-content" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Comment</label>
                                <textarea id="comment-content" name="content" rows="4" class="w-full px-4 py-3"
                                    placeholder="Write a useful update for the team...">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="comment-author" class="mb-2 block text-sm font-medium text-[var(--text-strong)]">Auteur</label>
                                <input id="comment-author" type="text" name="author" class="w-full px-4 py-3"
                                    value="{{ old('author') }}" placeholder="Comment author">
                                @error('author')
                                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="btn-primary">Add comment</button>
                            <a href="{{ route('projects.show', $task->project) }}" class="btn-secondary">View project</a>
                        </div>
                    </form>

                    <div class="mt-8 space-y-4">
                        @forelse ($task->comments as $comment)
                            <article class="rounded-2xl border border-[var(--line)] bg-[var(--card-soft)] p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-[var(--text-strong)]">{{ $comment->author ?: 'Anonyme' }}</span>
                                            <span class="text-xs text-[var(--muted)]">{{ $comment->created_at?->format('d M Y · H:i') }}</span>
                                        </div>
                                        <p class="mt-3 text-sm leading-6 text-[var(--text)]">{{ $comment->content }}</p>
                                    </div>

                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                                        onsubmit="return confirm('Delete ce commentaire ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-300 hover:text-rose-200">Delete</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="empty-column-card min-h-32">
                                <p>No comments yet. Add the first update for this task.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                <div class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Task information</h2>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Project</dt>
                            <dd class="text-right text-[var(--text-strong)]">{{ $task->project?->name ?? 'Unknown project' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Due date</dt>
                            <dd class="text-[var(--text-strong)]">{{ $task->due_date ? \Illuminate\Support\Carbon::parse($task->due_date)->format('d M Y') : 'Not set' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Assignee</dt>
                            <dd class="text-[var(--text-strong)]">{{ $assigneeName ?: 'Unassigned' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[var(--muted)]">Created at</dt>
                            <dd class="text-[var(--text-strong)]">{{ $task->created_at?->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="panel-dark p-6">
                    <h2 class="text-lg font-semibold">Actions rapides</h2>
                    @if ($canManageTask)
                        <div class="mt-4 flex flex-col gap-3">
                            <a href="{{ route('tasks.edit', $task) }}" class="btn-primary">Edit task</a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                onsubmit="return confirm('Delete this task?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary w-full">Delete task</button>
                            </form>
                        </div>
                    @else
                        <p class="mt-4 text-sm leading-6 text-[var(--muted)]">Only the project manager can update or delete this task.</p>
                    @endif
                </div>
            </aside>
        </div>
    </section>
@endsection
