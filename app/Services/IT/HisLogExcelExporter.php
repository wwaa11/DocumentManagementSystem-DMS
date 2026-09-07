<?php

namespace App\Services\IT;

use ZipArchive;

class HisLogExcelExporter
{
    /**
     * @param  list<list<string|int|float|null>>  $rows
     */
    public function build(array $rows): string
    {
        return $this->buildSheets([
            'HIS_Log' => $rows,
        ]);
    }

    /**
     * @param  array<string, list<list<string|int|float|null>>>  $sheets
     */
    public function buildSheets(array $sheets): string
    {
        $path = $this->createTempFilePath('hislog_xlsx_', '.xlsx');

        if ($path === null) {
            return '';
        }

        $sharedStrings = [];
        $stringIndex = [];

        foreach ($sheets as $rows) {
            foreach ($rows as $row) {
                foreach ($row as $cell) {
                    if ($cell === null || is_int($cell) || is_float($cell)) {
                        continue;
                    }

                    $value = (string) $cell;

                    if (! array_key_exists($value, $stringIndex)) {
                        $stringIndex[$value] = count($sharedStrings);
                        $sharedStrings[] = $value;
                    }
                }
            }
        }

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);

            return '';
        }

        $sheetNames = array_keys($sheets);

        $zip->addFromString('[Content_Types].xml', $this->contentTypes(count($sheetNames)));
        $zip->addFromString('_rels/.rels', $this->rels());
        $zip->addFromString('xl/workbook.xml', $this->workbook($sheetNames));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels(count($sheetNames)));
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/sharedStrings.xml', $this->sharedStringsXml($sharedStrings));

        foreach (array_values($sheets) as $index => $rows) {
            $zip->addFromString(
                'xl/worksheets/sheet'.($index + 1).'.xml',
                $this->sheetXml($rows, $stringIndex)
            );
        }

        $zip->close();

        $content = file_get_contents($path) ?: '';
        @unlink($path);

        return $content;
    }

    private function createTempFilePath(string $prefix, string $extension): ?string
    {
        $directory = storage_path('app/temp');

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return null;
        }

        $path = $directory.DIRECTORY_SEPARATOR.uniqid($prefix, true).$extension;

        if (file_put_contents($path, '') === false) {
            return null;
        }

        return $path;
    }

    private function contentTypes(int $sheetCount): string
    {
        $sheetOverrides = '';

        for ($index = 1; $index <= $sheetCount; $index++) {
            $sheetOverrides .= '<Override PartName="/xl/worksheets/sheet'.$index.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .$sheetOverrides
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            .'</Types>';
    }

    private function rels(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    /**
     * @param  list<string>  $sheetNames
     */
    private function workbook(array $sheetNames): string
    {
        $sheets = '';

        foreach ($sheetNames as $index => $name) {
            $sheetId = $index + 1;
            $sheets .= '<sheet name="'.$this->escapeXml($name).'" sheetId="'.$sheetId.'" r:id="rId'.$sheetId.'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            .$sheets
            .'</sheets>'
            .'</workbook>';
    }

    private function workbookRels(int $sheetCount): string
    {
        $relationships = '';

        for ($index = 1; $index <= $sheetCount; $index++) {
            $relationships .= '<Relationship Id="rId'.$index.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$index.'.xml"/>';
        }

        $stylesId = $sheetCount + 1;
        $sharedStringsId = $sheetCount + 2;

        $relationships .= '<Relationship Id="rId'.$stylesId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $relationships .= '<Relationship Id="rId'.$sharedStringsId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$relationships
            .'</Relationships>';
    }

    private function styles(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>
    <fills count="1"><fill><patternFill patternType="none"/></fill></fills>
    <borders count="1"><border/></borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
    <cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>
</styleSheet>
XML;
    }

    /**
     * @param  list<string>  $sharedStrings
     */
    private function sharedStringsXml(array $sharedStrings): string
    {
        $items = '';

        foreach ($sharedStrings as $value) {
            $items .= '<si><t>'.$this->escapeXml($value).'</t></si>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($sharedStrings).'" uniqueCount="'.count($sharedStrings).'">'
            .$items
            .'</sst>';
    }

    /**
     * @param  list<list<string|int|float|null>>  $rows
     * @param  array<string, int>  $stringIndex
     */
    private function sheetXml(array $rows, array $stringIndex): string
    {
        $sheetRows = '';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $cells = '';

            foreach ($row as $columnIndex => $cell) {
                $reference = $this->columnReference($columnIndex).$rowNumber;
                $cells .= $this->cellXml($reference, $cell, $stringIndex);
            }

            $sheetRows .= '<row r="'.$rowNumber.'">'.$cells.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'
            .$sheetRows
            .'</sheetData>'
            .'</worksheet>';
    }

    /**
     * @param  array<string, int>  $stringIndex
     */
    private function cellXml(string $reference, string|int|float|null $value, array $stringIndex): string
    {
        if ($value === null || $value === '') {
            return '<c r="'.$reference.'"/>';
        }

        if (is_int($value) || is_float($value)) {
            return '<c r="'.$reference.'"><v>'.$value.'</v></c>';
        }

        $stringValue = (string) $value;

        return '<c r="'.$reference.'" t="s"><v>'.$stringIndex[$stringValue].'</v></c>';
    }

    private function columnReference(int $index): string
    {
        $column = '';
        $number = $index + 1;

        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $column = chr(65 + $remainder).$column;
            $number = intdiv($number - 1, 26);
        }

        return $column;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
