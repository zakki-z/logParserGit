<?php

namespace App\Service;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfMaker
{
    private const CHUNK_SIZE = 100;

    public function generateLogPdf(array $logEntries): string
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
        ]);

        // CSS styling to match the template
        $css = '
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #1f2937;
                }
                h1 {
                    font-size: 24px;
                    font-weight: bold;
                    color: #1f2937;
                    margin-bottom: 20px;
                }
                .info-text {
                    font-size: 14px;
                    color: #4b5563;
                    margin-bottom: 15px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 11px;
                }
                thead {
                    background-color: #1f2937;
                    color: white;
                }
                th {
                    padding: 10px;
                    text-align: left;
                    font-weight: 600;
                }
                td {
                    padding: 8px;
                    border-bottom: 1px solid #e5e7eb;
                }
                tbody tr:hover {
                    background-color: #f9fafb;
                }
                .badge {
                    display: inline-block;
                    padding: 4px 12px;
                    border-radius: 9999px;
                    font-size: 10px;
                    font-weight: 500;
                    text-align: center;
                }
                .badge-channel {
                    background-color: #e5e7eb;
                    color: #1f2937;
                }
                .badge-error {
                    background-color: #dc2626;
                    color: white;
                }
                .badge-warning {
                    background-color: #eab308;
                    color: #1f2937;
                }
                .badge-info {
                    background-color: #3b82f6;
                    color: white;
                }
                .badge-debug {
                    background-color: #4f46e5;
                    color: white;
                }
                .badge-default {
                    background-color: #e5e7eb;
                    color: #1f2937;
                }
            </style>
        ';

        $mpdf->WriteHTML($css);

        // Header with total count
        $totalEntries = count($logEntries);
        $mpdf->WriteHTML('<h1>Log Entries</h1>');
        $mpdf->WriteHTML('<p class="info-text">Total entries: <strong>' . $totalEntries . '</strong></p>');

        // Table header
        $mpdf->WriteHTML('
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 18%;">Date</th>
                        <th style="width: 15%;">Channel</th>
                        <th style="width: 12%;">Type</th>
                        <th style="width: 47%;">Information</th>
                    </tr>
                </thead>
            </table>
        ');

        $chunks = array_chunk($logEntries, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $html = '<table><tbody>';

            foreach ($chunk as $logEntry) {
                $type = $logEntry->getType();
                $badgeClass = $this->getBadgeClass($type);

                $html .= '<tr>';
                $html .= '<td style="width: 8%;">' . $logEntry->getId() . '</td>';
                $html .= '<td style="width: 18%;">' . $logEntry->getDate()->format('Y-m-d H:i:s') . '</td>';
                $html .= '<td style="width: 15%;"><span class="badge badge-channel">' . htmlspecialchars($logEntry->getChannel()) . '</span></td>';
                $html .= '<td style="width: 12%;"><span class="badge ' . $badgeClass . '">' . htmlspecialchars($type) . '</span></td>';
                $html .= '<td style="width: 47%;">' . htmlspecialchars($logEntry->getInformation()) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            $mpdf->WriteHTML($html);
        }

        return $mpdf->Output('user_logs.pdf', Destination::STRING_RETURN);
    }

    private function getBadgeClass(string $type): string
    {
        return match ($type) {
            'ERROR' => 'badge-error',
            'WARNING' => 'badge-warning',
            'INFO' => 'badge-info',
            'DEBUG' => 'badge-debug',
            default => 'badge-default',
        };
    }
}
