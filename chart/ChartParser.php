<?
class ChartParser
{
	function getTagList()
	{
		return array('piechart', 'horizontalbarchart', 'verticalbarchart', 'linechart');
	}
	
	function &getNewContainer($node, &$context)
	{
		$tagName = strtolower( $node->getName() );
		
		switch($tagName)
		{
			case 'piechart':
				$object = &new PieChart($context);
				if($node->hasAttribute('depth'))
					$object->setDepth($node->getAttribute('depth'));
				break;
			case 'horizontalbarchart':
				$object = &new HorizontalBarChart($context);
				if($node->hasAttribute('grouping'))
				{
					$grouping = $node->getAttribute('grouping');
					$object->setGroupType($grouping);
				}
				
				if($node->hasAttribute('barcolor'))
					$object->setBarColor($node->getAttribute('barcolor'));
				
				if($node->hasAttribute('barspaceratio'))
				{
					$object->setDataEntryBarSpaceRatio($node->getAttribute('barspaceratio'));
				}
				break;
			case 'verticalbarchart':
				if($node->hasAttribute('grouping'))
					$grouping = $node->getAttribute('grouping');
				else
					$grouping = 'simple';
				
				switch($grouping)
				{
					case 'deep':
						$object = &new DeepVerticalBarChart($context);
						break;
					case 'side':
						$object = &new SideVerticalBarChart($context);
						break;
					default:
						$object = &new VerticalBarChart($context);
						break;
				}
				
				if($node->hasAttribute('barcolor'))
					$object->setBarColor($node->getAttribute('barcolor'));
				break;
			case 'linechart':
				$object = &new LineChart($context);
				break;
		}
		
		return $object;
	}
}
?>