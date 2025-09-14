<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $gradingTotal = Question::where('updated_at', '<', Carbon::now()->subMonth())->count();
        $validationTotal = Question::where('last_validated', '<', $sixMonthsAgo)->count();

        // Monthly quota
        $startOfMonth = Carbon::now()->startOfMonth();
        $questionsThisMonth = Question::where('created_at', '>=', $startOfMonth)->count();
        $monthlyQuota = 50;
        $quotaRemaining = max($monthlyQuota - $questionsThisMonth, 0);

        return view('dashboard.main', compact('gradingTotal', 'validationTotal', 'quotaRemaining', 'monthlyQuota'));
    }
}
