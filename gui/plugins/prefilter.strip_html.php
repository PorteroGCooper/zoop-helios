<?
function smarty_prefilter_strip_html($tpl_source, &$smarty)
{
	//die("here");
	//echo_r($tpl_source);
	//echo("<BR><BR><BR>");
	//$found = ereg('{\$[^|}]*}',$tpl_source, $matches);
	//echo_r($matches);
	//echo((int)$found);
    $source = ereg_replace('{(\$[^|}]*)}','{\\1|escape:"html"}',$tpl_source);
    //echo_r($source);
    //die();
    return $source;
}
?>
