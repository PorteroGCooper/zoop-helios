<?php
/**
* @package pdf
*/
/**
* @package pdf
*/
class PdfTableRow extends PdfContainer
{
	var $cellPadding;
	var $colWidths = array();
	var $colContents = array();
	var $autoSpacing = false;
	var $colCount = 0;
	var $border = 0;
	var $debug = 0;
	
	function pdfTableRow(&$pdf, $contents = array(), $colCount = 0, $colWidths = array(), $cellPadding = 1, $width = kPdf_default_page_width, $height = -1)
	{
		//echo("pdftablerow constructor" . "<br>");
		$this->PdfContainer($pdf, $contents, $width, $height);
		$this->cellPadding = $cellPadding;
		$this->colCount = $colCount;
		$colNum = 0;
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			$toColNum = $colNum + $val->getColSpan();
			for($colNum; $colNum < $toColNum; $colNum++)
			{
				$this->colContents[$colNum] = $key;
			}
		}
		if($colCount == 0)
		{
			$this->colCount = count($this->contents);
			$this->setColumnWidths($colWidths);
		}
		else
		{
			$this->colCount = $colCount;
			$this->setColumnWidths($colWidths);
		}
		if($this->height != -1)
		{
			$this->setHeight($this->height);
		}
	}
	
	function &getNewCell($columnId = -1)
	{
		$cell = &new PdfTableCell($this->pdf, array(), 1);
		$this->addElement($cell, $columnId);
		//if($columnId != -1)
		{
			//return $this->contents[$columnId];
		}
		//else
		{
			return $this->contents[count($this->contents) - 1];
		}
	}
	
	function addElement($pdfObject, $columnId = -1)
	{
		$this->contents[] = $pdfObject;
		$elementId = count($this->contents) - 1;
		if($columnId == -1)
		{
			$this->colCount += $pdfObject->getColSpan();
			for($i = 0; $i < $pdfObject->getColSpan(); $i++)
			{
				$this->colWidths[] = $pdfObject->getWidth() / $pdfObject->getColSpan();
				$this->colContents[] = $elementId;
			}
		}
		else
		{
			if(isset($this->colWidths[$columnId]))
			{
				$width = 0;
				for($i = $columnId; $i - $columnId < $pdfObject->getColSpan(); $i++)
				{
					$width += $this->colWidths[$i];
					$this->colContents[$i] = $elementId;
				}
				$this->contents[$elementId]->setWidth($width);
			}
			else
			{
				$width = $pdfObject->getWidth() / $pdfObject->getColSpan();
				for($i = $columnId; $i - $columnId < $pdfObject->getColSpan(); $i++)
				{
					$this->colWidths[$i] = $width;
					$this->colContents[$i] = $elementId;
				}
			}
		}
		if($this->autoSpacing)
		{
			$this->setColumnWidths();
		}
		else
			$this->setColumnWidths($this->colWidths);
	}
	
	function removeElement($id)
	{
		$colNum = 0;
		while($this->colContents[$i] != $id)
		{
			$colNum++;
		}
		
		$length = $this->contents[$id]->getColSpan();
		
		$this->colCount -= $lenghth;
		
		array_splice($this->colWidths,$i,$length);
		array_splice($this->colContents,$i,$length);
		array_splice($this->contents,$id,1);//removes $contents[$id] and shifts array
		
		if($this->autoSpacing)
		{
			$this->setColumnWidths();
		}
		else
		{
			$this->setColumnWidths($this->colWidths);
		}

	}

	function setAutoSpacing($bool)
	{
		$this->autoSpacing = $bool;
	}
	
	function setColumnCount($colCount)
	{
		if($colCount == 0)
			$this->colCount = count($this->elements);
		else
			$this->colCount = $colCount;
		if($this->autoSpacint)
		{
			$this->setColumnWidths();
		}
		else
		{
			$this->setColumnWidths($this->colWidths);
		}
	}
	
	function setColumnWidths($widths = array())
	{
		$curWidth = 0;
		if(count($widths) == 0)
			$this->setAutoSpacing(true);
		else
			$this->setAutoSpacing(false);
		for($i = 0; $i < count($widths); $i++)
		{
			$this->setColumnWidth($i, $widths[$i]);
			$curWidth += $widths[$i];
		}
		$remainingWidth = $this->getWidth() - $curWidth;
		$remainingRows = $this->colCount;
//		print_r($widths);
//		echo("<br>");
//		echo(count($widths));
		$remainingRows -= count($widths);
		if($remainingRows > 0)
		{
		
			$autoWidth = $remainingWidth / $remainingRows;
			for($i = count($widths); $i < $this->colCount; $i++)
			{
				$this->setColumnWidth($i, $autoWidth);
			}
		}
	}
	
	function calcElementWidth($elementId)
	{
		$i = 0;
		while((!isset($this->colContents[$i]) ||  $this->colContents[$i] != $elementId) && $i < $this->colCount)
		{
			$i++;
		}
		$startCol = $i;
		$endCol = $startCol + $this->contents[$elementId]->getColSpan();
		$width = 0;
		for($i = $startCol; $i < $endCol; $i++)
		{
			$width += $this->colWidths[$i];
		}
//		echo("Element $elementId of TableRow before:");
//		print_r($this->contents[$elementId]->getWidth());
//		echo("<br>");
		$this->setElementWidth($elementId, $width - $this->cellPadding * 2);
//		echo("Element $elementId of TableRow after:");
//		print_r($this->contents[$elementId]->getWidth());
//		echo("<br>");
	}
	
	function setColumnWidth($colNum, $width)
	{
		$this->colWidths[$colNum] = $width;
		if(isset($this->colContents[$colNum]))
		{
			$this->calcElementWidth($this->colContents[$colNum]);
		}
	}
	
	function setElementWidth($id, $width)
	{
		//echo(get_class($this->contents[$id]));
		//echo($id . ", " . $width . "<br>");
//		echo("Element $id of TableRow before:");
//		print_r($this->contents[$id]->getWidth());
//		echo(get_class($this->contents[$id]));
//		echo("<br>");
		$this->contents[$id]->setWidth($width);
//		echo("Element $id of TableRow after:");
//		print_r($this->contents[$id]->getWidth());
//		echo(get_class($this->contents[$id]));
//		echo("<br>");
	}
	
	function getElementWidth($id)
	{
		return $this->contents[$id]->getWidth();
	}
	
	function setWidth($width)
	{
		$this->width = $width;
		if($this->autoSpacing)
		{
			$this->setColumnWidths();
		}
		else
			$this->setColumnWidths($this->colWidths);
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function setHeight($height)
	{
		$this->height = $height;
		//echo("height:".$this->height . "<br>");
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			if($this->height != -1)
				$this->contents[$key]->setHeight($this->height - $this->cellPadding * 2);
			else
				$this->contents[$key]->setHeight($this->height);
		}

	}
	
	function getHeight()
	{
		if($this->height == -1)
			return $this->getContentHeight() + $this->cellPadding * 2;
		else
			return $this->height;
	}
	
	function setCellPadding($cellPadding)
	{
		if($this->cellPadding != $cellPadding)
		{
			$this->cellPadding = $cellPadding;
			if($this->autoSpacing)
			{
				//echo("setting row colWidths<br>");
				$this->setColumnWidths();
			}
			else
			{
				//echo("setting row colWidths<br>");
				$this->setColumnWidths($this->colWidths);
			}
			//echo("setting row cellPadding<br>");
		}
	}
	
	function getContentWidth()
	{
		if(isset($this->contentWidth) && $this->contentWidth != -1)
		{
			return $this->contentWidth;
		}
		$width = 0;
		$currElement = -1;
		reset($this->colContents);
		while(list($key, $val) = each($this->colContents))
		{
			if($currElement != $val)
			{
				if(isset($this->contents[$val]))
				{
					$elementWidth = $this->contents[$val]->getWidth();
					$currElement = $val;
				}
				else
					$elementWidth = $this->colWidths[$key];
				$width += $elementWidth;
			}
		}
		$this->contentWidth = $width;
		return $this->contentWidth;
	}
	
	function getContentHeight()//are we horizontal?  vertical?
	{
		$tallest = 0;
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			$elementHeight = $this->contents[$key]->getContentHeight();
			
			//echo($tallest." tallest before $key<br>");
			if($elementHeight > $tallest)
			{
				$tallest = $elementHeight;
			}
			//echo($tallest." tallest after  $key<br>");
		}
		
		$this->contentHeight = $tallest;
		return $this->contentHeight;
	}
	
	
	
	function draw($x, $y)
	{
		$this->setWidth($this->width);
		if($this->height == -1)
			$this->setHeight($this->getContentHeight() + 2 * $this->cellPadding);
		else
			$this->setHeight($this->height);
		
		$this->drawBorder($x, $y);
		$colx = $x;
		
//		echo("drawing TableRow at $x, $y<br> ");
//		echo(count($this->contents) . "<br>");
//		print_r($colx + $this->contents[$currElement]->getWidth() - $x);
//		echo("<br>");
//		print_r($this->width);
//		echo("<br>");
		if(!($this->bgColor[0] == 1 && $this->bgColor[1] == 1 && $this->bgColor[2] == 1))
		{
			$this->pdf->setColor($this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
//			echo($this->getHeight()."<br>");
			$this->pdf->filledRectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
			
		}
		$currElement = -1;
		for(
			$currCol = 0; 
				$currCol < $this->colCount;
			$currCol++)
		{
			//echo("drawing column $currCol<br>");
			if(isset($this->colContents[$currCol]) && $this->colContents[$currCol] != $currElement)
			{
				$currElement = $this->colContents[$currCol];
				if(isset($this->contents[$currElement]) && $this->contents[$currElement] != NULL)
				{
					$this->contents[$currElement]->draw($colx + $this->cellPadding, $y - $this->cellPadding);
					//print_r($colx."<br>");
					//print_r($this->colWidths);
				}
			}
			$colx += $this->colWidths[$currCol];
		}
		$currElement = -1;
		
		
		$this->pdf->setColor(0, 0, 0);
		
		if($this->border > 0)
		{
			$colx = $x;	// notice this time we don't want the cell padding
			
			for($colNum = 0; $colNum < count($this->colWidths) && $colx <= $this->getWidth(); $colNum++)
			{
				$curCol = $colNum;
				$nextWidth = 0;

				if(isset($this->colContents[$colNum]) && $this->colContents[$colNum] != $currElement)
				{
					$currElement = $this->colContents[$colNum];
					for($i = 0; $i < $this->contents[$currElement]->getColSpan(); $i++)
					{
						
						$nextWidth += $this->colWidths[$colNum + $i];
					}
					$colNum += $this->contents[$currElement]->getColSpan();
					$this->pdf->rectangle(		$colx, 
												$y, 
												$nextWidth,
												-$this->getHeight());
					$colx += $nextWidth;
				}
				else
				{
					$this->pdf->rectangle(	$colx,
											$y,
											$this->colWidths[$colNum],
											-$this->getHeight());
					$colx += $this->colWidths[$colNum];
				}
			}
		}
		
	}
}

?>