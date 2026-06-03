<?php

namespace App\Mail;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ProjectInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public string $acceptUrl;

    public string $declineUrl;

    public function __construct(public ProjectInvitation $invitation)
    {
        $this->acceptUrl = URL::temporarySignedRoute(
            'project-invitations.accept',
            now()->addDays(7),
            $invitation,
        );

        $this->declineUrl = URL::temporarySignedRoute(
            'project-invitations.decline',
            now()->addDays(7),
            $invitation,
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation a collaborer sur '.$this->invitation->project->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.project-invitation',
            with: [
                'invitation' => $this->invitation,
                'project' => $this->invitation->project,
                'inviter' => $this->invitation->inviter,
                'acceptUrl' => $this->acceptUrl,
                'declineUrl' => $this->declineUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
