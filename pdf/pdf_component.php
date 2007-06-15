<?php
//**
* @category zoop
* @package pdf
*/
// Copyright (c) 2007 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

 /**
 * @package pdf
 * @uses component
 * @version $id$
 * @copyright 1997-2007 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}/
 */
class component_pdf extends component
{
	function getIncludes()
	{
		return array(
				"cpdf" => $this->getBasePath() . "/class.pdf.php",
				"rotatepdf" => $this->getBasePath() . "/RotatePdf.php",
				"pdfreport" => $this->getBasePath() . "/pdfreport.php",
				"pdftable_old" => $this->getBasePath() . "/pdftable_old.php",
				"pdfobject" => $this->getBasePath() . "/PdfObject.php",
				"pdfcontainer" => $this->getBasePath() . "/PdfContainer.php",
				"pdftextbox" => $this->getBasePath() . "/PdfTextBox.php",
				"pdftablerow" => $this->getBasePath() . "/PdfTableRow.php",
				"pdftablecell" => $this->getBasePath() . "/PdfTableCell.php",
				"pdftable" => $this->getBasePath() . "/PdfTable.php",
				"pdfimage" => $this->getBasePath() . "/PdfImage.php",
				"pdfcircle" => $this->getBasePath() . "/PdfCircle.php",
				"pdfline" => $this->getBasePath() . "/PdfLine.php"
		);
	}
}
?>
