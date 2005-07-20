<?

class PdfTable extends PdfContainer
{
	var $cellPadding;
	var $colWidths = array();
	var $autoSpacing = false;
	var $border = false;
	var $colCount = 0;
	var $lastRow = 0;
	var $failOnRepeat = true;
	
	function pdfTable(&$pdf, $contents = array(), $colCount = 0, $colWidths = array(), $cellPadding = 0, $width = kPdf_default_page_width, $height = -1)
	{
//		echo("pdftable constructor"  . "<br>");
		$this->PdfContainer($pdf, $contents, $width, $height);
		$this->cellPadding = $cellPadding;
		if(count($colWidths) == 0)
		{
			$this->autoSpacing = true;
		}
		$this->setWidth($width);
//		echo("before: setColumnCount/widths");
//		print_r($this->colWidths);
//		echo("<br>");
		$this->setColumnCount( $colCount );
		$this->setColumnWidths( $colWidths );
//		echo("after:");
//		print_r($this->colWidths);
//		echo("<br>");
		$this->setCellPadding($cellPadding);
		//print_r($this->colWidths);
	}
	
	function &getNewRow()
	{
		$row = &new PdfTableRow($this->pdf, array(), $this->colCount);
		$this->addElement($row);
		return $this->contents[count($this->contents) - 1];
	}
	
	function addElement($pdfObject)
	{
		//echo("adding row to this table<br>");
		$this->contents[] = $pdfObject;
		//echo("setting border<br>");
		$this->contents[count($this->contents)-1]->setBorder($this->border);
		//echo("setting cellPadding<br>");
		$this->contents[count($this->contents)-1]->setCellPadding($this->cellPadding);
		//echo("setting width<br>");
		$this->contents[count($this->contents)-1]->setWidth($this->width);
		if($this->autoSpacing)
			$this->contents[count($this->contents)-1]->setColumnWidths();
		else
			$this->contents[count($this->contents)-1]->setColumnWidths($this->colWidths);
		//echo("added row to this table<br>");
		
	}
	
	function removeElement($id)
	{
		return array_splice($this->contents,$id,1);//removes $contents[$id] and shifts array
	}
	
	function getRowCount()
	{
		return count($this->contents);
	}
	
	function setCellPadding($cellPadding)
	{
		if($this->cellPadding != $cellPadding)
		{
			$this->cellPadding = $cellPadding;
			while(list($key, $val) = each($this->contents))
			{
				$this->contents[$key]->setCellPadding($cellPadding);
			}
		}
	}
	
	function setColumnCount($colCount)
	{
		$this->colCount = $colCount;
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			$this->contents[$key]->setColumnCount($this->colCount);
		}
	}
	
	function setColumnWidths($colWidths = array())
	{
		if(count($colWidths) == 0)
		{
			$this->autoSpacing = true;
		}
		else
		{
			$this->autoSpacing = false;
		}
		$this->colWidths = $colWidths;
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			$this->contents[$key]->setColumnWidths($this->colWidths);
		}
	}
	
	function setColumnWidth($colNum, $width)
	{
		$this->colWidths[$colNum] = $width;
//		echo("here12");
		while(list($key, $val) = each($this->contents))
		{
			$this->contents[$key]->setColumnWidth($colNum,$width);
		}
		//print_r($this->colWidths);
	}
	
	function setWidth($width)
	{
		$this->width = $width;
		
		foreach($this->contents as $key => $value)
		{
			$this->contents[$key]->setWidth($this->width);
		}
		if($this->autoSpacing == true)
		{
//			echo("here13");
			$this->setColumnWidths();
		}
		else
		{
			$this->setColumnWidths($this->colWidths);
		}
		//print_r($this->colWidths);
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function setHeight($height)
	{
		$this->height = $height;
	}
	
	function getHeight()
	{
		if($this->height == -1)
			return $this->getContentHeight();
		else
			return $this->height;
	}
	
	function setBorder($inBorder)
	{
		$this->border = $inBorder;
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			$this->contents[$key]->setBorder($this->border);
		}
	}
	
	function getContentWidth()
	{
		if(isset($this->contentWidth) && $this->contentWidth != -1)
			return $this->contentWidth;

		$widest = 0;
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			if($this->contents[$key]->getContentWidth() > $widest)
			{
				$widest = $this->contents[$key]->contentWidth();
			}
		}
		return $this->contentWidth;
	}
	
	function getContentHeight()//are we horizontal?  vertical?
	{
		$height = 0;
		$this->setHeight($this->height);
		$this->setWidth($this->width);
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			
			$elementHeight = $this->contents[$key]->getHeight();
			//echo($elementHeight . " row $key height<br>");
			$height += $elementHeight;
		}
		//echo($height . " table height<br>");
		$this->contentHeight = $height;
		return $this->contentHeight;
	}
	
	function draw($x, $y, $align = -1, $rowNum = 0)
	{
		$rowy = $y;
		$this->setWidth($this->width);
		if(!($this->bgColor[0] == 1 && $this->bgColor[0] == 1 && $this->bgColor[0] == 1))
		{
			$this->pdf->setColor($this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
			$this->pdf->filledRectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
			
		}
		$this->pdf->setColor(0, 0, 0);
		//echo($y . "<br>");
		//print_r($rowy);
		//echo("<br>");
		
		//echo("drawing Table at $x, $y<br> ");
		//echo("$rowNum < ".count($this->contents));
		//echo("$this->height == -1 || ($y - $rowy) + $this->contents[$rowNum]->getHeight() <= $this->height <br>");
		for($rowNum; 
				$rowNum < count($this->contents) && 
				($this->height == -1 || ($y - $rowy) + $this->contents[$rowNum]->getHeight() <= $this->height ); 
			$rowNum++)
		{
			//echo("drawing row $rowNum<br>");
			if(isset($this->contents[$rowNum]) && $this->contents[$rowNum] != NULL)
			{
				//echo("$rowNum wasn't empty<br>");
				$this->contents[$rowNum]->draw($x, $rowy);
				$rowy -= $this->contents[$rowNum]->getHeight();
			}
		}
		if($rowNum < count($this->contents))
		{
			//echo("calling getHeight<br>");
			//echo($this->contents[$rowNum]->getHeight() . "<br>");
			//echo(($y - $rowy) + $this->contents[$rowNum]->getHeight(). "<br>");
			//echo($this->height . "<br>");
		}
		$this->lastY = $rowy;
		return $rowNum;
	}
	
	function getLastY()
	{
		return $this->lastY;
	}
	
	function drawOneBlock($x, $y, $align = -1)
	{
		
		if(count($this->contents) == 0)
		{
			$this->lastY = $y;
			return false;
		}
		$newLastRow = $this->draw($x, $y, $align, $this->lastRow);
		if($newLastRow != $this->lastRow || !isset($this->contents[$newLastRow]) || $this->failOnRepeat == false)
		{
			$this->lastRow = $newLastRow;
		}
		else
		{
			//whoa nelly, we need to draw this one block at a time....
			//$maxHeight = $this->getHeight() - ($this->getHeight() - $this->lastY);
			//$this->contents[$this->lastRow]->setHeight($maxHeight);
			//$this->contents[$this->lastRow]->drawOneBlock($x,$this->lastY);
			//$this->lastY = $this->contents[$this->lastRow]->getLastY();
			trigger_error("unable to draw this block in the size given to the table:" . $this->getHeight(). " is smaller than " . $this->contents[$newLastRow]->getHeight());
			//html_print_r($this->contents[$newLastRow]);
			return false;
		}
		
		if($this->lastRow < count($this->contents))
			return true;
		else
			return false;
	}
}

?>