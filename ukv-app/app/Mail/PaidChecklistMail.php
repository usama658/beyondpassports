<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ChecklistRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class PaidChecklistMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ChecklistRequest $request) {}

    public function envelope(): Envelope
    {
        $dest = $this->request->destination?->name ?? 'your trip';

        return new Envelope(subject: "Your document checklist for {$dest}");
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Your full document checklist is ready and saved here: '
                .'<a href="'.url('/checklist/'.$this->request->token).'">view it any time</a>.</p>'
                .'<p>Independent service — not a government website. Service fee is separate from any government fee.</p>'
        );
    }
}
