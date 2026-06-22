@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Modifier la tâche</p>
                <h1 class="mt-2 text-3xl font-semibold">{{ $task->title }}</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Modifiez le statut, l’échéance, le responsable et la description de la tâche.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('tasks.show', $task) }}" class="btn-secondary">Voir les détails</a>
                <a href="{{ route('tasks.index') }}" class="btn-secondary">Retour aux tâches</a>
            </div>
        </div>

        <div class="panel-dark p-6">
            <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                @include('tasks.partials.form')

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Mettre à jour la tâche</button>
                    <a href="{{ route('tasks.show', $task) }}" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </section>
@endsection
