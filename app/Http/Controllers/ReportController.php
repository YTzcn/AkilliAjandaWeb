<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodReportRequest;
use App\Services\PeriodReportService;
use App\ViewModels\PeriodReportPresentation;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected PeriodReportService $periodReportService
    ) {}

    public function index(PeriodReportRequest $request): \Illuminate\View\View
    {
        [$from, $to] = $request->rangeBoundaries();
        $report = $this->periodReportService->buildForUser((int) Auth::id(), $from, $to);

        return view('reports.period', [
            'report' => $report,
            'dateFrom' => $request->validated('date_from'),
            'dateTo' => $request->validated('date_to'),
        ]);
    }

    public function exportCsv(PeriodReportRequest $request): StreamedResponse
    {
        [$from, $to] = $request->rangeBoundaries();
        $report = $this->periodReportService->buildForUser((int) Auth::id(), $from, $to);

        $filename = 'ajanda-raporu-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($report, $from, $to): void {
            self::writeCsv($report, $from, $to);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Yazdırılabilir HTML (Ctrl+P → PDF olarak kaydet). Ek paket gerektirmez.
     */
    public function exportPrintable(PeriodReportRequest $request): Response
    {
        [$from, $to] = $request->rangeBoundaries();
        $report = $this->periodReportService->buildForUser((int) Auth::id(), $from, $to);

        $filename = 'ajanda-raporu-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.html';

        return response()
            ->view('reports.period-print', [
                'report' => $report,
                'dateFrom' => $from->toDateString(),
                'dateTo' => $to->toDateString(),
            ])
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    private static function writeCsv(PeriodReportPresentation $report, Carbon $from, Carbon $to): void
    {
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Ajanda raporu', $from->format('d.m.Y'), '–', $to->format('d.m.Y')], ';');
        fputcsv($out, [], ';');
        fputcsv($out, ['Özet'], ';');
        fputcsv($out, ['Görev (toplam)', (string) $report->taskTotal], ';');
        fputcsv($out, ['Görev (tamamlanan)', (string) $report->taskCompleted], ';');
        fputcsv($out, ['Görev (bekleyen)', (string) $report->taskPending], ';');
        fputcsv($out, ['Etkinlik (çakışan)', (string) $report->eventTotal], ';');
        fputcsv($out, [], ';');
        fputcsv($out, ['Görevler'], ';');
        fputcsv($out, ['Başlık', 'Son tarih', 'Durum', 'Tamamlandı', 'Öncelik'], ';');
        foreach ($report->tasks as $t) {
            fputcsv($out, [
                $t->title,
                $t->due_date?->format('d.m.Y H:i') ?? '',
                (string) $t->status,
                $t->is_completed ? 'Evet' : 'Hayır',
                (string) $t->priority,
            ], ';');
        }
        fputcsv($out, [], ';');
        fputcsv($out, ['Etkinlikler'], ';');
        fputcsv($out, ['Başlık', 'Başlangıç', 'Bitiş', 'Konum'], ';');
        foreach ($report->events as $e) {
            fputcsv($out, [
                $e->title,
                $e->start_date?->format('d.m.Y H:i') ?? '',
                $e->end_date?->format('d.m.Y H:i') ?? '',
                (string) ($e->location ?? ''),
            ], ';');
        }
        fclose($out);
    }
}
