<?php

namespace App\Support;

use Dompdf\Cpdf;

/**
 * Draws the Customer Summary table straight onto PDF pages (A4 landscape).
 *
 * The HTML-to-PDF engine needs about 0.5 MB of memory per table row, so a full customer
 * list (1,000+ rows) ran out of memory. Drawing the table directly takes a few MB and
 * a fraction of a second, whatever the number of rows.
 *
 * Uses the standard Helvetica font, so text is Latin (Western European) characters.
 */
class CustomerSummaryPdf
{
    private const PAGE_W = 842.0;
    private const PAGE_H = 595.0;
    private const MARGIN = 24.0;
    private const FONT = 7.0;
    private const ROW_H = 12.0;
    private const HEAD_FONT = 6.6;
    private const PAD = 3.0;

    /** Column widths in points (sum 784, the printable width is 794). */
    private const WIDTHS = [32, 100, 40, 56, 116, 56, 112, 52, 42, 50, 62, 66];

    /** Column alignment: l = left, c = centre, r = right. */
    private const ALIGN = ['c', 'l', 'l', 'l', 'l', 'l', 'l', 'l', 'c', 'r', 'r', 'r'];

    private Cpdf $pdf;
    private string $fontRegular;
    private string $fontBold;

    /**
     * @param  array<int,array>  $rows  Rows in the same order as CustomerSummaryReport::HEADINGS.
     * @return string PDF file contents.
     */
    public static function render(CustomerSummaryReport $report, array $rows, array $totals, ?string $generatedBy): string
    {
        return (new self())->draw($report, $rows, $totals, $generatedBy);
    }

    private function draw(CustomerSummaryReport $report, array $rows, array $totals, ?string $generatedBy): string
    {
        $fontDir = base_path('vendor/dompdf/dompdf/lib/fonts/');
        $cache = storage_path('fonts');
        if (! is_dir($cache)) {
            @mkdir($cache, 0775, true);
        }

        $this->pdf = new Cpdf([0, 0, self::PAGE_W, self::PAGE_H], false, $cache);
        $this->pdf->addInfo('Title', $report->titleWithMonth());
        $this->fontRegular = $fontDir.'Helvetica';
        $this->fontBold = $fontDir.'Helvetica-Bold';
        $this->pdf->selectFont($this->fontRegular, 'WinAnsiEncoding');
        $this->pdf->selectFont($this->fontBold, 'WinAnsiEncoding');

        $headerLines = $this->headerBlock($report, $totals);
        $headHeight = $this->headingRowHeight();

        // Work out the page count first, so every page can say "Page N of M".
        $usableH = self::PAGE_H - 2 * self::MARGIN - 8; // 8 = room for the footer line
        $firstCapacity = max(1, (int) floor(($usableH - $this->blockHeight($headerLines) - $headHeight) / self::ROW_H));
        $nextCapacity = max(1, (int) floor(($usableH - $headHeight) / self::ROW_H));
        $count = count($rows);
        $pages = 1;
        if ($count > $firstCapacity) {
            $pages += (int) ceil(($count - $firstCapacity) / $nextCapacity);
        }
        // The totals row needs one more line: start another page if the last one is full.
        $lastPageRows = $pages === 1 ? $count : ($count - $firstCapacity) - ($pages - 2) * $nextCapacity;
        $lastCapacity = $pages === 1 ? $firstCapacity : $nextCapacity;
        if ($lastPageRows >= $lastCapacity) {
            $pages++;
        }

        $generated = 'Generated '.now()->format('d M Y H:i').($generatedBy ? ' by '.$generatedBy : '');
        $offset = 0;
        for ($page = 1; $page <= $pages; $page++) {
            if ($page > 1) {
                $this->pdf->newPage();
            }

            $y = self::PAGE_H - self::MARGIN; // top edge of the drawing area
            if ($page === 1) {
                $y = $this->drawBlock($headerLines, $y);
            }
            $y = $this->drawHeadingRow($y, $headHeight);

            $capacity = $page === 1 ? $firstCapacity : $nextCapacity;
            $slice = array_slice($rows, $offset, $capacity);
            foreach ($slice as $i => $row) {
                $y = $this->drawRow($row, $y, ($offset + $i) % 2 === 1);
            }
            $offset += count($slice);

            if ($page === $pages) {
                $this->drawTotals($totals, $y);
            }

            $this->drawFooter($generated.'   |   '.$report->titleWithMonth(), $page, $pages);
        }

        return $this->pdf->output();
    }

    /** @return array<int,array{0:string,1:float,2:bool}> text, font size, bold */
    private function headerBlock(CustomerSummaryReport $report, array $totals): array
    {
        return [
            [$report->titleWithMonth(), 14.0, true],
            ['Filters: '.$report->activeFilterText(), 8.0, false],
            [
                number_format($totals['customers']).' customers   |   Usage '.number_format($totals['usage_units'], 2)
                .' units   |   Month bills Rs. '.number_format($totals['month_bill'], 2)
                .'   |   Outstanding Rs. '.number_format($totals['outstanding'], 2),
                8.0,
                false,
            ],
        ];
    }

    private function blockHeight(array $lines): float
    {
        $h = 6.0;
        foreach ($lines as [, $size]) {
            $h += $size + 4;
        }

        return $h;
    }

    private function drawBlock(array $lines, float $y): float
    {
        foreach ($lines as [$text, $size, $bold]) {
            $y -= $size + 1;
            $this->pdf->selectFont($bold ? $this->fontBold : $this->fontRegular);
            $this->pdf->setColor($bold ? [0.07, 0.09, 0.15] : [0.29, 0.33, 0.39]);
            $this->pdf->addText(self::MARGIN, $y, $size, $this->enc($this->fit($text, $size, 794, $bold)));
            $y -= 3;
        }

        return $y - 6;
    }

    /** Heading text split into lines that fit each column. */
    private function headingLines(): array
    {
        $this->pdf->selectFont($this->fontBold);
        $out = [];
        foreach (CustomerSummaryReport::HEADINGS as $i => $heading) {
            $max = self::WIDTHS[$i] - 2 * self::PAD;
            $lines = [];
            $line = '';
            foreach (explode(' ', $heading) as $word) {
                $try = $line === '' ? $word : $line.' '.$word;
                if ($line !== '' && $this->width($try, self::HEAD_FONT) > $max) {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $line = $try;
                }
            }
            $lines[] = $line;
            $out[$i] = $lines;
        }

        return $out;
    }

    private function headingRowHeight(): float
    {
        $most = max(array_map('count', $this->headingLines()));

        return $most * (self::HEAD_FONT + 2) + 6;
    }

    private function drawHeadingRow(float $top, float $height): float
    {
        $lines = $this->headingLines();
        $x = self::MARGIN;
        $this->pdf->setColor([0.118, 0.251, 0.686]);
        $this->pdf->filledRectangle($x, $top - $height, array_sum(self::WIDTHS), $height);

        $this->pdf->selectFont($this->fontBold);
        $this->pdf->setColor([1, 1, 1]);
        foreach (self::WIDTHS as $i => $w) {
            $n = count($lines[$i]);
            $textTop = $top - ($height - $n * (self::HEAD_FONT + 2)) / 2;
            foreach ($lines[$i] as $k => $line) {
                $ty = $textTop - ($k + 1) * (self::HEAD_FONT + 2) + 2.2;
                $tx = $x + ($w - $this->width($line, self::HEAD_FONT)) / 2;
                $this->pdf->addText($tx, $ty, self::HEAD_FONT, $this->enc($line));
            }
            $x += $w;
        }

        return $top - $height;
    }

    private function drawRow(array $row, float $top, bool $shade): float
    {
        $bottom = $top - self::ROW_H;
        $total = array_sum(self::WIDTHS);

        if ($shade) {
            $this->pdf->setColor([0.976, 0.98, 0.984]);
            $this->pdf->filledRectangle(self::MARGIN, $bottom, $total, self::ROW_H);
        }

        $this->pdf->setStrokeColor([0.82, 0.84, 0.86]);
        $this->pdf->setLineStyle(0.4);
        $this->pdf->line(self::MARGIN, $bottom, self::MARGIN + $total, $bottom);

        $cells = [
            (string) $row[0], (string) $row[1], (string) $row[2], (string) $row[3], (string) $row[4], (string) $row[5],
            (string) $row[6], (string) $row[7], (string) $row[8],
            number_format((float) $row[9], 2), number_format((float) $row[10], 2), number_format((float) $row[11], 2),
        ];

        $x = self::MARGIN;
        foreach (self::WIDTHS as $i => $w) {
            $owes = $i === 11 && (float) $row[11] > 0;
            $this->pdf->selectFont($owes ? $this->fontBold : $this->fontRegular);
            $this->pdf->setColor($owes ? [0.725, 0.11, 0.11] : [0.07, 0.09, 0.15]);

            $text = $this->fit($cells[$i], self::FONT, $w - 2 * self::PAD, $owes);
            $tw = $this->width($text, self::FONT);
            $tx = match (self::ALIGN[$i]) {
                'r' => $x + $w - self::PAD - $tw,
                'c' => $x + ($w - $tw) / 2,
                default => $x + self::PAD,
            };
            $this->pdf->addText($tx, $bottom + 3.6, self::FONT, $this->enc($text));
            $x += $w;
        }

        return $bottom;
    }

    private function drawTotals(array $totals, float $top): void
    {
        $bottom = $top - self::ROW_H - 2;
        $total = array_sum(self::WIDTHS);
        $this->pdf->setColor([0.898, 0.906, 0.922]);
        $this->pdf->filledRectangle(self::MARGIN, $bottom, $total, self::ROW_H + 2);

        $this->pdf->selectFont($this->fontBold);
        $this->pdf->setColor([0.07, 0.09, 0.15]);
        $this->pdf->addText(self::MARGIN + self::PAD, $bottom + 4.6, self::FONT, $this->enc('Total ('.number_format($totals['customers']).' customers)'));

        $x = self::MARGIN + array_sum(array_slice(self::WIDTHS, 0, 9));
        foreach ([9 => $totals['usage_units'], 10 => $totals['month_bill'], 11 => $totals['outstanding']] as $i => $value) {
            $w = self::WIDTHS[$i];
            $text = number_format((float) $value, 2);
            $this->pdf->addText($x + $w - self::PAD - $this->width($text, self::FONT), $bottom + 4.6, self::FONT, $this->enc($text));
            $x += $w;
        }
    }

    private function drawFooter(string $text, int $page, int $pages): void
    {
        $this->pdf->selectFont($this->fontRegular);
        $this->pdf->setColor([0.42, 0.45, 0.5]);
        $label = $text.'   |   Page '.$page.' of '.$pages;
        $this->pdf->addText(self::MARGIN, 14, 6.5, $this->enc($label));

        // Credit, small and grey, at the right edge of the same line.
        $credit = CustomerSummaryReport::DEVELOPER_CREDIT;
        $size = 6.0;
        $this->pdf->addText(self::PAGE_W - self::MARGIN - $this->width($credit, $size), 14, $size, $this->enc($credit));
    }

    /** Text width in points; the font must be the one used to draw it. */
    private function width(string $text, float $size): float
    {
        return $this->pdf->getTextWidth($size, $this->enc($text));
    }

    /** Cut text with ... so it fits the width. */
    private function fit(string $text, float $size, float $max, bool $bold = false): string
    {
        $this->pdf->selectFont($bold ? $this->fontBold : $this->fontRegular);
        if ($this->width($text, $size) <= $max) {
            return $text;
        }
        while ($text !== '' && $this->width($text.'...', $size) > $max) {
            $text = mb_substr($text, 0, -1);
        }

        return rtrim($text).'...';
    }

    /** The standard font speaks Windows-1252; anything else prints as ?. */
    private function enc(string $text): string
    {
        return mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
    }
}

