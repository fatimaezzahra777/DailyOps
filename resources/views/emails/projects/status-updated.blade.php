@component('mail::message')
# Your project is moving forward stage by stage

Hello,

Project **{{ $project->name }}** has just changed stage.

@component('mail::panel')
Previous stage : **{{ \App\Models\Project::statusLabel($previousStatus) }}**  
New stage : **{{ \App\Models\Project::statusLabel($project->status) }}**
@endcomponent

Our team continues to monitor progress and move the project forward under the best conditions.

Thanks,  
The DailyOps team
@endcomponent
