<?php
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
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 *
 * @author   Steve Francia
 * @version  1.2
 *
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_loadeditor($params, &$smarty)
{
	global $sGlobals;

//  echo_r($smarty);

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
					$editvar.features=[\"FullScreen\",\"Preview\",\"Search\",\"SpellCheck\",
							\"Cut\",\"Copy\",\"Paste\",\"PasteWord\",\"|\",\"Undo\",\"Redo\",\"|\",
							\"ForeColor\",\"BackColor\",\"|\",\"Bookmark\",\"Hyperlink\",
							\"HTMLFullSource\",\"BRK\"
							,\"Numbering\",\"Bullets\",\"|\",\"Indent\",\"Outdent\",\"LTR\",\"RTL\",\"|\",
							\"Table\",\"Guidelines\",\"Absolute\",\"|\",\"Characters\",\"Line\",
							\"Form\",\"Clean\",\"ClearAll\",\"BRK\",
							\"StyleAndFormatting\",\"TextFormatting\",\"ListFormatting\",\"BoxFormatting\",
							\"ParagraphFormatting\",\"CssText\",\"Styles\",\"|\",
							\"Paragraph\",\"FontName\",\"FontSize\",\"|\",
							\"Bold\",\"Italic\",
							\"Underline\",\"Strikethrough\",\"|\",\"Superscript\",\"Subscript\",\"|\",
							\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"JustifyFull\"];\r

					$editvar.REPLACE(\"$name\");\r
				</script>\r" .
				"</div>\r";
			break;
		case 'SSblockeditor':
			$editvar = "oEdit" . $editor;
			$height > 150 ? $height : $height = 350;
			$width > 300 ? $width : $width = 600;
			$formpart ="<div align=\"left\">" .
				"<textarea id=\"$name\" name=\"$name\">" .
					stripslashes(encodeHTML($value))
				. "</textarea>" .

				"<script>\r" .
					"var $editvar = new InnovaEditor(\"$editvar\");\r" .

				"	$editvar.width=$width;\r
					$editvar.height=$height;\r
					$editvar.features=[\"FullScreen\",\"Preview\",\"Search\",\"SpellCheck\",
							\"Cut\",\"Copy\",\"Paste\",\"PasteWord\",\"|\",\"Undo\",\"Redo\",\"|\",
							\"ForeColor\",\"BackColor\",\"|\",\"Bookmark\",\"Hyperlink\",\"InternalLink\",
							\"HTMLFullSource\",\"BRK\"
							,\"Numbering\",\"Bullets\",\"|\",\"Indent\",\"Outdent\",\"LTR\",\"RTL\",\"|\",
							\"Table\",\"Guidelines\",\"Absolute\",\"|\",\"Characters\",\"Line\",
							\"Form\",\"Clean\",\"ClearAll\",\"BRK\",
							\"StyleAndFormatting\",\"TextFormatting\",\"ListFormatting\",\"BoxFormatting\",
							\"ParagraphFormatting\",\"CssText\",\"Styles\",\"|\",
							\"Paragraph\",\"FontName\",\"FontSize\",\"|\",
							\"Bold\",\"Italic\",
							\"Underline\",\"Strikethrough\",\"|\",\"Superscript\",\"Subscript\",\"|\",
							\"JustifyLeft\",\"JustifyCenter\",\"JustifyRight\",\"JustifyFull\"];\r

					$editvar.cmdInternalLink = \"modelessDialogShow('{$smarty->_tpl_vars['SCRIPT_URL']}/InternalLinks',365,270)\";\r
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
