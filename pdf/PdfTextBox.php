<?
/**
* @package pdf
*/
/**
* @package pdf
*/
class PdfTextBox extends PdfObject
{
	// most of these functions are just placeholders, waiting to be overriden in the classes that inherit from this object
	// this is mostly a template/interface so we know what to implement....
	var $textSize;
	var $textAngle = 0;
	var $textColor = array(0,0,0);
	var $contentWidth = -1;
	var $align = -1;
	var $textFont = "Helvetica";
	var $bold = false;
	var $italics = false;
	var $underline = false;
	
	function pdfTextBox(&$pdf, $contents = "", $textSize = kPdf_default_text_size, $width = kPdf_default_page_width, $height = -1)
	{
		$convmap = array(0xFF, 0x2FFFF, 0, 0xFFFF);
		$char = mb_decode_numericentity("&#8211;", $convmap, 'UTF-8');
   		$contents = mb_ereg_replace($char, '-', $contents);
		$contents = mb_convert_encoding($contents, "ISO-8859-1", "UTF-8");
		$this->pdfObject($pdf, $contents, $width, $height);
		$this->textSize = $textSize;
		$this->textFont = "Helvetica";
	}
	
	function setTextSize($size = kPdf_default_text_size)
	{
		$this->textSize = $size;
	}
	
	function setTextColor($color = array(0,0,0))
	{
		$this->textColor = $color;
	}
	
	function setTextFont($font = "Helvetica")
	{
		$this->textFont = $font;
	}
	
	function setBold($bold)
	{
		$this->bold = $bold;
	}
	
	function setItalics($italics)
	{
		$this->italics = $italics;
	}	
	
	function calculateFontFile()
	{
		if((!isset($this->bold) && !isset($this->italics)) || (!$this->bold && !$this->italics))
		{
			return dirname(__file__) . "/fonts/" . $this->textFont . ".afm";
		}
		else if(isset($this->bold) && $this->bold)
		{
			if(isset($this->italics) && $this->italics)
			{
				if(file_exists(dirname(__file__) . "/fonts/" . $this->textFont . "-BoldOblique.afm"))
					return dirname(__file__) . "/fonts/" . $this->textFont . "-BoldOblique.afm";
				else
					return dirname(__file__) . "/fonts/" . $this->textFont . "-BoldItalic.afm";
			}
			else
			{
				return dirname(__file__) . "/fonts/" . $this->textFont . "-Bold.afm";
			}
		}
		else
		{
			if(file_exists(dirname(__file__) . "/fonts/" . $this->textFont . "-Oblique.afm"))
				return dirname(__file__) . "/fonts/" . $this->textFont . "-Oblique.afm";
			else
				return dirname(__file__) . "/fonts/" . $this->textFont . "-Italic.afm";
		}
	}		 
	
	function getContentWidth()
	{
		if($this->contentWidth != -1)
		{
			return $this->contentWidth;
		}
		else
		{
			$this->getLines();
			$this->contentWidth = $this->lines["longest"];
		}
		return $this->contentWidth;
	}
	
	function setAlign($align)
	{
		$this->align = $align;
	}
	
	function getContentHeight()
	{
		$this->getLines();
		if(isset($this->lines["text"]))
		{
			$this->contentHeight = ($this->textSize * count($this->lines["text"])) + 1;
			//echo($this->contentHeight . " textbox height<br>");
			return $this->contentHeight;
		}
		else
		{
			return 0;
		}
	}
	
	function getLines()
	{
		//	break the text down into an array of paragraphs
		//echo 'font file = ' . $this->calculateFontFile() . ' ' . $this->textFont . '<br>';
		if(isset($this->lines) && $this->width == $this->lines["width"]  && $this->textSize == $this->lines["textSize"])
		{
			return $this->lines;
		}
		else 
		{
			$rawPars = explode("\r\n", $this->contents);
			
			//	go through each paragraph and find the line breaks
			//		when we're done we should have an array processed
			//		paragraphs.  Each paragraph should be an array of 
			//		lines.
			//echo "the font it is selecting " . $this->calculateFontFile() . '<br>';
			$this->pdf->selectFont($this->calculateFontFile());
			$lines = array();
			$lines2 = array();
			$lines["longest"] = 0;
			$lines["width"] = $this->width;
			$lines["textSize"] = $this->textSize;
			if(strlen($this->contents) == 1)
			{
				$lines["longest"] = $this->pdf->getTextWidth($this->textSize, $this->contents);
				$lines["length"][] = $lines["longest"];
				$lines["text"][] = $this->contents;
			}
			else
			{
				while( list($parNum, $thisPar) = each($rawPars) )
				{
					//	init your data

					$len = strlen($thisPar);
					$last = $len - 1;
					$lastEnd = -1;
					$curStart = 0;
					$curLen = 0;

					if( strlen($thisPar) == 0 )
					{
						$lines["text"][] = "";
						$lines["length"][] = 0;
					}

					for($i = 1; $i < $len; $i++)
					{
						//	if we are at a breaking point.
						//		a breaking point is either
						//			a real char before a space or
						//			the last char in the string

						if( (($thisPar[$i] == " ") && ($thisPar[$i - 1] != " ")) || ($i == $last) )
						{
							$word = substr($thisPar, $lastEnd + 1, ($i) - $lastEnd);

							$wordLen = ($this->pdf->getTextWidth($this->textSize, $word));

							//	did we find a line break yet

							if($i == $last)
							{
								if( $curLen + $wordLen > $this->width )
								{
									$line = substr($thisPar, $curStart, $lastEnd - $curStart + 1);

									$lines["text"][] = $line;
									$lines["length"][] = $curLen;
									if($curLen > $lines["longest"])
									{
										$lines["longest"] = $curLen;
									}

									$line = substr($thisPar, $lastEnd + 1, $i - $lastEnd);
									$word = trim($word);
									$this->pdf->getTextWidth($this->textSize, $word);

									$lines["text"][] = $line;
									$lines["length"][] = $wordLen;
									if($wordLen > $lines["longest"])
									{
										$lines["longest"] = $wordLen;
									}
								}
								else
								{
									$line = substr($thisPar, $curStart, $i - $curStart + 1);
									$lines["text"][] = $line;
									$lines["length"][] = $curLen + $wordLen;
									if($curLen + $wordLen > $lines["longest"])
										$lines["longest"] = $curLen + $wordLen;
								}
							}
							else if( $curLen + $wordLen > $this->width )
							{
								$line = substr($thisPar, $curStart, $lastEnd - $curStart + 1);
								$lines["text"][] = $line;
								$lines["length"][] = $curLen;
								if($curLen > $lines["longest"])
									$lines["longest"] = $curLen;

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
			}
			$this->lines = $lines;
			//$this->pdf->selectFont(zoop_dir . "/pdf/fonts/Helvetica-Bold.afm");
			//echo_r($lines);
			//echo 'font = ' . $this->textFont . ' bold = ' . ($this->bold ? 1 : 0) . ' italics = ' . ($this->italics ? 1 : 0) . ' size = ' . $this->textSize . ' ' . $this->bold . ' ' . $this->bold . '<br>';
			//echo 'true length = ' . $this->pdf->getTextWidth($this->textSize, 'asdfkla sdfklasdfjlkas;dfgj aslkfj alkw[sfjeoifjsa fi;awesfil;ifas efjilse;filaws eji iiiiiiiiiiiiiii') . '<br>';
			return $this->lines;
		}
	}
	
	function draw($x, $y, $align = -1)
	{
		$lines = $this->getLines();
//		echo($align);
		$this->pdf->selectFont($this->calculateFontFile());
		if($align == -1)
		{
			if($this->align == -1)
			{
				$align = PdfTableCell_align_left;
			}
			else
			{
				$align = $this->align;
			}
		}
//		print_r($lines);
		$i = 0;
		if(!($this->bgColor[0] == 1 && $this->bgColor[0] == 1 && $this->bgColor[0] == 1))
		{
			$this->pdf->setColor($this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
			$this->pdf->filledRectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
			$this->pdf->setColor(0, 0, 0);
		}
		$y -= $this->textSize - 1;
		$cury = $y;
		
//		echo("drawing TextBox at $x, $y<br> ");
		$this->pdf->setColor($this->textColor[0], $this->textColor[1], $this->textColor[2]);
		while(($this->height == - 1 || $y - $cury + $this->textSize < $this->height) && isset($this->lines["text"][$i]))
		{
			if($align == PdfTableCell_align_center)
			{
				$curx = $x + ($this->width / 2) - ($this->lines["length"][$i] / 2);
			}
			else if($align == PdfTableCell_align_left)
			{
				$curx = $x;
			}
			else if($align == PdfTableCell_align_right)
			{
				$curx = $x + $this->width - $this->lines["length"][$i];
			}
			
			$this->pdf->addText($curx, $cury, $this->textSize, $this->lines["text"][$i], $this->textAngle);
			$cury -= $this->textSize;
			$i++;
		}
		$this->pdf->setColor(0,0,0);
	}
}

?>