<?php
/**
* @category zoop
* @package convert
*/
// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * Write data into an excel XML format
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Portero Inc.
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */


Class excelXMLCreator {

	static $colcount;

	function getHeader() {
		$now = date('c');
		$header = <<<EOH
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Created>{$now}Z</Created>
  <LastSaved>{$now}Z</LastSaved>
  <Version>11.9999</Version>
 </DocumentProperties>
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <DownloadComponents/>
  <LocationOfComponents/>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>10005</WindowHeight>
  <WindowWidth>10005</WindowWidth>
  <WindowTopX>120</WindowTopX>
  <WindowTopY>135</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s23">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="s24">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <NumberFormat/>
  </Style>
 </Styles>

EOH;
		return $header;
	}

	function getFooter()
	{
		return "</Workbook>";
	}

	function create($data, $worksheets = false) {
		$output = self::getHeader();
		
		if ($worksheets) {
			foreach ($data as $name => $worksheet) { 
				$output .= self::addWorksheet($worksheet, $name);
			}
		} else {
			$output .= self::addWorksheet($data);
		}

		$output .= self::getFooter();

		return $output;
	}

	function addWorksheet($data, $name = 'Sheet1') {
		$output = "<Worksheet ss:Name=\"$name\">\n";
		$output .= self::addTable($data);
		$colcount = self::$colcount;

		$output .= <<<EOH
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <Selected/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveCol>$colcount</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>

EOH;

	return $output;

	}

	function addTable($data) {
		if (count($data) < 1) {
			return '';
		}

		$firstRow = array_shift($data);
		self::$colcount = count($firstRow);

		$colcount1 = self::$colcount +1;

		$output =   ' <Table ss:ExpandedColumnCount="' . $colcount1 .'" ss:ExpandedRowCount="8" x:FullColumns="1" x:FullRows="1">' . "\n";
		
		foreach ($firstRow as $column) { 
			$output .= "  <Column ss:Width=\"30\"  ss:AutoFitWidth=\"1\" /> \n";
		}

		$output .= self::addRow(array_keys($firstRow));
		$output .= self::addRow($firstRow);
		foreach ($data as $row) {
			$output .= self::addRow($row);
		}

		$output .= " </Table> \n";
		return $output;
	}

	function addRow($array) {
		$output = "  <Row>\n";
		foreach($array as $cell) {
			$output .= self::addCell($cell);
		}
		$output .= "  </Row>\n";
		return $output;
	}

	function addCell($cell) {
			if (is_bool($cell)) {
				$val = $cell ? 1 : 0;
				return  "   <Cell ss:StyleID=\"s24\"><Data ss:Type=\"Number\">$val</Data></Cell>\n";
			} elseif (empty($cell)) {
				return  "   <Cell ss:StyleID=\"s23\"/>\n";
			} elseif (is_numeric($cell)) {
				return  "   <Cell ss:StyleID=\"s24\"><Data ss:Type=\"Number\">$cell</Data></Cell>\n";
			} else {
				return  "   <Cell ss:StyleID=\"s23\"><Data ss:Type=\"String\">$cell</Data></Cell>\n";
			}
	}
}
