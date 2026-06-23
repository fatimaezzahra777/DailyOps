@component('mail::message')
# Votre projet avance étape par étape

Bonjour,

Le projet **{{ $project->name }}** vient de changer d'étape.

@component('mail::panel')
Ancienne étape : **{{ \App\Models\Project::statusLabel($previousStatus) }}**  
Nouvelle étape : **{{ \App\Models\Project::statusLabel($project->status) }}**
@endcomponent

Notre équipe continue le suivi afin de faire avancer le projet dans les meilleures conditions.

Merci,  
L'équipe DailyOps
@endcomponent
