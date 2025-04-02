<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get monthly expenses data for the chart
        $monthlyExpenses = $user->expenses()
            ->select(DB::raw('MONTH(date) as month'), DB::raw('YEAR(date) as year'), DB::raw('SUM(amount) as total'))
            ->whereYear('date', date('Y'))
            ->groupBy('month', 'year')
            ->orderBy('month')
            ->get();

        // Get expenses by category for the pie chart
        $expensesByCategory = $user->expenses()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        // Get total expenses for the current month
        $currentMonthTotal = $user->expenses()
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('amount');

        // Get expenses for the last 6 months
        $lastSixMonths = $user->expenses()
            ->where('date', '>=', now()->subMonths(6))
            ->orderBy('date')
            ->get();

        return view('dashboard', compact(
            'monthlyExpenses',
            'expensesByCategory',
            'currentMonthTotal',
            'lastSixMonths'
        ));
    }
}
