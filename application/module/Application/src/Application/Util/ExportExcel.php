<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Util;

use PHPExcel;

/**
 * Description of ExportExcel
 *
 * @author Roman
 */
class ExportExcel {

    private $phpExcelObject;
    private $creator;
    private $owner;
    private $subject;
    private $date;
    private $storage_path;
    // Variables file content
    private $filename;
    private $dataset;
    private $columns;
    private $position_title;
    private $content_title;
    private $title_sheet;
    private $sheet_count;
    private $array_title;
    // The report titles is configured
    private $tituloReporte;
    // header properties
    private $freeze_pane = true;
    private $header_background = "0099cc";
    private $letter_type_header = "Calibri";
    private $letter_color_header = "FFFFFF";
    private $letter_size_header = 16;
    private $letter_bold_header = true;
    private $letter_italic_header = false;
    private $boders_set_header = "allborders";
    // columns properties
    private $columns_background = "E8E8E8";
    private $letter_type_columns = "Calibri";
    private $letter_color_columns = "000000";
    private $letter_size_columns = 10;
    private $letter_bold_columns = false;
    private $letter_italic_columns = false;
    private $boders_set_columns = "allborders";
    // rows properties
    private $rows_background = "FFFFFF";
    private $letter_type_rows = "Calibri";
    private $letter_color_rows = "000000";
    private $letter_size_rows = 10;
    private $letter_bold_rows = false;
    private $letter_italic_rows = false;
    private $boders_set_rows = "allborders";

    /**
     * Constructor
     *
     * @param Array $data
     */
    public function __construct($data) {
        foreach ($data as $key => $value) {
            if (\property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
        $this->date = new \DateTime();
        $this->storage_path = str_replace("\\", "/", getcwd()) . "/../" . $this->filename . "_" . $this->date->format("YmdHis") . ".xlsx";
        $this->array_title = explode(",", $this->title_sheet);
        $this->tituloReporte = explode(",", $this->content_title);
    }

    /**
     * Generate a Excel File
     *
     * @return ArrayObject Contains url to get Excel File
     */
    public function generateReport() {
        $this->phpExcelObject = new PHPExcel();
        // properties and styles
        $style_title = $this->getStyleTitle();
        $style_header = $this->getStyleHeader();
        $style_columns = $this->getStyleColumns();
        $style_information = $this->getStyleInformation();
        // Properties are assigned the book
        $this->phpExcelObject->getProperties()->setCreator($this->creator)
                ->setLastModifiedBy($this->owner)
                ->setTitle($this->title_sheet)
                ->setSubject($this->subject)
                ->setDescription($this->content_title)
                ->setKeywords($this->content_title)
                ->setCategory($this->subject);
        $objWriter = $this->createWriter($style_title, $style_header, $style_columns, $style_information);
        $response = array(
            'success' => true,
            'url' => $this->saveExcelToLocalFile($objWriter, $this->storage_path)
        );
        return $response;
    }

    /**
     * Get Style Properties for Title
     *
     * @return ArrayObject
     */
    private function getStyleTitle() {
        return array(
            'font' => array(
                'name' => $this->letter_type_header,
                'bold' => $this->letter_bold_header,
                'italic' => $this->letter_italic_header,
                'strike' => false,
                'size' => $this->letter_size_header,
                'color' => array(
                    'rgb' => $this->letter_color_header
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'argb' => $this->header_background
                )
            ),
            'borders' => array(
                $this->boders_set_header => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );
    }

    /**
     * Get Style Properties for Header
     *
     * @return ArrayObject
     */
    private function getStyleHeader() {
        return array(
            'font' => array(
                'name' => $this->letter_type_header,
                'bold' => $this->letter_bold_header,
                'italic' => $this->letter_italic_header,
                'strike' => false,
                'size' => $this->letter_size_header,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );
    }

    /**
     * Get Style Properties for Columns
     *
     * @return ArrayObject
     */
    private function getStyleColumns() {
        return array(
            'font' => array(
                'name' => $this->letter_type_columns,
                'bold' => $this->letter_bold_columns,
                'italic' => $this->letter_italic_columns,
                'size' => $this->letter_size_columns,
                'color' => array(
                    'rgb' => $this->letter_color_columns
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $this->columns_background)
            ),
            'borders' => array(
                $this->boders_set_columns => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => TRUE
            )
        );
    }

    /**
     * Get Style Properties for Information
     *
     * @return PHPExcel_Style
     */
    private function getStyleInformation() {
        $style_information = new \PHPExcel_Style();
        $style_information->applyFromArray(array(
            'font' => array(
                'name' => $this->letter_type_rows,
                'bold' => $this->letter_bold_rows,
                'italic' => $this->letter_italic_rows,
                'size' => $this->letter_size_rows,
                'color' => array(
                    'rgb' => $this->letter_color_rows
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'argb' => $this->rows_background
                )
            ),
            'borders' => array(
                $this->boders_set_rows => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        return $style_information;
    }

    /**
     * Create Object of an Excel File
     *
     * @param ArrayObject $style_title Styles for title
     * @param ArrayObject $style_header Styles for header
     * @param ArrayObject $style_columns Styles for columns
     * @param PHPExcel_Style $style_informacion Styles for information
     * @return PHPExcel_Writer_Excel2007 Object created for export Excel File
     */
    private function createWriter($style_title, $style_header, $style_columns, $style_information) {
        // generate chars alphabet
        $array_chars = array();
        for ($i = 65; $i <= 90; $i++) {
            $char = chr($i);
            array_push($array_chars, $char);
        }
        if ($this->sheet_count > 1) {
            $this->phpExcelObject->createSheet();
        }
        $i = 0;
        foreach ($this->phpExcelObject->getWorksheetIterator() as $worksheet) {
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Logo');
            $objDrawing->setDescription('Logo');
            $objDrawing->setPath(str_replace("\\", "/", getcwd()) . "/../images/telmex_logo_xlsx.jpg");
            $objDrawing->setCoordinates('A1');
            $objDrawing->setWidth(135);
            $objDrawing->setWorksheet($worksheet);
            $worksheet->setTitle($this->array_title[$i]);
            $cant_columns = sizeof($this->columns[$i]);
            $worksheet->mergeCells($array_chars[0] . '1:' . $array_chars[$cant_columns - 1] . '2');
            if ($i == 0) {
                $worksheet->setCellValue($array_chars[0] . '1', "CUAD TELMEX");
            }
            $worksheet->mergeCells($array_chars[0] . '3:' . $array_chars[$cant_columns - 1] . '3');
            $worksheet->setCellValue($array_chars[0] . '3', $this->tituloReporte[$i]);
            $worksheet->getStyle($array_chars[0] . '1:' . $array_chars[$cant_columns - 1] . '1')->applyFromArray($style_header);
            $worksheet->getStyle($array_chars[0] . '3:' . $array_chars[$cant_columns - 1] . '3')->applyFromArray($style_title);
            $worksheet->getStyle($array_chars[0] . $this->position_title . ':' . $array_chars[$cant_columns - 1] . $this->position_title)->applyFromArray($style_columns);
            for ($j = 0; $j < $cant_columns; $j++) {
                $worksheet->setCellValueByColumnAndRow($j, $this->position_title, $this->columns[$i][$j]);
            }
            /** Freeze Panes */
            if ($this->freeze_pane) {
                $worksheet->freezePaneByColumnAndRow(0, $this->position_title + 1);
            }
            // get data array for content
            #$array = json_decode($this->dataset[$i], 1);
            $array = $this->dataset[$i];
            $vector_data = $this->std_class_object_to_array($array);
            $row = $this->position_title + 1;
            $col = 0;
            foreach ($vector_data as $key => $value) {
                $vdata = $vector_data[$key];
                foreach ($vdata as $key => $value) {
                    $worksheet->setCellValueByColumnAndRow($col, $row, $vdata[$key]);
                    $col++;
                }
                $col = 0;
                $row++;
            }
            for ($r = $this->position_title + 1; $r <= $row - 1; $r++) {
                $worksheet->setSharedStyle($style_information, $array_chars[0] . $r . ':' . $array_chars[$cant_columns - 1] . $r);
            }
            for ($rd = $array_chars[0]; $rd <= $array_chars[$cant_columns + 1]; $rd++) {
                $worksheet->getColumnDimension($rd)->setAutoSize(TRUE);
            }
            $i++;
        }
        // active sheet default
        $this->phpExcelObject->setActiveSheetIndex(0);
        return new \PHPExcel_Writer_Excel2007($this->phpExcelObject);
    }

    /**
     * Save File in custom path
     *
     * @param PHPExcel_Writer_Excel2007     $objWriter      Object created of an
     *                                                       Excel with all 
     *                                                       data required
     * @param string                        $storage_path   Path to save $objWriter
     *                                                      like a file.xlsx
     * @return String
     */
    private function saveExcelToLocalFile($objWriter, $storage_path) {
        $filePath = $storage_path;
        $objWriter->save($filePath);
        return basename($filePath);
    }

    /**
     * Convert an Object to Array
     *
     * @param   Obeject         $stdclassobject         StdClass to convert
     * @return  Array                                   Result to convert 
     *                                                  StdClass in ArrayObject
     */
    private function std_class_object_to_array($stdclassobject) {
        $array = array();
        $_array = \is_object($stdclassobject) ? \get_object_vars($stdclassobject) : $stdclassobject;
        foreach ($_array as $key => $value) {
            $value = (\is_array($value) || \is_object($value)) ? $this->std_class_object_to_array($value) : $value;
            $array[$key] = $value;
        }
        return $array;
    }

}
