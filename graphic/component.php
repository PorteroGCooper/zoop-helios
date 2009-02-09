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
 * component_zone 
 * 
 * @ingroup components
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_graphic extends component {
	function component_graphic() {
		$this->requireComponent('xml');
		$this->requireComponent('fpdf');
		$this->requireComponent('pdf');
	}
	
	function getIncludes() {
		$base = $this->getBasePath();
		return array(
			'smartgraphic' => $base . '/SmartGraphic.php',
			'smartpdf' => $base . '/SmartPdf.php',
			'smartimage' => $base . '/SmartImage.php',
			'smartimagemap' => $base . '/SmartImageMap.php',
			'bmfpdf' => $base . '/BMFPdf.php',
			'graphicrospdfengine' => $base . '/GraphicRosPdfEngine.php',
			
			//	the graphic contexts
			'graphiccontext' => $base . '/GraphicContext.php',
			'imagecontext' => $base . '/ImageContext.php',
			'fpdfcontext' => $base . '/FPdfContext.php',
			'rospdfcontext' => $base . '/RosPdfContext.php',
			'imagecontext' => $base . '/ImageContext.php',
			'imagemapcontext' => $base . '/ImageMapContext.php',

			//	the graphic objects
			'graphicobject' => $base . '/GraphicObject.php',
			'graphicdiv' => $base . '/GraphicDiv.php',
			'graphicdocument' => $base . '/GraphicDocument.php',
			'graphictextstyle' => $base . '/GraphicTextStyle.php',
			'graphictextstylestack' => $base . '/GraphicTextStyleStack.php',
			'graphictextrun' => $base . '/GraphicTextRun.php',
			'graphichardbrokenline' => $base . '/GraphicHardBrokenLine.php',
			'graphiclist' => $base . '/GraphicList.php',
			'graphiclistitem' => $base . '/GraphicListItem.php',
			'graphictable' => $base . '/GraphicTable.php',
			'graphictablerow' => $base . '/GraphicTableRow.php',
			'graphictablecell' => $base . '/GraphicTableCell.php',
			'graphicsoftbrokenline' => $base . '/GraphicSoftBrokenLine.php',
			'graphicrectangle' => $base . '/GraphicRectangle.php',
			'graphicimage' => $base . '/GraphicImage.php',
			'graphicline' => $base . '/GraphicLine.php',
			'graphictext' => $base . '/GraphicText.php',
			'graphicpagebreak' => $base . '/GraphicPageBreak.php',
			'graphicraw' => $base . '/GraphicRaw.php',
			'graphiccolumnset' => $base . '/GraphicColumnSet.php',
			'graphiccolumn' => $base . '/GraphicColumn.php'
		);
	}
}