<?
class ChartObjectParser
{
	function handle(&$curNode, &$styleStack, &$curContainer)
	{
		$newObject = &new CustomObject($curContainer->getContext());
		$curContainer->addChildObject($newObject);
	}
}
?>