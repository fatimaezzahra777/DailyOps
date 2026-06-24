@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Modifier le projet</p>
                <h1 class="mt-2 text-3xl font-semibold">{{ $project->name }}</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Mets a jour les informations du projet depuis cette fiche.</p>
            </div>

            <form action="{{ route('projects.destroy', $project) }}" method="POST"
                onsubmit="return confirm('Supprimer ce projet ?')" class="self-start">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary">Supprimer</button>
            </form>
        </div>

        <form action="{{ route('projects.update', $project) }}" method="POST" enctype="multipart/form-data" class="panel-dark space-y-5 p-6">
            @csrf
            @method('PUT')

            @include('projects.partials.form', ['project' => $project])

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">Mettre à jour le projet</button>
                <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Voir les détails</a>
                <a href="{{ route('projects.index') }}" class="btn-secondary">Retour au Kanban</a>
            </div>
        </form>
    </section>
@endsection
