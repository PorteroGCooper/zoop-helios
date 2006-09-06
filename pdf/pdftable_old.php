<?php
/**
* @package pdf
*/
	define("kPdfTabe_default_cell_padding", 5);
/**
* @package pdf
*/	
	class pdftable_old
	{
		var $pdfReport;
		var $rows;
		var $cols;
		var $data;
		var $colWidths;
		var $left;
		var $top;
		var $textSize;
		var $cellPadding;
		var $border;
		
		function pdftable_old($inPdfReport, $inCols, $inRows, $inLeft, $inTop)
		{
			$this->pdfReport = &$inPdfReport;
			$this->rows = $inRows;
			$this->cols = $inCols;
			$this->left = $inLeft;
			$this->top = $inTop;
			$this->textSize = kPdf_default_text_size;
			$this->cellPadding = kPdfTabe_default_cell_padding;
			$this->border = 0;
			
			for($colNum = 0; $colNum < $this->cols; $colNum++)
			{
				$this->colWidths[$colNum] = 100;
				
				for($rowNum = 0; $rowNum < $this->rows; $rowNum++)
				{
					$this->data[$colNum][$rowNum] = "";
				}
			}
			
			
		}
		
		
		function setTextSize($inNewSize)
		{
			$this->textSize = $inNewSize;
		}
		
		
		function setCellPadding($inNewPadding)
		{
			$this->cellPadding = $inNewPadding;
		}
		
		function setField($inRowNum, $inColNum, $inValue, $inType = "text", $inHeight = 0, $inWidth = 0)
		{
			if($inValue != NULL)
			{
				$this->data[$inColNum][$inRowNum]["content"] = $inValue;
			}
			else
			{
				$this->data[$inColNum][$inRowNum]["content"] = "";
			}
			$this->data[$inColNum][$inRowNum]["type"] = $inType;
			$this->data[$inColNum][$inRowNum]["height"] = $inHeight;
			$this->data[$inColNum][$inRowNum]["width"] = $inWidth;
		}
		
		function setColWidth($inColNum, $inWidth)
		{
			$this->colWidths[$inColNum] = $inWidth;
		}
		
		function setBorder($inBorder)
		{
			$this->border = $inBorder;
		}
		
		function draw()
		{
			$rowy = $this->top;
			//print_r($this->data);
			for($rowNum = 0; $rowNum < $this->rows; $rowNum++)
			{
				$colx = $this->left + $this->cellPadding;
				
				$tallest = 2 * $this->cellPadding;					// we always have to move down at least this much
				//print("\n" . $rowNum . "\n");
				for($colNum = 0; $colNum < $this->cols; $colNum++)
				{
					
					if(isset($this->data[$colNum]) && $this->data[$colNum][$rowNum] != NULL)
					{
						$availableWidth = $this->colWidths[$colNum] - (2 * $this->cellPadding);
						if($this->data[$colNum][$rowNum]["type"] == "text")
						{
							$height = $this->pdfReport->textBox($this->data[$colNum][$rowNum]["content"], 
													$colx, 
													$rowy, 
													$availableWidth, 
													$this->textSize);
						}
						else if($this->data[$colNum][$rowNum]["type"] == "image")
						{
							$height = $this->data[$colNum][$rowNum]["height"];
							if(strstr($this->data[$colNum][$rowNum]["content"], 'bullet'))
							{
								$this->pdfReport->pdf->filledEllipse($colx + 2, 
																$rowy - $this->data[$colNum][$rowNum]["height"] + 1,
																2															
																);
							}
							else
							{							
								$this->pdfReport->addJpegFromFile($this->data[$colNum][$rowNum]["content"], //filename
																$colx ,
																$rowy - $this->data[$colNum][$rowNum]["height"] - $this->cellPadding + ($this->cellPadding - 3), 
																$this->data[$colNum][$rowNum]["width"], 
																$this->data[$colNum][$rowNum]["height"]);
							}
						}
						if( ($height + (2 * $this->cellPadding)) > $tallest)
						{
							$tallest = $height + (2 * $this->cellPadding);
						}
						
						$colx += $this->colWidths[$colNum];
					}
				}
				
				//	now that we know the height go back and draw the border on all the cells
				
				if($this->border > 0)
				{
					$colx = $this->left;	// notice this time we don't want the cell padding
					
					for($colNum = 0; $colNum < $this->cols; $colNum++)
					{
						$this->pdfReport->pdf->rectangle($colx - $this->cellPadding, 
															$rowy, 
															$this->colWidths[$colNum],
															-$tallest);
						
						$colx += $this->colWidths[$colNum];
					}
				}
				
				$rowy -= $tallest;
			}
			
		}
	}
?>