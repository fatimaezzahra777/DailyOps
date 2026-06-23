<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project,public string $previousStatus,) 
    {
        
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mise à jour de votre projet : '.$this->project->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.projects.status-updated',
        );
    }
}
