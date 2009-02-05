<?
class PieChart extends Chart
{
	var $depth;
	
	function PieChart(&$context)
	{
		//	pass onto the base class constructor
		$this->Chart($context);
		$this->depth = 0;
	}
	
	function setDepth($depth)
	{
		$this->depth = $depth;
	}
	
	function addDataEntry($data)
	{
		$color = HexToRgb($data['color']);
		$key = parent::addDataEntry($data);
		$this->context->addColor("color$key", $color[0], $color[1], $color[2]);
	}
	
	function getLegendWidth()
	{
		$longest = 0;
		$this->context->setTextSize(10);
		foreach($this->data as $name => $thisSlice)
		{
			$newLen = $this->context->getStringWidth($thisSlice['text']) + 70;

			if($newLen > $longest)
				$longest = $newLen;
		}
		
		return $longest;
	}
	
	function drawLegend($x, $y, $width, $reallyDraw)
	{
		$total = $this->getValueTotal();
		
		$curx = $x + 5;
		$cury = $y;
		$this->context->setTextSize(10);
		foreach($this->data as $name => $thisSlice)
		{
			if($reallyDraw)
			{
				$this->context->setCurFillColor("color$name");
				$url = isset($thisSlice['url']) ? $thisSlice['url'] : NULL;
				$this->context->addRect($curx, $cury, 10, 10, 'DF', $url);
				
				$percentage = round($thisSlice['value'] * 100 / $total, 1);
				
				$this->context->addText($curx + 20, $cury + 10, "$percentage%");
				$this->context->addText($curx + 70, $cury + 10, $thisSlice['text']);
			}
			$cury += 15;
		}
		
		return $cury - $y;
	}
	
	function getValueTotal()
	{
		$total = 0;
		foreach($this->data as $name => $thisSlice)
		{
			$total += $thisSlice['value'];
		}
		
		return $total;
	}
	
	function drawPlotArea($x, $y, $width, $reallyDraw)
	{
//		echo "$x, $y, $width, $reallyDraw<br>";
		$this->context->setTextSize(8);
		
		$left = $x;
		$top = $y;
		$bottom = $y + $this->plotHeight;
		$right = $x + $width;
		
		$ellipseHeight = $this->plotHeight - $this->depth - 40;
		
		$cx = $left + ($width / 2);
		$cy = $top + ($ellipseHeight / 2) + 20;
		
		$ellipseWidth = $width - 60;
		
		//	get the total
		$total = $this->getValueTotal();
		
		//
		//	sort the pieces from back to front
		//
		
		$sliceOrders = array();
		$sliceInfo = array();
		
		//	the general case
		$curTheta = -90;
		foreach($this->data as $name => $thisSlice)
		{
			if(!$thisSlice['value'])
				continue;

			//	find out how big the slice is
			$deltaTheta = 360 * ($thisSlice['value'] / $total);

			//	find which side of the slice is furthest back
			//echo $curTheta . ' ' . ($curTheta + $deltaTheta) . ' ' . NormalizeAngle($curTheta) . ' ' . NormalizeAngle($curTheta + $deltaTheta) . '<br>'; 
			if($deltaTheta >= 180)
			{
				$dist = 361;		//	force it do be drawn last
			}
			else
			{
				$dist1 = abs(270 - NormalizeAngle2($curTheta));
				$dist2 = abs(270 - NormalizeAngle2($curTheta + $deltaTheta));
				$dist = $dist1 < $dist2 ? $dist1 : $dist2;
			}

			//echo "angle1 = " . NormalizeAngle2($curTheta) . '<br>';
			//echo "angle2 = " . NormalizeAngle2($curTheta + $deltaTheta) . '<br>';
			//echo "$name $dist = $dist1 < $dist2 ? $dist1 : $dist2<br>";
			//echo "curTheta = $curTheta, deltaTheta = $deltaTheta;<br>";

			//	put them all into an array that can be sorted
			$sliceOrders[$name] = $dist;
			$thisSliceInfo->sTheta = $curTheta;
			$thisSliceInfo->eTheta = $curTheta + $deltaTheta;
			$thisSliceInfo->percentage = round($thisSlice['value'] * 100 / $total, 1);
			if( isset($thisSlice['url']) )
				$thisSliceInfo->url = $thisSlice['url'];
			$sliceInfo[$name] = clone $thisSliceInfo;

			//	move on to the next slice
			$curTheta += $deltaTheta;
		}

		asort($sliceOrders);
		
//		echo_r($sliceOrders);
//		echo_r($sliceInfo);
		
		$textRects = array();
		
		//	draw each slice
		foreach($sliceOrders as $name => $dist)
		{
			//if($name == 'color1')
			$thisSliceInfo = $sliceInfo[$name];
			
			//	we could do adjustments on a per slice basis to pull slices out to hilite them
			$centerx = $cx;
			$centery = $cy;
			
			if($reallyDraw)
			{
				//	set the fill color for this slice
				$this->context->setCurFillColor("color$name");
			}

			//	draw the 3D stuff
			if($this->depth)
			{
				if($reallyDraw)
				{
					//	draw the outside face of each slice
					if($thisSliceInfo->sTheta <= 180 && $thisSliceInfo->eTheta >= 0)
					{
						$sTheta = $thisSliceInfo->sTheta >= 0 ? $thisSliceInfo->sTheta : 0;
						$eTheta = $thisSliceInfo->eTheta <= 180 ? $thisSliceInfo->eTheta : 180;
						$this->context->addCylinderSlice($centerx, $centery, $ellipseWidth, $ellipseHeight, $sTheta, 
															$eTheta, $this->depth, 'DF');
					}
				}
			}
			
			//	draw the slice top
			if( isset($thisSliceInfo->url) )
				$url = $thisSliceInfo->url;
			else
				$url = NULL;
			
			if($reallyDraw)
			{
				//	special case for when there is only one slice
				if( ceil(abs($thisSliceInfo->sTheta - $thisSliceInfo->eTheta)) % 360 == 0)
				{
					$this->context->addEllipse($centerx, $centery, $ellipseWidth / 2, $ellipseHeight / 2, 'DF', $url);
				}
				else
				{
//					echo "$url<br>";
					$this->context->addArc($centerx, $centery, $ellipseWidth, $ellipseHeight, $thisSliceInfo->sTheta, $thisSliceInfo->eTheta, 'DF', $url);
				}
			}
			
			//	draw the label
			$ellipsex = NULL;
			$ellipsey = NULL;
			$textAngle = ($thisSliceInfo->sTheta + $thisSliceInfo->eTheta) / 2;
			if(NormalizeAngle($textAngle) < 180)
			{
				$textAdjustWidth = 80;
				$textAdjustHeight = 100;
			}
			else
			{
				$textAdjustWidth = 50;
				$textAdjustHeight = 20;
			}
			
			$labelText = $thisSliceInfo->percentage . '%';
			$textWidth = $this->context->getStringWidth($labelText);
			$textHeight = $this->context->getTextSize();
			
			EllipseCirclePos($centerx, $centery, $ellipseWidth, $ellipseHeight, 
								$textAngle, $ellipsex, $ellipsey);
			

			$circlePosx = $ellipsex;
			$circlePosy = $ellipsey;
//			$ellipsex += cos(deg2rad($textAngle));
//			$ellipsey += sin(deg2rad($textAngle));
			$ellipsex += cos(deg2rad($textAngle)) * $textWidth * 0.8;
			$ellipsey += sin(deg2rad($textAngle)) * $textHeight * 1.1;
			$ellipsex -= 0.5 * $textWidth;
			$ellipsey += 0.5 * $textHeight;
			
			if($textAngle > 0 && $textAngle < 180)
				$ellipsey += $this->depth;
			
			$thisRect = array('left' => $ellipsex, 'right' => $ellipsex + $textWidth,
								'top' => $ellipsey - $textHeight, 'bottom' => $ellipsey, 'text' => $labelText,
								'circlex' => $circlePosx, 'circley' => $circlePosy, 'angle' => $textAngle);
			$textRects[$textAngle] = $thisRect;
			
			//if($reallyDraw)
			//	$this->context->addText($ellipsex, $ellipsey, $labelText);
		}
		
		if($reallyDraw)
			$this->processLabels($textRects);
		
		return $this->getPlotHeight();
	}
	
	function processLabels($textRects)
	{
		ksort($textRects);
		//echo_r($textRects);
		
		$clusters = $this->clusterLabels($textRects);
		$this->drawClusters($clusters);
		//echo_r($clusters);
	}
	
	function drawClusters($clusters)
	{
		foreach($clusters as $thisCluster)
		{
			if(count($thisCluster) == 1)
			{
				$this->context->addText($thisCluster[0]['left'], $thisCluster[0]['bottom'], $thisCluster[0]['text']);
			}
			else
			{
				continue;
				
				//	maybe we can use the below code later.  Right now it doesn't handle enough
				//		cases so I'm just going to not draw any label that collides with
				//		another label
				$first = $thisCluster[0];
				$middle = $thisCluster[floor(count($thisCluster) / 2)];
				$last = $thisCluster[count($thisCluster) - 1];
				
				$m = ($last['bottom'] - $first['bottom']) / ($last['left'] - $first['left']);
				$b = $middle['bottom'] - ($m * $middle['left']);
				
				//$this->context->addLine(10, $m * 10 + $b, 800, $m * 800 + $b);
				
				if(abs($m) < 1)
				{
					if(abs($m < 0.3))
						$gap = 3;
					else
						$gap = 0;
					
					if($first['left'] < $last['left'])
					{
						$left = $first;
						$right = $last;
					}
					else
					{
						$left = $last;
						$right = $first;					
					}
					
					$totalWidth = 0;
					foreach($thisCluster as $thisBox)
					{
						$totalWidth += $thisBox['right'] - $thisBox['left'] + $gap;
					}
					
					if($thisCluster[0]['angle'] > 0 && $thisCluster[0]['angle'] < 180)
					{
						$curx = $middle['left'] + ($totalWidth / 2);

						foreach($thisCluster as $thisBox)
						{
							$curx -= $thisBox['right'] - $thisBox['left'] + $gap;
							$this->context->addText($curx, $m * $curx + $b, $thisBox['text']);
							$linex = $curx + (($thisBox['right'] - $thisBox['left']) / 2);
							$liney = $m * $linex + $b + (abs($m) / $m) - ($thisBox['bottom'] - $thisBox['top']);
							$this->context->addLine($linex, $liney, $thisBox['circlex'], $thisBox['circley']);							
						}
					}
					else
					{
						$curx = $middle['left'] - ($totalWidth / 2);

						foreach($thisCluster as $thisBox)
						{
							$this->context->addText($curx, $m * $curx + $b, $thisBox['text']);
							$linex = $curx + (($thisBox['right'] - $thisBox['left']) / 2);
							$liney = $m * $linex + $b + (abs($m) / $m);
							$this->context->addLine($linex, $liney, $thisBox['circlex'], $thisBox['circley']);
							$curx += $thisBox['right'] - $thisBox['left'] + $gap;
						}
					}
				}
				else
				{
					if($first['left'] < $last['left'])
					{
						$top = $first;
						$bottom = $last;
					}
					else
					{
						$top = $last;
						$bottom = $first;					
					}
					
					$totalHeight = 0;
					foreach($thisCluster as $thisBox)
					{
						$totalHeight += $thisBox['bottom'] - $thisBox['top'];
					}
					
					if($thisCluster[0]['angle'] > 90 && $thisCluster[0]['angle'] < 270)
					{
						$cury = $middle['bottom'] + ($totalHeight / 2);

						foreach($thisCluster as $thisBox)
						{
							$cury -= $thisBox['bottom'] - $thisBox['top'];
							$this->context->addText(($cury - $b) / $m, $cury, $thisBox['text']);
							$liney = $cury + (($thisBox['bottom'] - $thisBox['top']) / 2);
							$linex = (($liney - $b) / $m) - (abs($m) / $m) + ($thisBox['right'] - $thisBox['left']);
							$this->context->addLine($linex, $liney, $thisBox['circlex'], $thisBox['circley']);
						}
					}
					else
					{
						$cury = $middle['bottom'] - ($totalHeight / 2);

						foreach($thisCluster as $thisBox)
						{
							$this->context->addText(($cury - $b) / $m, $cury, $thisBox['text']);
							$liney = $cury + (($thisBox['bottom'] - $thisBox['top']) / 2);
							$linex = (($liney - $b) / $m) - (abs($m) / $m);
							$this->context->addLine($linex, $liney, $thisBox['circlex'], $thisBox['circley']);
							$cury += $thisBox['bottom'] - $thisBox['top'];
						}
					}
				}				
			}
		}
	}
	
	function clusterLabels($textRects)
	{
		$clusters = array();
		$curCluster = array();
		
		$lastRect = NULL;
		foreach($textRects as $thisRect)
		{
//			echo_r($thisRect);
			if($lastRect && !$this->rectIntersect($lastRect, $thisRect))
			{
				$clusters[] = $curCluster;
				$curCluster = array();
			}
			
			$curCluster[] = $thisRect;

			$lastRect = $thisRect;
			
//			echo "<b>cur cluster</b>";
//			echo_r($curCluster);
//			echo "<b>clusters</b>";
//			echo_r($clusters);
		}
		
		$clusters[] = $curCluster;
		
		if( count($clusters) > 1 )
		{
			//	see if the first and last clusters overlap
			if($this->rectIntersect($clusters[0][0], $clusters[count($clusters) - 1][count($clusters[count($clusters) - 1]) - 1]))
			{
				$end = array_pop($clusters);
				$clusters[0] = array_merge($end, $clusters[0]);
			}
		}
		
		return $clusters;
	}
	
	function rectIntersect($rect1, $rect2)
	{
		if($rect1['left'] < $rect2['left'])
		{
			$leftRect = $rect1;
			$rightRect = $rect2;
		}
		else
		{
			$leftRect = $rect2;
			$rightRect = $rect1;
		}
		
		if($rightRect['left'] >= $leftRect['right'])
			return false;
		
		if($rect1['top'] < $rect2['top'])
		{
			$topRect = $rect1;
			$bottomRect = $rect2;
		}
		else
		{
			$topRect = $rect2;
			$bottomRect = $rect1;
		}
		
		if($bottomRect['top'] >= $topRect['bottom'])
			return false;
		
		return true;
	}
}
?>