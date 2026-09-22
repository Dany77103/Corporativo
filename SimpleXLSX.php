<?php
namespace Shuchkin;

use SimpleXMLElement;
use ZipArchive;

class SimpleXLSX
{
    // Banderas de tipos de datos
    const SCHEMA_REL_CELLS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet';
    const SCHEMA_REL_OFFICEDOC = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';

    private $sheets = [];
    private $sheetNames = [];
    private $sharedstrings = [];
    private $error = false;

    public static function parse($filename, $is_data = false)
    {
        $xlsx = new self();
        if ($xlsx->_parse($filename, $is_data)) {
            return $xlsx;
        }
        return false;
    }

    public static function parseError()
    {
        return false;
    }

    public function __construct()
    {
    }

    public function rows($worksheetIndex = 0)
    {
        if (($ws = $this->worksheet($worksheetIndex)) === false) {
            return false;
        }
        $rows = [];
        foreach ($ws->sheetData->row as $row) {
            $r = [];
            foreach ($row->c as $c) {
                $r[] = $this->value($c);
            }
            $rows[] = $r;
        }
        return $rows;
    }

    public function worksheet($worksheetIndex = 0)
    {
        if (isset($this->sheets[$worksheetIndex])) {
            return $this->sheets[$worksheetIndex];
        }
        return false;
    }

    public function value($cell)
    {
        $dataType = (string)$cell['t'];
        $val = (string)$cell->v;

        if ($dataType === 's') { // Shared String
            if (isset($this->sharedstrings[(int)$val])) {
                return $this->sharedstrings[(int)$val];
            }
        }
        return $val;
    }

    private function _parse($filename, $is_data = false)
    {
        if (!class_exists('ZipArchive')) {
            $this->error = 'ZipArchive extension missing in PHP';
            return false;
        }

        $zip = new ZipArchive();
        $status = $is_data ? $zip->openFileData($filename) : $zip->open($filename);

        if ($status === true) {
            // Cargar Cadenas Compartidas (Shared Strings)
            if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $xml = simplexml_load_string($zip->getFromIndex($idx));
                if ($xml && isset($xml->si)) {
                    foreach ($xml->si as $val) {
                        if (isset($val->t)) {
                            $this->sharedstrings[] = (string)$val->t;
                        } elseif (isset($val->r)) {
                            $t = '';
                            foreach ($val->r as $r) {
                                $t .= (string)$r->t;
                            }
                            $this->sharedstrings[] = $t;
                        }
                    }
                }
            }

            // Cargar Hojas de Trabajo (Sheets)
            for ($i = 1; $i <= 20; $i++) {
                if (($idx = $zip->locateName("xl/worksheets/sheet{$i}.xml")) !== false) {
                    $xml = simplexml_load_string($zip->getFromIndex($idx));
                    if ($xml) {
                        $this->sheets[] = $xml;
                    }
                }
            }

            $zip->close();
            return true;
        }

        return false;
    }
}