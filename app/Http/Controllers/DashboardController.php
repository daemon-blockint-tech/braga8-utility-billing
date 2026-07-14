<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\UtilityMeter;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Complaint;
use App\Models\MeterReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index() {
        $totalTenants = Tenant::count();
        $newTenantsThisMonth = Tenant::whereMonth('created_at', Carbon::now()->month)->count();
        $totalInvoiceValue = Invoice::sum('total_amount');
        $totalPaidAmount = Payment::where('status', 'verified')->sum('amount_paid');
        $paidCount = Payment::where('status', 'verified')->count();
        $totalUnpaidAmount = max($totalInvoiceValue - $totalPaidAmount, 0);
        $unpaidCount = Invoice::whereDoesntHave('payments', function ($query) {
            $query->where('status', 'verified');
        })->count();

        $baseValue = max($totalInvoiceValue, 1);
        $percentPaid = round(($totalPaidAmount / $baseValue) * 100);
        $percentUnpaid = 100 - $percentPaid;

        $overdueCount = Invoice::where('created_at', '<', Carbon::now()->subDays(30))
            ->whereDoesntHave('payments', function ($query) {
                $query->where('status', 'verified');
            })->count();

        $totalInvoicesCount = max(Invoice::count(), 1);
        $percentOverdue = round(($overdueCount / $totalInvoicesCount) * 100);

        $totalMeters = UtilityMeter::count();
        $metersDone = MeterReading::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $metersRemaining = max($totalMeters - $metersDone, 0);

        $unitsCompleted = DB::table('meter_readings')
            ->join('utility_meters', 'meter_readings.meter_id', '=', 'utility_meters.id')
            ->whereMonth('meter_readings.created_at', Carbon::now()->month)
            ->whereYear('meter_readings.created_at', Carbon::now()->year)
            ->select('utility_meters.unit_id')
            ->groupBy('utility_meters.unit_id')
            ->having(DB::raw('count(*)'), '>=', 2)
            ->get()
            ->count();

        $reports = \App\Models\UsageReport::orderBy('month_year', 'asc')->take(6)->get();
        $chartData = [];

        foreach ($reports as $report) {
            $chartData[] = [
                'month' => Carbon::parse($report->month_year)->format('M'),
                'electricity' => $report->total_electric_usage ?? 0, 
                'water' => $report->total_water_usage ?? 0,
            ];
        }

        $allValues = $reports->flatMap(fn($r) => [
            $r->total_electric_usage ?? 0, 
            $r->total_water_usage ?? 0
        ]);

        $maxVal = max($allValues->max() ?? 100, 1);

        return view('dashboard', [
            'totalTenants' => $totalTenants,
            'newTenantsThisMonth' => $newTenantsThisMonth,
            'totalPaidAmount' => $totalPaidAmount,
            'paidCount' => $paidCount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'unpaidCount' => $unpaidCount,
            'totalComplaints' => Complaint::where('status', '!=', 'resolved')->count(),
            'percentPaid' => $percentPaid,
            'percentUnpaid' => $percentUnpaid,
            'percentOverdue' => min($percentOverdue, 100),
            'metersDone' => $metersDone,
            'metersRemaining' => $metersRemaining,
            'totalMeters' => $totalMeters,
            'unitsCompleted' => $unitsCompleted,
            'totalUnits' => Unit::count(),
            'chartData' => $chartData,
            'maxVal' => $maxVal,
        ]);
    }

    public function getProgressData()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $totalMeters = UtilityMeter::count();

        $completedReadings = MeterReading::whereMonth('recorded_at', $currentMonth)
            ->whereYear('recorded_at', $currentYear)
            ->distinct('meter_id')
            ->count();

        return response()->json([
            'total_meters' => $totalMeters,
            'completed_meters' => $completedReadings,
            'percentage' => $totalMeters > 0 ? ($completedReadings / $totalMeters) : 0,
            'month_name' => now()->format('F')
        ]);
    }
}