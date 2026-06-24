@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Créer une tâche</p>
                <h1 class="mt-2 text-3xl font-semibold">Nouvelle tâche</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Créez une tâche et associez-la au projet concerné.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('tasks.index') }}" class="btn-secondary">Retour aux tâches</a>
                <a href="{{ route('projects.index') }}" class="btn-secondary">Tableau des projets</a>
            </div>
        </div>

        <div class="panel-dark p-6">
            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('tasks.partials.form')

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Créer une tâche</button>
                    <a href="{{ route('tasks.index') }}" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </section>
@endsection
