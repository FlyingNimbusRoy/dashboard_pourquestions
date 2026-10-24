<?php

namespace App\Notifications;

use App\Filament\Resources\Issues\IssueResource;
use App\Models\DashIssue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DashIssueAssigned extends Notification
{
    public function __construct(private readonly DashIssue $issue) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->issue->reason_summary ?: $this->issue->reason_code;
        $url = IssueResource::getUrl('view', ['record' => $this->issue->id]);

        return (new MailMessage)
            ->subject("Issue assigned to you: {$subject}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A {$this->issue->type} issue has been assigned to you.")
            ->line("**{$subject}**")
            ->when($this->issue->subject_type && $this->issue->subject_id, fn ($m) =>
                $m->line("Subject: {$this->issue->subject_type} #{$this->issue->subject_id}")
            )
            ->action('View issue', $url)
            ->line('You can assign, update, or resolve it from the dashboard.');
    }
}
