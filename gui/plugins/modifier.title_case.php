<?php
/**
 * Smarty Title Case plugin
 *
 * Converts string to title case (much cooler than just capitalizing it)
 *
 * @ingroup Smarty
 * @ingroup plugins
 * @author Justin Hileman {@link http://justinhileman.com}
 * @see nv_title_case()
 *
 * @param string $string
 * @return string Capitalized input string.
 */
function smarty_modifier_title_case($string) {
    return nv_title_case($string);
}