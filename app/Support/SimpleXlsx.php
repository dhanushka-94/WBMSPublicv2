<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Writes a single-sheet .xlsx file (real Excel format, no extra package needed).
 * Numbers stay numbers, so Excel can sort, filter and sum them.
 */
class SimpleXlsx
{
    // Cell styles, see styles().
    private const S_DEFAULT = 0;
    private const S_HEADER = 1;
    private const S_MONEY = 2;
    private const S_TITLE = 3;
    private const S_TOTAL_TEXT = 4;
    private const S_TOTAL_MONEY = 5;
    private const S_INT = 6;
    private const S_NOTE = 7;

    /**
     * @param  string[]  $titleLines  Rows above the table (title, filters ...).
     * @param  string[]  $headings
     * @param  iterable<array>  $rows
     * @param  array<int,string>  $columnTypes  Column index => 'int' or 'money' (anything else is text/number as is).
     * @param  array<int,float>  $widths  Column index => width in characters.
     * @param  array|null  $totalsRow  Same length as $headings; null cells stay empty.
     * @param  string|null  $footerNote  Small grey line placed after the totals (e.g. a credit).
     * @return string Path of a temporary .xlsx file. The caller deletes it.
     */
    public static function build(
        string $sheetName,
        array $titleLines,
        array $headings,
        iterable $rows,
        array $columnTypes = [],
        array $widths = [],
        ?array $totalsRow = null,
        ?string $footerNote = null
    ): string {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the Excel file.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rootRels());
        $zip->addFromString('xl/workbook.xml', self::workbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($titleLines, $headings, $rows, $columnTypes, $widths, $totalsRow, $footerNote));
        $zip->close();

        return $path;
    }

    private static function sheet(array $titleLines, array $headings, iterable $rows, array $types, array $widths, ?array $totals, ?string $footerNote): string
    {
        $cols = count($headings);
        $lastCol = self::col($cols - 1);
        $out = [];
        $r = 0;

        foreach ($titleLines as $i => $line) {
            $r++;
            $out[] = '<row r="'.$r.'">'.self::cell($r, 0, $line, $i === 0 ? self::S_TITLE : self::S_NOTE).'</row>';
        }
        if ($titleLines) {
            $r++; // blank spacer row
        }

        $r++;
        $headerRow = $r;
        $cells = '';
        foreach ($headings as $c => $h) {
            $cells .= self::cell($r, $c, $h, self::S_HEADER);
        }
        $out[] = '<row r="'.$r.'" ht="32" customHeight="1">'.$cells.'</row>';

        foreach ($rows as $row) {
            $r++;
            $cells = '';
            foreach (array_values($row) as $c => $value) {
                $style = match ($types[$c] ?? null) {
                    'money' => self::S_MONEY,
                    'int' => self::S_INT,
                    default => self::S_DEFAULT,
                };
                $cells .= self::cell($r, $c, $value, $style);
            }
            $out[] = '<row r="'.$r.'">'.$cells.'</row>';
        }
        $lastDataRow = $r;

        if ($totals !== null) {
            $r++;
            $cells = '';
            foreach ($totals as $c => $value) {
                $style = ($types[$c] ?? null) === 'money' ? self::S_TOTAL_MONEY : self::S_TOTAL_TEXT;
                $cells .= self::cell($r, $c, $value, $style);
            }
            $out[] = '<row r="'.$r.'">'.$cells.'</row>';
        }

        if ($footerNote !== null && $footerNote !== '') {
            $r += 2; // one blank row between the totals and the credit
            $out[] = '<row r="'.$r.'">'.self::cell($r, 0, $footerNote, self::S_NOTE).'</row>';
        }

        $colXml = '';
        foreach ($headings as $c => $_) {
            $w = $widths[$c] ?? 14;
            $colXml .= '<col min="'.($c + 1).'" max="'.($c + 1).'" width="'.$w.'" customWidth="1"/>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0">'
            .'<pane ySplit="'.$headerRow.'" topLeftCell="A'.($headerRow + 1).'" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .'<cols>'.$colXml.'</cols>'
            .'<sheetData>'.implode('', $out).'</sheetData>';

        if ($lastDataRow > $headerRow) {
            $xml .= '<autoFilter ref="A'.$headerRow.':'.$lastCol.$lastDataRow.'"/>';
        }

        return $xml
            .'<pageMargins left="0.4" right="0.4" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>'
            .'<pageSetup orientation="landscape" fitToHeight="0"/>'
            .'</worksheet>';
    }

    private static function cell(int $row, int $col, mixed $value, int $style): string
    {
        $ref = self::col($col).$row;
        $s = $style ? ' s="'.$style.'"' : '';

        if ($value === null || $value === '') {
            return '<c r="'.$ref.'" s="'.$style.'"/>'; // keeps the cell border even when empty
        }
        if (is_int($value) || is_float($value)) {
            return '<c r="'.$ref.'"'.$s.'><v>'.$value.'</v></c>';
        }

        return '<c r="'.$ref.'"'.$s.' t="inlineStr"><is><t xml:space="preserve">'.self::esc((string) $value).'</t></is></c>';
    }

    private static function col(int $index): string
    {
        $name = '';
        for ($i = $index + 1; $i > 0; $i = intdiv($i - 1, 26)) {
            $name = chr(65 + ($i - 1) % 26).$name;
        }

        return $name;
    }

    private static function esc(string $text): string
    {
        // Drop characters XML cannot hold, then escape the rest.
        $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        // Excel sheet names: max 31 characters, none of  \ / ? * [ ] :
        $name = mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetName) ?? 'Report', 0, 31);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.self::esc($name).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private static function styles(): string
    {
        // numFmt 164 = #,##0.00 ; xf index order matches the S_* constants above.
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>'
            .'<fonts count="5">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><i/><sz val="10"/><color rgb="FF555555"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="4">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1E40AF"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE5E7EB"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right><top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="8">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="164" fontId="3" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="1" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'</cellXfs>'
            .'</styleSheet>';
    }
}
