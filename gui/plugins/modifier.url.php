<?php
/**
 * Smarty url plugin
 *
 * Canonicalizes URLs using the zoop url() util. All URLs should be passed through this
 * or the url() function. No need to pass 'em through both though :)
 *
 * @ingroup Smarty
 * @ingroup plugins
 * @author Justin Hileman {@link http://justinhileman.com}
 * @see url()
 *
 * @param string $string
 * @return string Canonicalized URL string.
 */
function smarty_modifier_url($string) {
    return url($string);
}