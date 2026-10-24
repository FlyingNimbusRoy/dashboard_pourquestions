<?php

namespace Tests\Feature;

use App\Models\DashIssue;
use App\Models\User;
use App\Notifications\DashIssueAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class IssueAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeIssue(array $extra = []): DashIssue
    {
        return DashIssue::create([
            'type'           => 'content',
            'subject_type'   => 'question',
            'subject_id'     => 1,
            'reason_code'    => 'test_code',
            'reason_summary' => 'Test reason',
            'status'         => 'open',
            ...$extra,
        ]);
    }

    public function test_assigning_issue_to_another_user_sends_notification(): void
    {
        Notification::fake();

        $actor    = User::factory()->admin()->create();
        $assignee = User::factory()->admin()->create();
        $issue    = $this->makeIssue();

        $this->actingAs($actor);
        $issue->update(['assignee_id' => $assignee->id]);

        Notification::assertSentTo($assignee, DashIssueAssigned::class);
        Notification::assertNotSentTo($actor, DashIssueAssigned::class);
    }

    public function test_self_assignment_sends_no_notification(): void
    {
        Notification::fake();

        $actor = User::factory()->admin()->create();
        $issue = $this->makeIssue();

        $this->actingAs($actor);
        $issue->update(['assignee_id' => $actor->id]);

        Notification::assertNothingSent();
    }

    public function test_setting_assignee_to_null_sends_no_notification(): void
    {
        Notification::fake();

        $actor    = User::factory()->admin()->create();
        $assignee = User::factory()->admin()->create();
        $issue    = $this->makeIssue(['assignee_id' => $assignee->id]);

        $this->actingAs($actor);
        $issue->update(['assignee_id' => null]);

        Notification::assertNothingSent();
    }

    public function test_updating_other_fields_sends_no_notification(): void
    {
        Notification::fake();

        $actor    = User::factory()->admin()->create();
        $assignee = User::factory()->admin()->create();
        $issue    = $this->makeIssue(['assignee_id' => $assignee->id]);

        $this->actingAs($actor);
        $issue->update(['status' => 'in_progress']);

        Notification::assertNothingSent();
    }
}
