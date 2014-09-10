<?php

class Sydney_Tools_Csv
{

    private $name = '';
    private $rawdatas = array();

    private $titleLine = '';

    private $lines = array();
    private $cols = array();

    private $currentLine = -1;
    private $nextLine = -1;

    private $excludelist = array();

    public function __construct($name, Array $datas, Array $excludelist = array())
    {
        $this->name = $name;
        $this->rawdatas = $datas;
        $this->excludelist = $excludelist;
        $this->load();
    }

    public function load()
    {
        // Init
        reset($this->rawdatas);
        $firstElement = current($this->rawdatas);
        if (is_array($firstElement)) {
            $this->cols = array_keys($firstElement);
        }
        /* else {

                }*/

        // Datas
        foreach ($this->rawdatas as $kData => $vData) {
            //if (is_array($vData) && !in_array($kData,$this->excludelist, true)) {
            $this->newLine();
            foreach ($vData as $k => $data) {
                $this->add($data, $k);
            }
            //}
        }
    }

    public function newLine()
    {

        if ($this->currentLine == -1) {
            $this->currentLine++;
            $this->nextLine = $this->currentLine + 1;
        } else {
            $this->currentLine = ($this->nextLine > $this->currentLine) ? (($this->nextLine - $this->currentLine) + $this->currentLine) + 1 : $this->currentLine + 1;
            $this->nextLine = $this->currentLine + 1;
        }

    }

    public function add($value, $name = '')
    {
        if (!in_array($name, $this->excludelist, true)) {
            if (is_array($value)) {
                $this->lines[$this->currentLine][] = $name;
                $csv = new Sydney_Tools_Csv($name, $value, $this->excludelist);
                //print ">".$this->name." > ".$this->currentLine.chr(10);
                //print ">".$this->name." > ".$this->nextLine.chr(10);
                $this->lines[$this->nextLine] = $csv;
                $this->nextLine = $this->nextLine + $csv->count() + 1;
            } else {
                //print $this->name." > $name".chr(10);
                $value = trim($value);
                //print ">>".$this->name." > ".$this->currentLine.chr(10);
                //print ">>".$this->name." > ".$this->nextLine.chr(10);
                $this->lines[$this->currentLine][] = $value;

            }
        }
    }

    private function _filterCols($value)
    {
        if (in_array($value, $this->excludelist, true)) {
            return false;
        }

        return true;
    }

    private function _getTitleLine()
    {
        if (empty($this->titleLine) && is_array($this->cols) && count($this->cols) > 0) {
            $this->cols = array_filter($this->cols, array(
                $this,
                '_filterCols'
            ));
            $this->titleLine = implode(";", $this->cols);
        }

        return $this->titleLine;
    }

    public function get()
    {
        return $this->lines;
    }

    public function count()
    {
        return count($this->lines);
    }

    public function __toString()
    {
        $str = '';

        $str .= $this->_getTitleLine() . chr(10);

        foreach ($this->lines as $value) {
            // Classic line
            if (is_array($value)) {
                $str .= implode(";", $value) . chr(10);
            } else { // Sydney_Tools_Csv line
                $value = trim($value);
                if (!empty($value)) {
                    $str .= $value . chr(10);
                    if (!end($this->lines)) {
                        $str .= $this->_getTitleLine() . chr(10);
                    }
                }
            }
        }

        return $str;
    }

}
