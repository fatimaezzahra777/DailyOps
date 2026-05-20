@include('tasks.partials.table')

@include('tasks.partials.modals', ['tasks' => $tasks, 'projects' => $projects, 'openModal' => $openModal ?? session('open_modal')])
