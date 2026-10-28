<?php

namespace App\Observers;

use App\Models\DashIssue;
use App\Models\User;
use App\Notifications\DashIssueAssigned;

class DashIssueObserver
{
    public function created(DashIssue $issue): void
    {
        $this->notifyAssignee($issue);
    }

    public function updated(DashIssue $issue): void
    {
        if (! $issue->wasChanged('assignee_id')) {
            return;
        }

        $this->notifyAssignee($issue);
    }

    private function notifyAssignee(DashIssue $issue): void
    {
        if ($issue->assignee_id === null) {
            return;
        }

        $assignee = User::find($issue->assignee_id);
        if ($assignee === null) {
            return;
        }

        // Don't notify the person who just assigned it to themselves.
        if ($assignee->id === auth()->id()) {
            return;
        }

        $assignee->notify(new DashIssueAssigned($issue));
    }
}
