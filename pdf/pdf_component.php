<?php
/**
* @category zoop
* @package pdf
*/
/*
	include(dirname(__file__) . "/class.pdf.php");
	include(dirname(__file__) . "/RotatePdf.php");
	include(dirname(__file__) . "/pdfreport.php");
	include(dirname(__file__) . "/pdftable_old.php");
	include(dirname(__file__) . "/PdfObject.php");
	include(dirname(__file__) . "/PdfContainer.php");
	include(dirname(__file__) . "/PdfTextBox.php");
	include(dirname(__file__) . "/PdfTableRow.php");
	include(dirname(__file__) . "/PdfTableCell.php");
	include(dirname(__file__) . "/PdfTable.php");
	include(dirname(__file__) . "/PdfImage.php");
	include(dirname(__file__) . "/PdfCircle.php");
	include(dirname(__file__) . "/PdfLine.php");
	//include(dirname(__file__) . "/HTMLtoPdfObject.php");
*/
/**
* @package pdf
*/
class component_pdf extends component
{
	function getIncludes()
	{
		return array(
				"cpdf" => dirname(__file__) . "/class.pdf.php",
				"rotatepdf" => dirname(__file__) . "/RotatePdf.php",
				"pdfreport" => dirname(__file__) . "/pdfreport.php",
				"pdftable_old" => dirname(__file__) . "/pdftable_old.php",
				"pdfobject" => dirname(__file__) . "/PdfObject.php",
				"pdfcontainer" => dirname(__file__) . "/PdfContainer.php",
				"pdftextbox" => dirname(__file__) . "/PdfTextBox.php",
				"pdftablerow" => dirname(__file__) . "/PdfTableRow.php",
				"pdftablecell" => dirname(__file__) . "/PdfTableCell.php",
				"pdftable" => dirname(__file__) . "/PdfTable.php",
				"pdfimage" => dirname(__file__) . "/PdfImage.php",
				"pdfcircle" => dirname(__file__) . "/PdfCircle.php",
				"pdfline" => dirname(__file__) . "/PdfLine.php"
		);
	}
}
?>