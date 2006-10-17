<?
class ChartObjectParser
{
	function getTagList()
	{
		return array('chartplot', 'chartdata', 'chartlegend', 'chartstring', 'chartcat', 'chartgroup');
	}
	
	function handle(&$curNode, &$styleStack, &$curContainer)
	{
		$tagName = strtolower( $curNode->getName() );
		
		switch($tagName)
		{
			case 'chartplot':
				$newObject = &new ChartPlot($curContainer->getContext());
				$curContainer->addChildObject($newObject);
				
				$chart = &$this->getParent($curContainer, 'chart');
				if($curNode->hasAttribute('height'))
					$chart->setPlotHeight($curNode->getAttribute('height'));
				
				if($curNode->hasAttribute('depthvector'))
				{
					$parts = explode(',', $curNode->getAttribute('depthvector'));
					$chart->setDepth(trim($parts[0]), trim($parts[1]));
				}
				break;
			case 'chartlegend':
				$newObject = &new ChartLegend($curContainer->getContext());
				$curContainer->addChildObject($newObject);
				
				$chart = &$this->getParent($curContainer, 'chart');
				break;
			case 'chartstring':
				$newObject = &new ChartString($curContainer->getContext());
				$curContainer->addChildObject($newObject);
				
				$chart = &$this->getParent($curContainer, 'chart');
				if($curNode->hasAttribute('text'))
					$newObject->setText($curNode->getAttribute('text'));
				if($curNode->hasAttribute('size'))
					$newObject->setSize($curNode->getAttribute('size'));
				break;
			case 'chartcat':
				//echo_r($curNode->getAttributes());
				$chart = &$this->getParent($curContainer, 'chart');
				$color = HexToRgb($curNode->getAttribute('color'));
				$chart->addLegendEntry($curNode->getAttribute('name'), 
						$curNode->getAttribute('text'), array($color[0], $color[1], $color[2]));
				break;
			case 'chartgroup':
				$chart = &$this->getParent($curContainer, 'chart');
				$group = &$chart->addGroup($curNode->getAttribute('name'));
				$group->setText($curNode->getAttribute('text'));
				break;
			case 'chartdata':
				$chart = &$this->getParent($curContainer, 'chart');
				$chart->addDataEntry($curNode->getAttributes());
				break;
		}
	}
	
	function &getParent(&$curContainer, $objectType)
	{
		$curParent = &$curContainer;
		
		while($curParent)
		{
			if(is_a($curParent, $objectType))
			{
				return $curParent;
			}
			
			$curParent = &$curParent->parent;
		}
		
		trigger_error("$objectType not found");
	}
}
?>