<?php

namespace App\Http\Controllers;

use App\Models\Creadation;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreadationController extends Controller
{
    public function index(Request $request): View
    {
        $folders = Creadation::query()
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $attachments = TaskAttachment::query()
            ->with(['task.project', 'user'])
            ->whereHas('task.project', fn ($query) => $query->visibleTo($request->user()))
            ->latest()
            ->get();

        $groupedAttachments = $attachments->groupBy(fn (TaskAttachment $attachment) => $attachment->categorySlug());

        $folders = $folders->map(function (Creadation $folder) use ($groupedAttachments) {
            $folder->setAttribute('files_count', $groupedAttachments->get($folder->slug, collect())->count());

            return $folder;
        });

        $selectedFolder = $request->query('folder');
        $visibleAttachments = $selectedFolder
            ? $groupedAttachments->get($selectedFolder, collect())
            : $attachments->take(12);

        return view('creadations.index', [
            'folders' => $folders,
            'attachments' => $visibleAttachments,
            'selectedFolder' => $selectedFolder,
            'totalAttachments' => $attachments->count(),
        ]);
    }
}
