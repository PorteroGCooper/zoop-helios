<?php

/**
* Zoop Smarty plugin
* @package Smarty
* @subpackage plugins
*/

/**
* Smarty zcache prefilter plugin
*
* Clip template source from {zcache} blocks into a new file and replace
* them with {include_zcache} tags that permit isolated caching and private
* delimiters
*
* Adapted from clipcache written by boots
*
* @file        prefilter.zcache.php
* @version     0.2.0 2007-Feb-09
* @since       2005-APR-08
*
* @author      Steve Francia {steve@takkle.com}
* @author      Andrew Hayward {andrew@takkle.com}
* @author      boots {jayboots ~ yahoo com}
* @copyright  takkle inc.,brainpower, boots, 2004-2007
* @license     LGPL 2.1
* @link        http://www.phpinsider.com/smarty-forum/viewtopic.php?p=19733#19733
* @link        http://www.zoopframework.com/
*
* @param string $source
* @param Smarty_Compiler $compiler
*
* This filter observes the following tag attributes on {zcache} blocks:
*
* #param id required unique id of zcache block within template
* #param group required specify cache build group
* #param ttl required time to live for template part/group
* #param ldelim optional specify the left delimiter to use for included content
* #param rdelim optional specify the right delimiter to use for included content
*/
function smarty_prefilter_zcache($source, &$compiler)
{
	if (!defined("gui_caching") || gui_caching == 0)
		return $source;

	// setup
	require_once $compiler->_get_plugin_filepath( 'outputfilter', 'trimwhitespace' );
	$ld = $compiler->left_delimiter;
	$rd = $compiler->right_delimiter;
	$ldq = preg_quote($ld, '~');
	$rdq = preg_quote($rd, '~');

	preg_match_all("~({$ldq}\s*/*zcache(.*?)\s*{$rdq})~s", $source, $_match, PREG_OFFSET_CAPTURE);

	$template_tags = $_match[1];
	$template_tag_attrs = $_match[2];

	$blocks = array();
	$clip_count = 0;
	$offset = 0;
	$blockCount = 0;
	foreach ( $template_tags as $i => $tag )
	{
		if ( preg_match ( "~{$ldq}\s*/~", $tag[0] ) )
		{
			$clip_count --;
			if ( $clip_count == 0 )
			{
				$blocks[$blockCount]['content'] = substr ( $source, $offset, $tag[1] - $offset );
				$blocks[$blockCount]['len'] = ( $tag[1] + strlen($tag[0]) ) - $blocks[$blockCount]['offset'];
				$blockCount ++;
			}
		}
		else
		{
			$clip_count ++;
			if ( $clip_count == 1 )
			{
				$offset = $tag[1] + strlen ( $tag[0] );
				$blocks[$blockCount]['attrs'] = trim ( $template_tag_attrs[$i][0] );
				$blocks[$blockCount]['offset'] = $offset - strlen ( $tag[0] );
			}
		}
	}

	$replacements = array();

	foreach ( $blocks as $block) {
		$block['attrs'] = _zcache_extract_attrs( $block['attrs'] , $compiler);
		$replacements[] =_zcache_magic($block, $compiler);
	}

	// replace clip blocks
	$replace_offset = 0;
	$placeholder = "@@@SMARTY:ZOOP:ZCACHE@@@";
	$phLen = strlen($placeholder);
	foreach ($blocks as $block)
	{
		$source = substr_replace($source, $placeholder, $replace_offset + $block['offset'], $block['len']);
		$replace_offset += ($phLen-$block['len'] );
	}

	smarty_outputfilter_trimwhitespace_replace( $placeholder, $replacements, $source );

	return $source;
}

function _zcache_extract_attrs($tag_args, &$compiler)
{
        /* Tokenize tag attributes. */
        preg_match_all('~(?:' . $compiler->_obj_call_regexp . '|' . $compiler->_qstr_regexp . ' | (?>[^"\'=\s]+)
                         )+ |
                         [=]
                        ~x', $tag_args, $match);
        $tokens       = $match[0];

	$array = array();

	for ($i = 0; $i < count ($tokens); $i += 3)
	{
		$array[$tokens[$i]] =  $tokens[$i + 2];
	}

	 return $array;
}

function _zcache_magic($block, &$compiler)
{
	static $inst_id = 1;
	if ( $inst_id == 1)
		$inst_id += rand();

	$ld = $compiler->left_delimiter;
	$rd = $compiler->right_delimiter;
	$params = $block['attrs'];

	foreach ( array( 'cache_id'=>'cache_id', 'ttl'=>'cache_lifetime' ) as $required=>$mapto )
	{
		if ( !array_key_exists( $required, $params ) ) {
			$compiler->_syntax_error( "zcache: '$required' param missing. Aborted.", E_USER_WARNING );
			return;
		} else {
			$$mapto = $params[$required];
		}
	}

	foreach ( array( 'rdelim'=>$rd, 'ldelim'=>$ld, 'id' =>  $inst_id++) as $optional=>$default ) {
		${"_{$optional}"} = $default;
		$$optional = ( array_key_exists( $optional, $params ) )
			? $params[$optional] // substr( $params[$optional], 1, strlen( $params[$optional] ) - 2 )
			: $default;
	}

	// write the clip block file source template
	$write_path = rtrim( $compiler->compile_dir, "/\\" ) . DIRECTORY_SEPARATOR;

// 	$id = md5($id); //crc32($id); // so we can accept any charset.
	$file_name = $compiler->_current_file.'#' . $id;

	if (defined(gui_look))
		$filenametowrite = $write_path . 'zcache' . DIRECTORY_SEPARATOR . gui_look . DIRECTORY_SEPARATOR . $file_name;
	else
		$filenametowrite = $write_path . 'zcache' . DIRECTORY_SEPARATOR . $file_name;

	require_once SMARTY_CORE_DIR . 'core.write_file.php';
	smarty_core_write_file( array( 'filename'=>$filenametowrite, 'contents'=>$block['content'], 'create_dirs'=>true ), $compiler );

	// prepare replacement source for the clip block
	if ( $ldelim == $ld && $rdelim == $rd ) {
		$replacement = $ld . 'include_zcache file="' . $file_name . '" cache_id="' . $cache_id . '" cache_lifetime=' . $cache_lifetime . $rd;

	} else {
		$replacement = $ld . 'include_zcache file="' . $file_name . '" cache_id="' . $cache_id . '" cache_lifetime=' . $cache_lifetime . ' ldelim="' . $ldelim . '" rdelim="' . $rdelim.'"' . $rd;
	}

	return $replacement;
}

?>