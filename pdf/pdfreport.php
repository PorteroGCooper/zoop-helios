<?
/**
* @package pdf
*/
	define("kPdf_default_page_width", 8.5 * 72);
	define("kPdf_default_page_height", 11 * 72);
	define("kPdf_default_text_size", 12);
/**
* @package pdf
*/	
	class pdfreport
	{
		var $curPage;
		var $pdf;
		var $defaultTextSize;
		
		function pdfreport($inBounds, &$inPdf)
		{
			$this->curPage = 1;
			
			if( $inBounds == NULL)
				$bounds = array(0, 0, kPdf_default_page_width, kPdf_default_page_height);
			else
				$bounds = $inBounds;
			if($inPdf != NULL)
			{
				$this->pdf = &$inPdf;
				//$this->pdf->setEncryption('sweat1', 'sweat1');
				$this->stream = false;
			}
			else
			{
				$this->pdf = new Cpdf($bounds);
				//$this->pdf->setEncryption('sweat1', 'sweat1');
				$this->stream = true;
				//echo "Streaming is on";
				//echo_r(debug_backtrace());
			}
		}
		
		function addText($inLeft, $inTop, $inText, $inSize = kPdf_default_text_size, $inAngle = 0)
		{
			$this->pdf->addText($inLeft, $inTop, $inSize, $inText, $inAngle);
		}
		
		function addJpegFromFile($filename, $inLeft, $inTop, $width, $height)
		{
			$this->pdf->addJpegFromFile($filename, $inLeft, $inTop, $width, $height);
		}
		
		
		function setColor($inRgbArray)
		{
			$this->pdf->setColor($inRgbArray[0], $inRgbArray[1], $inRgbArray[2]);
		}
		
		
		function setStrokeColor($inRgbArray)
		{
			$this->pdf->setStrokeColor($inRgbArray[0], $inRgbArray[1], $inRgbArray[2]);
		}
		
		function drawHeader()
		{
		}
		
		function drawFooter()
		{
		}
		
		function drawContentFrame()
		{
		}
		
		function drawPageNumber()
		{
		}
		
		function drawPageContent()
		{
			return false;
		}
		
		function drawPage()
		{
			$this->drawHeader();
			$this->drawFooter();
			$this->drawContentFrame();
			$this->drawPageNumber();
			$moreToDraw = $this->drawPageContent();
			
			return $moreToDraw;
		}
		
		function draw()
		{
			
			while( $this->drawPage() )
			{
				$this->curPage++;
				$this->pdf->newPage();
			}
			
			if($this->stream)
			{
				//echo("I want to see the pdf stuff...");
				$this->pdf->stream();
				//echo_r(debug_backtrace());
				//echo "I'm streaming!! I'm streaming!!";
			}
		}
		
		
		function textBox($inText, $inLeft, $inTop, $inWidth, $inTextSize = kPdf_default_text_size)
		{
			$lines = $this->getBoxLines( $inText, $inTextSize, $inWidth );
			
			$cury = $inTop;
			$starty = $cury;
			
			while( list($index, $lineText) = each($lines) )
			{
				$cury -= $inTextSize;
				
				$this->pdf->addText($inLeft, $cury, $inTextSize, $lineText);
			}
			
			return $starty - $cury;
		}
		
		
		//	I didn't want to break any of the old stuff
		
		function getBoxLines($inText, $inTextSize, $inTextWidth)
		{
			//	break the text down into an array of paragraphs
			
			$rawPars = explode("\r\n", $inText);
			
			
			//	go through each paragraph and find the line breaks
			//		when we're done we should have an array processed
			//		paragraphs.  Each paragraph should be an array of 
			//		lines.
			
			$lines = array();
			$lines2 = array();
			
			
			while( list($parNum, $thisPar) = each($rawPars) )
			{
				//	init your data
				
				$len = strlen($thisPar);
				$last = $len - 1;
				
				$lastEnd = -1;
				$curStart = 0;
				$curLen = 0;
				
				if( strlen($thisPar) == 0 )
					$lines[] = "";
				
				for($i = 1; $i < $len; $i++)
				{
					//	if we are at a breaking point.
					//		a breaking point is either
					//			a real char before a space or
					//			the last char in the string
					
					if( (($thisPar[$i] == " ") && ($thisPar[$i - 1] != " ")) || ($i == $last) )
					{
						$word = substr($thisPar, $lastEnd + 1, $i - $lastEnd);
						
						$wordLen = $this->pdf->getTextWidth($inTextSize, $word);
						
						//	did we find a line break yet
						
						if($i == $last)
						{
							if( $curLen + $wordLen > $inTextWidth )
							{
								$line = substr($thisPar, $curStart, $lastEnd - $curStart + 1);
								$lines[] = $line;
								
								$line = substr($thisPar, $lastEnd + 1, $i - $lastEnd);
								$lines[] = $line;
							}
							else
							{
								$line = substr($thisPar, $curStart, $i - $curStart + 1);
								$lines[] = $line;
							}
						}
						else if( $curLen + $wordLen > $inTextWidth )
						{
							$line = substr($thisPar, $curStart, $lastEnd - $curStart + 1);
							$lines[] = $line;
							
							$curLen = $wordLen;
							$curStart = $lastEnd + 1;
						}
						else
						{
							$curLen += $wordLen;
						}
						
						
						$lastEnd = $i;
					}
					
				}
				
				
			}
			
			return $lines;
		}
	}
?>