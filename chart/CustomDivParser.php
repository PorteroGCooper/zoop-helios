<?
class PieChartParser
{
	function &getNewContainer($tagName, &$context)
	{
		$object = &new CustomDiv($context);
		return $object;
	}
}
?>