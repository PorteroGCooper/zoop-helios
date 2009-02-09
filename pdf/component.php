<?php

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
 * @ingroup pdf
 * @ingroup components
 *
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}/
 */
class component_pdf extends component {
	function getIncludes() {
		$base = $this->getBasePath();
		return array(
			"cpdf"         => $base . "/class.pdf.php",
			"rotatepdf"    => $base . "/RotatePdf.php",
			"pdfreport"    => $base . "/pdfreport.php",
			"pdftable_old" => $base . "/pdftable_old.php",
			"pdfobject"    => $base . "/PdfObject.php",
			"pdfcontainer" => $base . "/PdfContainer.php",
			"pdftextbox"   => $base . "/PdfTextBox.php",
			"pdftablerow"  => $base . "/PdfTableRow.php",
			"pdftablecell" => $base . "/PdfTableCell.php",
			"pdftable"     => $base . "/PdfTable.php",
			"pdfimage"     => $base . "/PdfImage.php",
			"pdfcircle"    => $base . "/PdfCircle.php",
			"pdfline"      => $base . "/PdfLine.php"
		);
	}
}