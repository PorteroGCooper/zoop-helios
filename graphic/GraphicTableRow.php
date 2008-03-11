<?
class GraphicTableRow extends GraphicObject
{
	var $cells;
	var $breakable;
	
	function GraphicTableRow(&$context)
	{
		$this->GraphicObject($context);
		$this->cells = array();
		$this->breakable = 1;
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
//		echo "checking min height<br>";
//		echo_backtrace();
		if($this->isBreakable())
		{
			return 20;
			//	the min height is the row is the greatest of the minHeights of the cells
			$maxMin = 0;
			foreach(array_keys($this->cells) as $cellKey)
			{
//				 echo "cellKey = $cellKey<br>";
				 $cellMin = $this->cells[$cellKey]->getMinHeight();
//				 echo "cellMin = $cellMin<br>";
				 if($cellMin > $maxMin)
				 	$maxMin = $cellMin;
			}
			
			return $maxMin;
		}
		else
		{
			$height = $this->getHeight($width);
//			echo "total height = $height<br>";
			return $height;
		}
	}
	
	function doneDrawing()
	{
		//	a table row is done drawing if all of it's cells are done drawing
		foreach(array_keys($this->cells) as $cellKey)
		{
			//	if it's repeatable then it doesn't count
			if($this->cells[$cellKey]->isRepeatable())
				continue;
			
			if(!$this->cells[$cellKey]->doneDrawing())
				return 0;
		}
		
		return 1;
	}
	
	function getColWidth($colNum)
	{
		$answer = 0;
		for($i = $colNum; $i < $colNum + $this->cells[$colNum]->getColSpan(); $i++)
		{
			if( $this->parent->hasColWidth($i) )
				$answer += $this->parent->getColWidth($i);
			else
			{
				$blankCols = 0;
				$accountedFor = 0;
				for($curCol = 0; $curCol < count($this->cells); $curCol++)
				{
					if( $this->parent->hasColWidth($curCol) )
						$accountedFor += $this->parent->getColWidth($curCol);
					else
						$blankCols += $this->cells[$curCol]->getColSpan();
				}
				//echo "accounted for = $accountedFor : colnum = $colNum : blank cols = $blankCols<br>";
				if($blankCols == 0)
				{
					//die('here');
					$answer += $this->parent->getWidth() - $accountedFor;
				}
				else
					$answer += ($this->parent->getWidth() - $accountedFor) / $blankCols;
			}
		}
		return $answer;
	}
	
	function setColWidth($colNum, $width)
	{
		$this->parent->setColWidth($colNum, $width);
	}
	
	function addTableCell(&$newCell)
	{
		assert( is_a($newCell, 'GraphicTableCell') );
		
		if($this->repeatable)
			$newCell->makeRepeatable();
		
		$this->cells[] = &$newCell;
	}
		
	function &getNewTableCell()
	{
		$object = &new GraphicTableCell($this->context);
		$object->setParent($this);
		$this->addTableCell($object);
		return $object;
	}
	
	/*	
	function calcColWidths()
	{
		$unwrappedWidths = array();
		$thisRow = &$this;
		foreach(array_keys($thisRow->cells) as $cellKey)
		{
			//if(!isset($unwrappedWidths[$cellKey]))
			//	$unwrappedWidths[$cellKey] = 0;
			
			$thisWidth = $thisRow->cells[$cellKey]->getUnwrappedWidth();
			//if($thisWidth > $unwrappedWidths[$cellKey])
			//	$unwrappedWidths[$cellKey] = $thisWidth;
			$unwrappedWidths[$cellKey] = $thisWidth;
		}
		
		echo_backtrace();
		echo_r($unwrappedWidths);
	}
	*/
	
	function draw($x, $y, $width, $reallyDraw)
	{
//		static $count = 0;
//		$this->calcColWidths();
		
		$contentWidth = $width;

		//	first set any col widths defined in this row
		$curCol = 0;
		foreach($this->cells as $cellKey => $dummyCell)
		{
			if( $this->cells[$cellKey]->hasWidth() )
			{
				$this->setColWidth($curCol, $this->cells[$cellKey]->getWidth());
			}
			
			$curCol++;
		}
		
		//	figure out the height of the row
		$tallest = 0;
		$curCol = 0;
		$heights = array();
		foreach(array_keys($this->cells) as $cellKey)
		{
			$colWidth = $this->getColWidth($curCol);
			
			$thisCell = &$this->cells[$cellKey];
			
			$height = $thisCell->getHeight($colWidth);
			
			if($height > $tallest)
				$tallest = $height;
			
			$heights[$curCol] = $height;
			$curCol++;
		}
		
//		$this->calcColWidths();
//		if($count > 0)
//			die();
//		$count ++;
		
		//	draw the actual cells
		$curx = $x;
		$curCol = 0;
		foreach(array_keys($this->cells) as $cellKey)
		{
			$colWidth = $this->getColWidth($curCol);
			
			$thisCell = &$this->cells[$cellKey];
			/*
			//	I'm pretty sure that this colspan stuff doesn't actually work yet
			$colSpan = $thisCell->getColSpan();
			if($colSpan > 1)
			{
				//$colWidth = 0;
				
				for($i = 1; $i < $colSpan; $i++)
				{
					$colWidth += $this->getColWidth($curCol + $i);
				}
			}
			//*/
			
			//$thisy = $y + (($tallest - $heights[$curCol]) / 2);
			$thisy = $y;
			$thisCell->setTempHeight($tallest);
			$thisCell->draw($curx, $thisy, $colWidth, $reallyDraw);
			$thisCell->clearTempHeight();
			
			$curx += $colWidth;
			
			//	draw the right border for this cell
			if($this->parent->getBorder() && $reallyDraw)
					$this->context->addLine($curx, $y, $curx, $y + $tallest);
			
			$curCol++;
		}
		
		//	draw the border around the row
		if($this->parent->getBorder() && $reallyDraw)
			$this->context->addRect($x, $y, $width, $tallest);
		
		return $tallest;
	}
}
?>
