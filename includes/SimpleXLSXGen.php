<?php
namespace Shuchkin;

class SimpleXLSXGen
{
    public $curSheet;
    protected $defaultFont;
    protected $defaultFontSize;
    protected $sheets;
    protected $template;
    protected $F, $F_KEYS;
    protected $XF, $XF_KEYS;
    protected $SI, $SI_KEYS;
    const N_NORMAL = 0;
    const N_INT = 1;
    const N_DEC = 2;
    const N_PERCENT_INT = 9;
    const N_PRECENT_DEC = 10;
    const N_DATE = 14;
    const N_TIME = 20;
    const N_DATETIME = 22;
    const F_NORMAL = 0;
    const F_HYPERLINK = 1;
    const F_BOLD = 2;
    const F_ITALIC = 4;
    const F_UNDERLINE = 8;
    const F_STRIKE = 16;
    const F_COLOR = 32;
    const A_LEFT = 0;
    const A_CENTER = 1;
    const A_RIGHT = 2;
    const A_TOP = 4;
    const A_MIDDLE = 8;
    const A_BOTTOM = 16;

    public function __construct()
    {
        $this->curSheet = -1;
        $this->defaultFont = 'Calibri';
        $this->defaultFontSize = 10;
        $this->sheets = [['name' => 'Sheet1', 'rows' => [], 'hyperlinks' => [], 'mergecells' => []]];
        $this->SI = [];
        $this->SI_KEYS = [];
        $this->F = [self::N_NORMAL];
        $this->F_KEYS = [self::N_NORMAL];
        $this->XF = [[0, 0, 0, 0, 0, 0, 0]];
        $this->XF_KEYS = ['0000000'];
    }

    public static function fromArray(array $rows, $sheetName = null)
    {
        $xlsx = new static();
        return $xlsx->addSheet($rows, $sheetName);
    }

    public function addSheet(array $rows, $name = null)
    {
        $this->curSheet++;
        if ($name === null) {
            $name = 'Sheet' . ($this->curSheet + 1);
        } else {
            $name = mb_substr($name, 0, 31);
            $name = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '', $name);
        }
        $this->sheets[$this->curSheet] = ['name' => $name, 'hyperlinks' => [], 'mergecells' => [], 'rows' => []];
        if (is_array($rows) && isset($rows[0]) && is_array($rows[0])) {
            $this->sheets[$this->curSheet]['rows'] = $rows;
        } else {
            foreach ($rows as $row) {
                $this->sheets[$this->curSheet]['rows'][] = $row;
            }
        }
        return $this;
    }

    public function mergeCells($range)
    {
        $this->sheets[$this->curSheet]['mergecells'][] = $range;
        return $this;
    }

    public function setStyle($range, $style)
    {
        return $this; // Simplified: No styling in lite version
    }

    public function downloadAs($filename)
    {
        $temp = tmpfile();
        $this->write($temp);
        $meta_datas = stream_get_meta_data($temp);
        $tmp_filename = $meta_datas['uri'];

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp_filename));
        readfile($tmp_filename);
        fclose($temp);
    }

    protected function write($fh)
    {
        $dir = sys_get_temp_dir();
        $zip = new \ZipArchive();
        $filename = $dir . '/xlsx_gen_' . uniqid() . '.zip';

        if ($zip->open($filename, \ZipArchive::CREATE) !== TRUE) {
            return;
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');

        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="1"><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs></styleSheet>');

        // SHARED STRINGS
        $si_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="0" uniqueCount="0">';
        $rows = $this->sheets[0]['rows'];
        $si_cnt = 0;
        foreach ($rows as $r) {
            foreach ($r as $v) {
                if (!is_numeric($v)) {
                    $v = htmlspecialchars($v);
                    $si_xml .= '<si><t>' . $v . '</t></si>';
                    $si_cnt++;
                }
            }
        }
        $si_xml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $si_xml);

        // SHEET DATA
        $sheet_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        $si_idx = 0;
        foreach ($rows as $i => $r) {
            $sheet_xml .= '<row r="' . ($i + 1) . '">';
            foreach ($r as $j => $v) {
                $t = is_numeric($v) ? 'n' : 's';
                $val = is_numeric($v) ? $v : $si_idx++;
                $sheet_xml .= '<c r="' . $this->num2name($j) . ($i + 1) . '" t="' . $t . '"><v>' . $val . '</v></c>';
            }
            $sheet_xml .= '</row>';
        }
        $sheet_xml .= '</sheetData>';

        // Merge Cells logic
        if (!empty($this->sheets[0]['mergecells'])) {
            $sheet_xml .= '<mergeCells count="' . count($this->sheets[0]['mergecells']) . '">';
            foreach ($this->sheets[0]['mergecells'] as $range) {
                $sheet_xml .= '<mergeCell ref="' . $range . '"/>';
            }
            $sheet_xml .= '</mergeCells>';
        }

        $sheet_xml .= '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet_xml);

        $zip->close();

        $content = file_get_contents($filename);
        fwrite($fh, $content);
        unlink($filename);
    }

    public function num2name($num)
    {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->num2name($num2 - 1) . $letter;
        }
        return $letter;
    }
}
?>