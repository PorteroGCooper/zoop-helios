<?
class ChartObjectParser
{
	function handle(&$curNode, &$styleStack, &$curContainer)
	{
		$tagName = strtolower( $curNode->getName() );
		
		switch($tagName)
		{
			case 'chartplot':
				$newObject = &new ChartPlot($curContainer->getContext());
				$curContainer->addChildObject($newObject);
				break;
		}
	}
}
?>