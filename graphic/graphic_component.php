<?php
/**
* @category zoop
* @package zone
*/

// Copyright (c) 2005 Supernerd LLC and Contributors.
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
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class component_graphic extends component
{
	/**
	 * component_zone 
	 * 
	 * @access public
	 * @return void
	 */
	function component_graphic()
	{
		$this->requireComponent('xml');
		$this->requireComponent('fpdf');
		$this->requireComponent('pdf');
	}
	
	function getIncludes()
	{
		return array(
			'smartgraphic' => dirname(__file__) . '/SmartGraphic.php',
			'smartpdf' => dirname(__file__) . '/SmartPdf.php',
			'smartimage' => dirname(__file__) . '/SmartImage.php',
			'smartimagemap' => dirname(__file__) . '/SmartImageMap.php',
			'bmfpdf' => dirname(__file__) . '/BMFPdf.php',
			'graphicrospdfengine' => dirname(__file__) . '/GraphicRosPdfEngine.php',
			//	the graphic contexts
			'graphiccontext' => dirname(__file__) . '/GraphicContext.php',
			'imagecontext' => dirname(__file__) . '/ImageContext.php',
			'fpdfcontext' => dirname(__file__) . '/FPdfContext.php',
			'rospdfcontext' => dirname(__file__) . '/RosPdfContext.php',
			'imagecontext' => dirname(__file__) . '/ImageContext.php',
			'imagemapcontext' => dirname(__file__) . '/ImageMapContext.php',

			//	the graphic objects
			'graphicobject' => dirname(__file__) . '/GraphicObject.php',
			'graphicdiv' => dirname(__file__) . '/GraphicDiv.php',
			'graphicdocument' => dirname(__file__) . '/GraphicDocument.php',
			'graphictextstyle' => dirname(__file__) . '/GraphicTextStyle.php',
			'graphictextstylestack' => dirname(__file__) . '/GraphicTextStyleStack.php',
			'graphictextrun' => dirname(__file__) . '/GraphicTextRun.php',
			'graphichardbrokenline' => dirname(__file__) . '/GraphicHardBrokenLine.php',
			'graphiclist' => dirname(__file__) . '/GraphicList.php',
			'graphiclistitem' => dirname(__file__) . '/GraphicListItem.php',
			'graphictable' => dirname(__file__) . '/GraphicTable.php',
			'graphictablerow' => dirname(__file__) . '/GraphicTableRow.php',
			'graphictablecell' => dirname(__file__) . '/GraphicTableCell.php',
			'graphicsoftbrokenline' => dirname(__file__) . '/GraphicSoftBrokenLine.php',
			'graphicrectangle' => dirname(__file__) . '/GraphicRectangle.php',
			'graphicimage' => dirname(__file__) . '/GraphicImage.php',
			'graphicline' => dirname(__file__) . '/GraphicLine.php',
			'graphictext' => dirname(__file__) . '/GraphicText.php',
			'graphicpagebreak' => dirname(__file__) . '/GraphicPageBreak.php',
			'graphicraw' => dirname(__file__) . '/GraphicRaw.php',
			'graphiccolumnset' => dirname(__file__) . '/GraphicColumnSet.php',
			'graphiccolumn' => dirname(__file__) . '/GraphicColumn.php'
		);
		
	}


	/**
	 * run 
	 * 
	 * @access public
	 * @return void
	 */
	function run()
	{
	}
}
?>
