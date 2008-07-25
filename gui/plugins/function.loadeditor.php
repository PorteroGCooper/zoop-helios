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
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 *
 * @author   Steve Francia
 * @version  1.2.5
 *
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_loadeditor($params, &$smarty)
{
	global $sGlobals;

isset($smarty->_tpl_vars) ? $SCRIPT_BASE = $smarty->_tpl_vars['SCRIPT_BASE'] : $SCRIPT_BASE = SCRIPT_BASE;
isset($smarty->_tpl_vars) ? $SCRIPT_URL = $smarty->_tpl_vars['SCRIPT_URL'] : $SCRIPT_URL = SCRIPT_URL;

	$width = 600;
	$height = 100;
	$type = "fulleditor";
	$editor = rand();

	foreach ($params as $_key=>$_value) {
		switch ($_key) {
			case 'type':
//			case 'editor':
			case 'value':
			case 'name':
			case 'height':
			case 'width':
			$$_key = $_value;
			break;
		}
	}

	$valstring = "";

	switch ($type)
	{
		case 'editor':
			$editvar = "oEdit" . $editor;
			$height > 60 ? $height : $height = 100;
			$width > 300 ? $width : $width = 600;
			$formpart ="<div align=\"left\">" .
				"<textarea id=\"$name\" name=\"$name\">" .
					stripslashes(encodeHTML($value))
				. "</textarea>" .

				"<script>\r" .
					"var $editvar = new InnovaEditor(\"$editvar\");\r" .

				"	$editvar.width=$width;\r
					$editvar.height=$height;\r
					$editvar.css=\"{$SCRIPT_BASE}/public/resources/css/SSS.css\";\r
					$editvar.REPLACE(\"$name\");\r
				</script>\r" .
				"</div>\r";
			break;
		case 'fulleditor':
			$editvar = "oEdit" . $editor;
			$height > 60 ? $height : $height = 100;
			$width > 300 ? $width : $width = 600;
			$formpart ="<div align=\"left\">" .
				"<textarea id=\"$name\" name=\"$name\">" .
					stripslashes(encodeHTML($value))
				. "</textarea>" .

				"<script>\r" .
					"var $editvar = new InnovaEditor(\"$editvar\");\r" .

				"	$editvar.width=$width;\r
					$editvar.height=$height;\r
					$editvar.features=[\"Save\",\"FullScreen\",\"Preview\",
								\"Search\",\"SpellCheck\",\"|\",
								\"Superscript\",\"Subscript\",\"|\",\"LTR\",\"RTL\",\"|\",
								\"Table\",\"Guidelines\",\"Absolute\",\"|\",
								\"Flash\",\"Media\",\"|\",\"Image\",\"|\",
								\"Form\",\"Characters\",\"Clean\",\"XHTMLSource\",\"BRK\",
								\"PasteWord\",\"PasteText\",\"|\",
								\"Undo\",\"Redo\",\"|\",\"Hyperlink\",\"InternalLink\",\"Bookmark\",\"|\",
								\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"JustifyFull\",\"|\",
								\"Numbering\",\"Bullets\",\"|\",\"Indent\",\"Outdent\",\"|\",
								\"Line\",\"RemoveFormat\",\"BRK\",
								\"StyleAndFormatting\",\"TextFormatting\",\"ListFormatting\",
								\"BoxFormatting\",\"ParagraphFormatting\",\"CssText\",\"Styles\",\"|\",
								\"Cut\",\"Copy\",\"Paste\",\"Paragraph\",\"FontName\",\"FontSize\",\"|\",
								\"Bold\",\"Italic\",\"Underline\",\"Strikethrough\",\"|\",
								\"ForeColor\",\"BackColor\"];\r

					$editvar.css=\"{$SCRIPT_BASE}/public/resources/css/SSS.css\";\r
					$editvar.REPLACE(\"$name\");\r
				</script>\r" .
				"</div>\r";
			break;
		case 'ssblockeditor':
		case 'SSblockeditor':
			$editvar = "oEdit" . $editor;
			$height > 150 ? $height : $height = 350;
			$width > 300 ? $width : $width = 600;
			$formpart ="<div align=\"left\">" .
				"<textarea id=\"$name\" name=\"$name\">" .
					$value
				. "</textarea>" .

				"<script>\r" .
					"var $editvar = new InnovaEditor(\"$editvar\");\r" .

				"	$editvar.width=$width;\r
					$editvar.height=$height;\r " .
"
					$editvar.features=[\"Save\",\"FullScreen\",\"Preview\",
								\"Search\",\"SpellCheck\",\"|\",
								\"Superscript\",\"Subscript\",\"|\",\"LTR\",\"RTL\",\"|\",
								\"Table\",\"Guidelines\",\"Absolute\",\"|\",
								\"Flash\",\"Media\",\"|\",\"Image\",\"|\",
								\"Form\",\"Characters\",\"Clean\",\"XHTMLSource\",\"BRK\",
								\"PasteWord\",\"PasteText\",\"|\",
								\"Undo\",\"Redo\",\"|\",\"Hyperlink\",\"InternalLink\",\"Bookmark\",\"|\",
								\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"JustifyFull\",\"|\",
								\"Numbering\",\"Bullets\",\"|\",\"Indent\",\"Outdent\",\"|\",
								\"Line\",\"RemoveFormat\",\"BRK\",
								\"StyleAndFormatting\",\"TextFormatting\",\"ListFormatting\",
								\"BoxFormatting\",\"ParagraphFormatting\",\"CssText\",\"Styles\",\"|\",
								\"Cut\",\"Copy\",\"Paste\",\"Paragraph\",\"FontName\",\"FontSize\",\"|\",
								\"Bold\",\"Italic\",\"Underline\",\"Strikethrough\",\"|\",
								\"ForeColor\",\"BackColor\"];\r
" .
"					$editvar.cmdInternalLink = \"modalDialogShow('{$SCRIPT_URL}/editor/InternalLinks',500,400)\";\r
					$editvar.cmdAssetManager = \"modalDialogShow('{$SCRIPT_BASE}/public/resources/js/Editor/assetmanager/assetmanager.php',640,465)\";\r
					$editvar.useBR=true;\r
					$editvar.publishingPath = \"{$SCRIPT_URL}\";\r
					$editvar.css=\"{$SCRIPT_BASE}/public/resources/css/SSS.css\";\r
					$editvar.REPLACE(\"$name\");\r
				</script>\r" .
				"</div>\r";
			break;
		case 'minieditor':
			$editvar = "oEdit" . $editor;
			$height > 60 ? $height : $height = 100;
			$width > 300 ? $width : $width = 400;
			$formpart ="<div align=\"left\">" .
				"<textarea id=\"$name\" name=\"$name\">" .
					stripslashes(encodeHTML($value))
				. "</textarea>" .

				"<script>\r" .
					"var $editvar = new InnovaEditor(\"$editvar\");\r" .

				"	$editvar.width=$width;\r
					$editvar.height=$height;\r
					$editvar.features=[\"StyleAndFormatting\",
						\"TextFormatting\",\"ListFormatting\",\"BoxFormatting\",
						\"ParagraphFormatting\",\"CssText\",\"Styles\",
						\"Cut\",\"Copy\",\"Paste\",\"|\",\"Undo\",\"Redo\",\"|\",
						\"Bold\",\"Italic\",\"Underline\",\"|\",
						\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"JustifyFull\",\"|\",
						\"Numbering\",\"Bullets\",\"|\",
						\"ForeColor\"];\r

					$editvar.css=\"{$SCRIPT_BASE}/public/resources/css/SSS.css\";\r
					$editvar.REPLACE(\"$name\");\r
				</script>\r" .
				"</div>\r";
			break;
	}

	return $formpart;
}

function encodeHTML($sHTML)
{
	$sHTML=ereg_replace("&","&amp;",$sHTML);
	$sHTML=ereg_replace("<","&lt;",$sHTML);
	$sHTML=ereg_replace(">","&gt;",$sHTML);
	return $sHTML;
}


/* vim: set expandtab: */

?>
