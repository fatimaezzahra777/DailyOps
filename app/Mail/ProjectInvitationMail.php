<?php

namespace App\Mail;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ProjectInvitation $invitation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation au projet '.$this->invitation->project->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.projects.invitation',
            with: [
                'project' => $this->invitation->project,
                'manager' => $this->invitation->invitedBy,
                'acceptUrl' => route('project-invitations.accept', $this->invitation->token),
                'declineUrl' => route('project-invitations.decline', $this->invitation->token),
            ],
        );
    }
}
