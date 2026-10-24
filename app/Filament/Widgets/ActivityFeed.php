<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Issues\IssueResource;
use App\Filament\Resources\Operations\AnnouncementResource;
use App\Filament\Resources\Operations\FeatureToggleResource;
use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\WorkQueue\SupportMessageResource;
use App\Models\Announcement;
use App\Models\DashIssueActivity;
use App\Models\FeatureToggle;
use App\Models\InternalNote;
use App\Models\QuestionChange;
use App\Models\User;
use Filament\Widgets\Widget;

/**
 * Unified activity feed: question changes, issue activity, notes, toggle flips, announcements.
 * Replaces the separate RecentActivity and IssueActivityFeed widgets.
 */
class ActivityFeed extends Widget
{
    protected static ?int $sort = 8;

    protected string $view = 'filament.widgets.activity-feed';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $items = collect();

        // Question changes
        QuestionChange::with('user')->latest('id')->limit(10)->get()->each(function ($c) use ($items) {
            $items->push([
                'at'    => $c->created_at,
                'who'   => $c->user?->name,
                'type'  => 'question',
                'label' => $c->action,
                'text'  => 'Question #'.$c->question_id,
                'url'   => QuestionResource::getUrl('edit', ['record' => $c->question_id]),
            ]);
        });

        // Issue activity
        DashIssueActivity::with(['issue', 'user'])->latest('id')->limit(5)->get()->each(function ($a) use ($items) {
            $items->push([
                'at'    => $a->created_at,
                'who'   => $a->user?->name,
                'type'  => 'issue',
                'label' => $a->action,
                'text'  => $a->issue?->reason_summary ?? 'Issue #'.$a->issue_id,
                'url'   => $a->issue_id ? IssueResource::getUrl('view', ['record' => $a->issue_id]) : null,
            ]);
        });

        // Internal notes (staff notes on games, players, messages, pack grants/revokes)
        InternalNote::with('author')->latest()->limit(8)->get()->each(function ($n) use ($items) {
            $isRevoke = str_starts_with($n->note, '[REVOKED]');
            $label = match ($n->note_type) {
                'game'              => 'game note',
                'user'              => 'player note',
                'support_message'   => 'msg note',
                'gamepack_purchase' => $isRevoke ? 'pack revoked' : 'pack granted',
                default             => 'note',
            };
            $text = match ($n->note_type) {
                'game'              => 'Game #'.$n->note_id,
                'user'              => 'Player #'.$n->note_id,
                'support_message'   => 'Message #'.$n->note_id,
                'gamepack_purchase' => 'Purchase #'.$n->note_id,
                default             => ucfirst($n->note_type).' #'.$n->note_id,
            };
            $url = match ($n->note_type) {
                'support_message' => SupportMessageResource::getUrl('view', ['record' => $n->note_id]),
                default           => null,
            };
            $items->push([
                'at'    => $n->created_at,
                'who'   => $n->author?->name,
                'type'  => 'note',
                'label' => $label,
                'text'  => $text,
                'url'   => $url,
            ]);
        });

        // Feature toggle flips (most recently changed)
        $toggles = FeatureToggle::latest('updated_at')->limit(3)->get();
        $toggleUserIds = $toggles->pluck('changed_by')->filter()->unique()->all();
        $toggleUsers = User::whereIn('id', $toggleUserIds)->pluck('name', 'id');
        $toggles->each(function ($t) use ($items, $toggleUsers) {
            $items->push([
                'at'    => $t->updated_at,
                'who'   => $toggleUsers->get($t->changed_by),
                'type'  => 'toggle',
                'label' => $t->enabled ? 'enabled' : 'disabled',
                'text'  => $t->type.' / '.$t->key,
                'url'   => FeatureToggleResource::getUrl('edit', ['record' => $t->id]),
            ]);
        });

        // Announcements (most recently created)
        $announcements = Announcement::latest()->limit(3)->get();
        $annUserIds = $announcements->pluck('created_by')->filter()->unique()->all();
        $annUsers = User::whereIn('id', $annUserIds)->pluck('name', 'id');
        $announcements->each(function ($a) use ($items, $annUsers) {
            $items->push([
                'at'    => $a->created_at,
                'who'   => $annUsers->get($a->created_by),
                'type'  => 'announcement',
                'label' => $a->active ? 'live' : 'draft',
                'text'  => \Illuminate\Support\Str::limit($a->message, 60),
                'url'   => AnnouncementResource::getUrl('edit', ['record' => $a->id]),
            ]);
        });

        return [
            'items' => $items->sortByDesc('at')->take(20)->values(),
        ];
    }
}
