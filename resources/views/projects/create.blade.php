@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-3xl space-y-6">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--muted)]">Create project</p>
            <h1 class="mt-2 text-3xl font-semibold">New project</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">Ajoute un nouveau projet depuis ce formulaire.</p>
        </div>

        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" class="panel-dark space-y-5 p-6">
            @csrf
            @include('projects.partials.form')

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">Save project</button>
                <a href="{{ route('projects.index') }}" class="btn-secondary">Back to board</a>
            </div>
        </form>
    </section>
@endsection
