<?php
/**
* @category zoop
* @package zone
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
 * component_zone 
 * 
 * @uses component
 * @package 
 * @version $id$
 * @copyright 1997-2007 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class component_graphic extends component
{
	function getRequiredComponents()
	{
		return array('xml', 'fpdf', 'pdf');
	}
	
	function getIncludes()
	{
		return array(
			'smartgraphic' => $this->getBasePath() . '/SmartGraphic.php',
			'smartpdf' => $this->getBasePath() . '/SmartPdf.php',
			'smartimage' => $this->getBasePath() . '/SmartImage.php',
			'smartimagemap' => $this->getBasePath() . '/SmartImageMap.php',
			'bmfpdf' => $this->getBasePath() . '/BMFPdf.php',
			'graphicrospdfengine' => $this->getBasePath() . '/GraphicRosPdfEngine.php',
			
			//	the graphic contexts
			'graphiccontext' => $this->getBasePath() . '/GraphicContext.php',
			'imagecontext' => $this->getBasePath() . '/ImageContext.php',
			'fpdfcontext' => $this->getBasePath() . '/FPdfContext.php',
			'rospdfcontext' => $this->getBasePath() . '/RosPdfContext.php',
			'imagecontext' => $this->getBasePath() . '/ImageContext.php',
			'imagemapcontext' => $this->getBasePath() . '/ImageMapContext.php',

			//	the graphic objects
			'graphicobject' => $this->getBasePath() . '/GraphicObject.php',
			'graphicdiv' => $this->getBasePath() . '/GraphicDiv.php',
			'graphicdocument' => $this->getBasePath() . '/GraphicDocument.php',
			'graphictextstyle' => $this->getBasePath() . '/GraphicTextStyle.php',
			'graphictextstylestack' => $this->getBasePath() . '/GraphicTextStyleStack.php',
			'graphictextrun' => $this->getBasePath() . '/GraphicTextRun.php',
			'graphichardbrokenline' => $this->getBasePath() . '/GraphicHardBrokenLine.php',
			'graphiclist' => $this->getBasePath() . '/GraphicList.php',
			'graphiclistitem' => $this->getBasePath() . '/GraphicListItem.php',
			'graphictable' => $this->getBasePath() . '/GraphicTable.php',
			'graphictablerow' => $this->getBasePath() . '/GraphicTableRow.php',
			'graphictablecell' => $this->getBasePath() . '/GraphicTableCell.php',
			'graphicsoftbrokenline' => $this->getBasePath() . '/GraphicSoftBrokenLine.php',
			'graphicrectangle' => $this->getBasePath() . '/GraphicRectangle.php',
			'graphicimage' => $this->getBasePath() . '/GraphicImage.php',
			'graphicline' => $this->getBasePath() . '/GraphicLine.php',
			'graphictext' => $this->getBasePath() . '/GraphicText.php',
			'graphicpagebreak' => $this->getBasePath() . '/GraphicPageBreak.php',
			'graphicraw' => $this->getBasePath() . '/GraphicRaw.php',
			'graphiccolumnset' => $this->getBasePath() . '/GraphicColumnSet.php',
			'graphiccolumn' => $this->getBasePath() . '/GraphicColumn.php'
		);
		
	}
}
?>
