<?php

namespace App\Services\Analytics;

use App\Models\Category;
use App\Models\DashGameQuestionResult;
use App\Models\DashIssue;

class CategoryStats
{
    public function questionCount(int $categoryId): int
    {
        return Category::find($categoryId)?->questions()->count() ?? 0;
    }

    public function appearedCount(int $categoryId): int
    {
        try {
            return (int) DashGameQuestionResult::query()
                ->where('category_id', $categoryId)
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function openIssueCount(int $categoryId): int
    {
        return DashIssue::query()
            ->where('subject_type', 'category')
            ->where('subject_id', $categoryId)
            ->whereIn('status', ['open', 'assigned', 'in_progress'])
            ->count();
    }
}
