<?
class GraphicList extends GraphicObject
{
	var $items;
	var $nestLevel;
	var $bulletType;
	
	function GraphicList(&$context)
	{
		$this->GraphicObject($context);
		$this->items = array();
		$this->nestLevel = 1;
		$this->bulletType = 'filled_circle';
	}
	
	function addItem(&$newItem)
	{
		assert( is_a($newItem, 'GraphicListItem') );
		
		$this->items[] = &$newItem;
	}
	
	/*
	function &getNewList()
	{
		$object = &new GraphicList($this->context);
		$object->setNestLevel($this->nestLevel + 1);
		$object->setParent($this);
		$this->addChild($object);
		return $object;
	}
	*/
	
	function &getNewListItem()
	{
		$object = &new GraphicListItem($this->context);
		$object->setParent($this);
		$this->addItem($object);
		return $object;
	}
	
	function setNestLevel($nestLevel)
	{
		$this->nestLevel = $nestLevel;
		
		if($this->nestLevel > 1 && $this->bulletType != NULL)
			$this->bulletType = 'circle';
	}
	
	function getNestLevel()
	{
		return $this->nestLevel;
	}
	
	function setBulletType($bulletType)
	{
		$this->bulletType = $bulletType;
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
		$cury = $y;
		
		$indent = 20;
		$contentWidth = $width - $indent;
		$curx = $x + $indent;
		$itemNum = 1;
		foreach($this->items as $itemKey => $dummyItem)
		{
//			echo "$reallyDraw $itemKey $cury<br>";
			
			$thisItem = &$this->items[$itemKey];
			$thisItem->setAlignment('left');
			$height = $thisItem->draw($curx, $cury, $contentWidth, $reallyDraw);
			
			//$thisItem->getHeight($contentWidth);
			
//			echo "graphicList : draw ; $reallyDraw ; $height<br>";
			
			if($this->bulletType != NULL)
			{
				if($reallyDraw)
				{
					switch($this->bulletType)
					{
						case 'filled_circle':
							$this->context->addCircle($x + 12, $cury + 7, 2, 'F');
							break;
						case 'circle':
							$this->context->addCircle($x + 12, $cury + 7, 2, 'D');
							break;
						case 'number':
							$this->context->addText($x + 10, $cury + 10, "$itemNum.");
							break;
						case 'lower':
							$char = chr($itemNum + 96);
							$this->context->addText($x + 10, $cury + 10, "$char.");
							break;
						default:
							trigger_error('invalid bullet type: ' . $this->bulletType);
							break;
					}					
				}
			}
			$cury += $height;
			$itemNum++;
		}
		
		return $cury - $y;
	}
}

?>