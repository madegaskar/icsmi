<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\ReportBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $reports = new ReportBuilder($filters['from'] ?? null, $filters['to'] ?? null);

        return view('reports.index', [
            'filters' => $filters,
            'summary' => $reports->summary(),
            'sections' => collect(ReportBuilder::SECTIONS)->mapWithKeys(fn ($section) => [$section => $reports->section($section)]),
        ]);
    }

    public function export(Request $request, string $section, string $format): StreamedResponse|BinaryFileResponse
    {
        $filters = $this->filters($request);
        $report = (new ReportBuilder($filters['from'] ?? null, $filters['to'] ?? null))->section($section);

        AuditLog::record('report.exported', $request->user()->id, ['section' => $section, 'format' => $format] + $filters);

        $rows = array_map(fn (array $row) => array_map($this->neutralizeFormula(...), $row), $report['rows']);
        $filename = 'icsmi-'.$section.'-'.now()->format('Ymd').'.'.$format;

        return $format === 'csv'
            ? $this->csv($filename, $report['headers'], $rows)
            : $this->xlsx($filename, $report['headers'], $rows);
    }

    /**
     * @return array{from?: ?string, to?: ?string}
     */
    private function filters(Request $request): array
    {
        return array_filter($request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]));
    }

    /**
     * Stops spreadsheet programs from running user-supplied text as a formula.
     */
    private function neutralizeFormula(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<mixed>>  $rows
     */
    private function csv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers, escape: '');

            foreach ($rows as $row) {
                fputcsv($output, $row, escape: '');
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<mixed>>  $rows
     */
    private function xlsx(string $filename, array $headers, array $rows): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'report');

        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues($headers));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
