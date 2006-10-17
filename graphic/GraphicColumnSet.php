<?
class GraphicColumnSet extends GraphicDiv
{
	var $columns;
	
	function GraphicColumnSet(&$context)
	{
		$this->GraphicDiv($context);
		$this->columns = array();
	}
	
	function isBreakable()
	{
		return 1;
	}
	
	function getMinHeight($width)
	{
		$keys = array_keys($this->children);
		return $this->children[$keys[0]]->getHeight($width);
	}
	
	function &getNewColumn()
	{
		$object = &new GraphicColumn();
		$this->columns[] = &$object;
		
		return $object;
	}
	
	function calcColumns()
	{
		//echo count($this->columns);
	}
	
	function draw($x, $y, $width = NULL, $reallyDraw = 1)
	{
		$curx = $x;
		$cury = $y;
		
		$colNum = 0;
		while(!$this->doneDrawing())
		{
			if($colNum >= count($this->columns))
				break;
			
			$cury += $this->header->draw($curx, $cury, $this->columns[$colNum]->getWidth(), $reallyDraw);
			
			GraphicDiv::draw($curx, $cury, $this->columns[$colNum]->getWidth(), $reallyDraw);			
			
			$curx += $this->columns[$colNum]->getWidth();
			$cury = $y;
			$colNum++;
		}
	}
}
?>