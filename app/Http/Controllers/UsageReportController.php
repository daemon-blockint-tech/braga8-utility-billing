<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\UsageReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UsageReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $latestReport = UsageReport::orderBy('month_year', 'desc')->first();

        $searchMonthNumber = null;
        if ($search) {
            $trimmedSearch = strtolower(trim($search));

            $monthsMap = [
                'jan' => '01', 'januari' => '01', 'january' => '01', '01' => '01', '1' => '01',
                'feb' => '02', 'februari' => '02', 'february' => '02', '02' => '02', '2' => '02',
                'mar' => '03', 'maret' => '03', 'march' => '03', '03' => '03', '3' => '03',
                'apr' => '04', 'april' => '04', '04' => '04', '4' => '04',
                'mei' => '05', 'may' => '05', '05' => '05', '5' => '05',
                'jun' => '06', 'juni' => '06', 'june' => '06', '06' => '06', '6' => '06',
                'jul' => '07', 'juli' => '07', 'july' => '07', '07' => '07', '7' => '07',
                'agu' => '08', 'agustus' => '08', 'august' => '08', 'aug' => '08', '08' => '08', '8' => '08',
                'sep' => '09', 'september' => '09', '09' => '09', '9' => '09',
                'okt' => '10', 'oktober' => '10', 'october' => '10', 'oct' => '10', '10' => '10',
                'nov' => '11', 'november' => '11', '11' => '11',
                'des' => '12', 'desember' => '12', 'december' => '12', 'dec' => '12', '12' => '12',
            ];

            if (array_key_exists($trimmedSearch, $monthsMap)) {
                $searchMonthNumber = $monthsMap[$trimmedSearch];
            }
        }

        $reports = UsageReport::when($search, function ($query) use ($search, $searchMonthNumber) {
                if ($searchMonthNumber) {
                    return $query->where('month_year', 'like', "%-{$searchMonthNumber}");
                }
                return $query->where('month_year', 'like', "%{$search}%");
            })
            ->orderBy('month_year', 'desc')
            ->paginate(10);

        $reports->getCollection()->transform(function ($report) {
            $dateParts = explode('-', $report->month_year);
            $year  = $dateParts[0];
            $month = $dateParts[1];

            $report->details = Invoice::whereYear('billing_period_start', $year)
                ->whereMonth('billing_period_start', $month)
                ->with(['tenant', 'unit'])
                ->get();

            return $report;
        });

        return view('reports.index', compact('reports', 'latestReport'));
    }

    public function generate(Request $request)
    {
        $month         = $request->input('month');
        $formattedDate = Carbon::parse($month . '-01')->translatedFormat('F Y');

        $report  = UsageReport::where('month_year', $month)->first() ?? new UsageReport();
        $hasData = $report->calculateMonthlyStats($month);

        if (!$hasData) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['month' => 'Gagal membuat laporan! Belum ada data tagihan (invoice) atau meteran utilitas yang masuk untuk periode ' . $formattedDate . '.']);
        }

        return redirect()->route('reports.index')
            ->with('success', 'Laporan bulanan periode ' . $formattedDate . ' berhasil dibuat!');
    }

    public function exportPdf($id)
    {
        $report = UsageReport::findOrFail($id);

        $dateParts = explode('-', $report->month_year);
        $year  = $dateParts[0];
        $month = $dateParts[1];

        $invoices = Invoice::whereYear('billing_period_start', $year)
            ->whereMonth('billing_period_start', $month)
            ->with(['tenant', 'unit'])
            ->get();

        if (ob_get_contents()) ob_end_clean();

        $pdf = Pdf::loadView('pdf.usage-report', compact('report', 'invoices'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("Usage-Report-{$report->month_year}.pdf");
    }

    public function destroy($id)
    {
        $report        = UsageReport::findOrFail($id);
        $formattedDate = Carbon::parse($report->month_year . '-01')->translatedFormat('F Y');

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Laporan bulanan periode ' . $formattedDate . ' berhasil dihapus.');
    }
}