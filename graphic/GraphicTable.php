<?
class GraphicTable extends GraphicObject
{
	var $rows;
	var $border;
	var $colWidths;
	var $rowSpacing;
	var $alignment;
	var $breakable;
	var $startRow;
	var $header;
	var $columnSpacingType;
	
	function GraphicTable(&$context)
	{
		$this->GraphicObject($context);
		$this->rows = array();
		$this->colWidths = array();
		$this->alignment = 'left';
		$this->breakable = 1;
		$this->startRow = 0;
		$this->autospaceType = 'even';
	}
	
	function setAutospaceType($type)
	{
		$this->autospaceType = $type;
	}
	
	function setBreakable($breakable)
	{
		$this->breakable = $breakable;
	}
	
	function isBreakable()
	{
		return $this->breakable;
	}
	
	function getMinHeight($width)
	{
		assert(!$this->doneDrawing());
		
		//	if it is breakable then the min height is the height of the first row
		//	if it's not breakable then the min height is the height of the entire table
		if($this->isBreakable())
		{
			$keys = array_keys($this->rows);
			$minHeight = $this->rows[$keys[$this->startRow]]->getMinHeight($width);			
		}
		else
		{
			$minHeight = $this->getHeight($width);
		}
		
		//	check to make sure this can fit on the page
		
		$document = $this->getAncestor('GraphicDocument');
		
		if( $minHeight > $document->getPageContentHeight() )
		{
			trigger_error("table row can't fit on a single page");
			die();
		}
		
		return $minHeight;
	}
	
	function doneDrawing()
	{
		return $this->startRow < count($this->rows) ? 0 : 1;
	}
	
	function setBorder($border)
	{
		$this->border = (integer)$border;
	}
	
	function getBorder()
	{
		return $this->border;
	}
	
	function setAlignment($alignment)
	{
		$this->alignment = $alignment;
	}
	
	function getAlignment()
	{
		return $this->alignment;
	}
	
	function setRowSpacing($rowSpacing)
	{
		$this->rowSpacing = (integer)$rowSpacing;
	}
	
	function getRowSpacing()
	{
		return $this->rowSpacing ? $this->rowSpacing : 0;
	}
	
	function getWidth()
	{
		if( isset($this->width) )
			return $this->width;
		else
			return $this->parent->getContentWidth();
	}
	
	function setColWidth($colNum, $width)
	{
		$this->colWidths[$colNum] = $width;
	}
	
	function hasColWidth($colNum)
	{
		return isset($this->colWidths[$colNum]) ? 1 : 0;
	}
	
	function getColWidth($colNum)
	{
		assert(isset($this->colWidths[$colNum]));
		return $this->colWidths[$colNum];
	}
	
	function addTableRow(&$newRow)
	{
		assert( is_a($newRow, 'GraphicTableRow') );
		
		if($this->repeatable)
			$newRow->makeRepeatable();
		
		$this->rows[] = &$newRow;
	}
	
	function &getNewTableRow()
	{
		$object = &new GraphicTableRow($this->context);
		$object->setParent($this);
		$this->addTableRow($object);
		
		return $object;
	}
	
	function &getHeader()
	{
		$this->header = &new GraphicTableRow($this->context);
		$this->header->setParent($this);
		$this->header->makeRepeatable();
		return $this->header;		
	}
	
	function calcColWidths()
	{
		$unwrappedWidths = array();
		
		$thisRow = &$this->header;
		foreach(array_keys($thisRow->cells) as $cellKey)
		{
			if(!isset($unwrappedWidths[$cellKey]))
				$unwrappedWidths[$cellKey] = 0;
			
			$thisWidth = $thisRow->cells[$cellKey]->getUnwrappedWidth();
			$thisWidth = $thisWidth < 2 ? 2 : $thisWidth;
			if($thisWidth > $unwrappedWidths[$cellKey])
				$unwrappedWidths[$cellKey] = $thisWidth;
		}		
		
		foreach(array_keys($this->rows) as $rowKey)
		{
			$thisRow = &$this->rows[$rowKey];
			foreach(array_keys($thisRow->cells) as $cellKey)
			{
				if(!isset($unwrappedWidths[$cellKey]))
					$unwrappedWidths[$cellKey] = 0;
				
				$thisWidth = $thisRow->cells[$cellKey]->getUnwrappedWidth();
				$thisWidth = $thisWidth < 2 ? 2 : $thisWidth;
				if($thisWidth > $unwrappedWidths[$cellKey])
					$unwrappedWidths[$cellKey] = $thisWidth;
			}
		}
		
		$totalWidth = 0;
		foreach($unwrappedWidths as $thisWidth)
		{
			$totalWidth += $thisWidth;
		}
		
		foreach($unwrappedWidths as $colNum => $thisWidth)
		{
			$this->setColWidth($colNum, ($thisWidth / $totalWidth) * $this->getWidth());
		}
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
		if($this->autospaceType == 'line-width')
			$this->calcColWidths();
		
		$cw = $this->getContentWidth();
		$contentWidth = $width < $cw ? $width : $cw;
		
		$curx = $x;
		$cury = $y;
		
		if($this->header)
			$cury += $this->header->draw($curx, $cury, $contentWidth, $reallyDraw) + $this->getRowSpacing();
		
		for($rowKey = $this->startRow; $rowKey < count($this->rows); $rowKey++)
		{
			//	get a reference to the row object
			$thisRow = &$this->rows[$rowKey];
			
			//	check to see if drawing this row is going to put us past the end of the page
			//	if $this is not breakable then we already know that the whole thing should fit
			//	so we don't need to do this check
			if($this->isBreakable() && $reallyDraw)
			{
				$minHeight = $thisRow->getMinHeight($contentWidth);
				$document = $this->getAncestor('GraphicDocument');
				if( $cury + $minHeight  > $document->getPageBottom() )
					break;
			}
			
			//	actually draw the row
			$height = $thisRow->draw($curx, $cury, $contentWidth, $reallyDraw);
			
			$cury += $height + $this->getRowSpacing();
			
			//	if we drew part but not all then we need to leave the loop now to make sure we don't move past it
			if($this->isBreakable() && $reallyDraw && !$thisRow->doneDrawing())
				break;
		}
		
		//	update the pointer to the current row
		if($reallyDraw && !$this->repeatable)
			$this->startRow = $rowKey;
		
		$cury -= $this->getRowSpacing();
		
//		$this->calcColWidths();
		
		return $cury - $y;
	}
}
?>