<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Organization;
use App\Models\Remittance;
use App\Models\Transaction;
use App\Models\VoidRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $semesters      = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();
        $activeSemester = $semesters->firstWhere('is_active', true);
        $org            = auth()->user()->organization;

        $reportData    = null;
        $reportColumns = null;
        $reportTitle   = null;
        $reportPeriod  = null;

        $type       = $request->input('type');
        $semesterId = $request->input('semester_id') ?: $activeSemester?->id;
        $export     = $request->input('export', 'preview');

        if ($type && $semesterId) {
            $semester = AcademicYear::findOrFail($semesterId);
            [$reportColumns, $reportData] = $this->buildReport($org, $type, $semester, $request);
            $reportTitle  = $this->reportTitle($type);
            $reportPeriod = $semester->name;

            if ($export === 'pdf') {
                return $this->streamPdf($org, $semester, $type, $reportTitle, $reportColumns, $reportData);
            }

            if ($export === 'csv') {
                return $this->streamCsv($org, $type, $reportColumns, $reportData);
            }
        }

        return view('org.reports.index', compact(
            'semesters', 'activeSemester', 'reportData', 'reportColumns', 'reportTitle', 'reportPeriod'
        ));
    }

    public function exportPdf(Request $request)
    {
        return $this->index($request->merge(['export' => 'pdf']));
    }

    public function exportCsv(Request $request)
    {
        return $this->index($request->merge(['export' => 'csv']));
    }

    // -------------------------------------------------------------------------
    // Report builder
    // -------------------------------------------------------------------------

    private function buildReport(Organization $org, string $type, AcademicYear $semester, Request $request): array
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        return match ($type) {
            'collection_summary' => $this->collectionSummary($org, $semester, $dateFrom, $dateTo),
            'per_student'        => $this->perStudent($org, $semester, $dateFrom, $dateTo),
            'remittance'         => $this->remittanceReport($org, $semester),
            'void_log'           => $this->voidLog($org, $semester, $dateFrom, $dateTo),
            'payment_methods'    => $this->paymentMethods($org, $semester, $dateFrom, $dateTo),
            default              => [[], []],
        };
    }

    private function baseQuery(Organization $org, AcademicYear $semester, ?string $dateFrom, ?string $dateTo)
    {
        return Transaction::where('organization_id', $org->id)
            ->where('academic_year_id', $semester->id)
            ->where('is_void', false)
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo));
    }

    private function collectionSummary(Organization $org, AcademicYear $semester, ?string $df, ?string $dt): array
    {
        $columns = ['Fee Name', 'Category', 'Transactions', 'Total Amount'];

        $rows = $this->baseQuery($org, $semester, $df, $dt)
            ->with('feeProfile')
            ->selectRaw('fee_profile_id, transaction_type, COUNT(*) as tx_count, SUM(amount_paid) as total')
            ->groupBy('fee_profile_id', 'transaction_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                $r->feeProfile?->name ?? 'Fine / Other',
                $r->feeProfile?->category ?? '—',
                $r->tx_count,
                '₱ ' . number_format($r->total, 2),
            ])
            ->values()
            ->toArray();

        return [$columns, $rows];
    }

    private function perStudent(Organization $org, AcademicYear $semester, ?string $df, ?string $dt): array
    {
        $columns = ['Student No.', 'Student Name', 'OR No.', 'Fee', 'Amount', 'Payment', 'Date'];

        $rows = $this->baseQuery($org, $semester, $df, $dt)
            ->with(['student', 'feeProfile'])
            ->orderBy('student_id')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($t) => [
                $t->student->student_number,
                $t->student->full_name,
                $t->or_number,
                $t->feeProfile?->name ?? 'Fine',
                '₱ ' . number_format($t->amount_paid, 2),
                $t->payment_method,
                $t->created_at->format('M d, Y'),
            ])
            ->values()
            ->toArray();

        return [$columns, $rows];
    }

    private function remittanceReport(Organization $org, AcademicYear $semester): array
    {
        $columns = ['Control No.', 'Status', 'Transactions', 'Total', 'Created By', 'Created', 'Verified', 'Accepted'];

        $rows = Remittance::where('organization_id', $org->id)
            ->where('academic_year_id', $semester->id)
            ->with(['createdBy', 'verifiedBy', 'acceptedBy'])
            ->withCount('transactions')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                $r->control_number,
                $r->status,
                $r->transactions_count,
                '₱ ' . number_format($r->total_amount, 2),
                $r->createdBy?->username ?? '—',
                $r->created_at->format('M d, Y'),
                $r->verified_at?->format('M d, Y') ?? '—',
                $r->accepted_at?->format('M d, Y') ?? '—',
            ])
            ->values()
            ->toArray();

        return [$columns, $rows];
    }

    private function voidLog(Organization $org, AcademicYear $semester, ?string $df, ?string $dt): array
    {
        $columns = ['OR No.', 'Student No.', 'Student Name', 'Amount', 'Reason', 'Requested By', 'Resolved'];

        $rows = VoidRequest::whereHas('transaction', fn ($q) =>
                $q->where('organization_id', $org->id)
                  ->where('academic_year_id', $semester->id)
            )
            ->with(['transaction.student', 'requestedBy'])
            ->when($df, fn ($q) => $q->whereDate('created_at', '>=', $df))
            ->when($dt, fn ($q) => $q->whereDate('created_at', '<=', $dt))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($v) => [
                $v->transaction->or_number,
                $v->transaction->student->student_number,
                $v->transaction->student->full_name,
                '₱ ' . number_format($v->transaction->amount_paid, 2),
                $v->reason,
                $v->requestedBy?->username ?? '—',
                $v->resolved_at?->format('M d, Y') ?? 'Pending',
            ])
            ->values()
            ->toArray();

        return [$columns, $rows];
    }

    private function paymentMethods(Organization $org, AcademicYear $semester, ?string $df, ?string $dt): array
    {
        $columns = ['Date', 'Cash (Count)', 'Cash Total', 'GCash (Count)', 'GCash Total', 'Day Total'];

        $grouped = $this->baseQuery($org, $semester, $df, $dt)
            ->selectRaw('DATE(created_at) as txdate, payment_method, COUNT(*) as tx_count, SUM(amount_paid) as total')
            ->groupBy('txdate', 'payment_method')
            ->orderBy('txdate')
            ->get()
            ->groupBy('txdate');

        $rows = $grouped->map(fn ($group, $date) => [
            $date,
            (int) $group->where('payment_method', 'CASH')->sum('tx_count'),
            '₱ ' . number_format($group->where('payment_method', 'CASH')->sum('total'), 2),
            (int) $group->where('payment_method', 'GCASH')->sum('tx_count'),
            '₱ ' . number_format($group->where('payment_method', 'GCASH')->sum('total'), 2),
            '₱ ' . number_format($group->sum('total'), 2),
        ])->values()->toArray();

        return [$columns, $rows];
    }

    // -------------------------------------------------------------------------
    // Export helpers
    // -------------------------------------------------------------------------

    private function streamPdf(
        Organization $org,
        AcademicYear $semester,
        string $type,
        string $reportTitle,
        array $reportColumns,
        array $reportData
    ) {
        $pdf = Pdf::loadView('pdf.report', compact(
            'org', 'semester', 'type', 'reportTitle', 'reportColumns', 'reportData'
        ))->setPaper('a4', 'landscape');

        $filename = sprintf('%s-%s-%s.pdf', $org->name, $type, now()->format('Y-m-d'));

        return $pdf->download($filename);
    }

    private function streamCsv(
        Organization $org,
        string $type,
        array $reportColumns,
        array $reportData
    ) {
        $filename = sprintf('%s-%s-%s.csv', $org->name, $type, now()->format('Y-m-d'));

        return response()->streamDownload(function () use ($reportColumns, $reportData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $reportColumns);
            foreach ($reportData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function reportTitle(string $type): string
    {
        return match ($type) {
            'collection_summary' => 'Collection Summary',
            'per_student'        => 'Per-Student Breakdown',
            'remittance'         => 'Remittance Report',
            'void_log'           => 'Void Transaction Log',
            'payment_methods'    => 'Payment Methods',
            default              => 'Report',
        };
    }
}
