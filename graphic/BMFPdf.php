<?php

class BMFPdf extends FPDF
{
	function Text($x, $y, $txt, $params = array())
	{
		$s = 'BT ';
		if( isset($params['angle']) )
		{
			$a = deg2rad($params['angle']);
			$s .= sprintf('%.2f %.2f %.2f %.2f %.2f %.2f Tm ', cos($a), -1.0*sin($a), 
								sin($a), cos($a), $x*$this->k, ($this->h-$y)*$this->k);
		}
		else
		{
			$s .= sprintf('%.2f %.2f Td ', $x*$this->k, ($this->h-$y)*$this->k);
		}
		
		$s .= sprintf('(%s) Tj ET', $this->_escape($txt));
		
		if($this->underline and $txt!='')
			$s.=' '.$this->_dounderline($x,$y,$txt);
		if($this->ColorFlag)
			$s='q '.$this->TextColor.' '.$s.' Q';
		
		$this->_out($s);
	}
	
	function Circle($x,$y,$r,$style='')
	{
		$this->Ellipse($x,$y,$r,$r,$style);
	}
	
	//	this came from the google cache of some german site that I didn't
	//		understand
	
	function Ellipse($x,$y,$rx,$ry,$style='D')
	{
		if($style=='F')
			$op='f';
		elseif($style=='FD' or $style=='DF')
			$op='B';
		else
			$op='S';
		
		$lx=4/3*(M_SQRT2-1)*$rx;
		$ly=4/3*(M_SQRT2-1)*$ry;
		$k=$this->k;
		$h=$this->h;
		
		$this->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c',
			($x+$rx)*$k,($h-$y)*$k,
			($x+$rx)*$k,($h-($y-$ly))*$k,
			($x+$lx)*$k,($h-($y-$ry))*$k,
			$x*$k,($h-($y-$ry))*$k));
		$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
			($x-$lx)*$k,($h-($y-$ry))*$k,
			($x-$rx)*$k,($h-($y-$ly))*$k,
			($x-$rx)*$k,($h-$y)*$k));
		$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
			($x-$rx)*$k,($h-($y+$ly))*$k,
			($x-$lx)*$k,($h-($y+$ry))*$k,
			$x*$k,($h-($y+$ry))*$k));
		$this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s',
			($x+$lx)*$k,($h-($y+$ry))*$k,
			($x+$rx)*$k,($h-($y+$ly))*$k,
			($x+$rx)*$k,($h-$y)*$k,
			$op));
	}
	
	
	//	This uses the tweaked logic of imagearc and imagefilledarc for evaluating angles
	//		not logic that would actually draw the angles passed in.  This may need to be tweaked
	//		for compatibility with other context modules.
	
	//	arc and curve were made by myself from the pdf spec
	function Arc($x, $y, $rx, $ry, $sTheta, $eTheta, $style = 'D')
	{
		//	convert the angles
		$sTheta = deg2rad((float)$sTheta);
		$eTheta = deg2rad((float)$eTheta);
		
		//	move the pen to the starting point
		$this->_move($x, $y);
		
		$this->_arc($x, $y, $rx, $ry, $sTheta, $eTheta, $style);
		
		//	close off the path
		$this->_closePath($style);
	}
	
	function CylinderSlice($x, $y, $rx, $ry, $sTheta, $eTheta, $depth, $style = 'D')
	{
		if($sTheta < 0)
			$sTheta = 0;
		if($eTheta > 180)
			$eTheta = 180;
		
		//	convert the angles
		$sTheta = deg2rad((float)$sTheta);
		$eTheta = deg2rad((float)$eTheta);
		
		//	get the first point on the ellipse
		$x1 = $x + $rx * cos($sTheta);
		$y1 = $y + $ry * sin($sTheta);
		
		//	move the pen to the starting point and start drawing
		$this->_move($x1, $y1);
		$this->_line($x1, $y1 + $depth);
		$this->_arc($x, $y + $depth, $rx, $ry, $sTheta, $eTheta, $style);
		
		//	get the second point on the ellipse
		$x2 = $x + $rx * cos($eTheta);
		$y2 = $y + $ry * sin($eTheta);
		
		//	come back up and draw the rest
		$this->_line($x2, $y2);
		$this->_arc($x, $y, $rx, $ry, $eTheta, $sTheta, $style);
		
		//	close off the path
		$this->_closePath($style);
	}
	
	function _move($x, $y)
	{
		$k = $this->k;
		$h = $this->h;
		
		$x = $x * $k;
		$y = ($h - $y) * $k;
		$this->_out(sprintf(' %.2f %.2f m ', $x, $y));		
	}
	
	function _line($x, $y)
	{
		$k = $this->k;
		$h = $this->h;
		
		$x = $x * $k;
		$y = ($h - $y) * $k;
		$this->_out(sprintf(' %.2f %.2f l ', $x, $y));		
	}
	
	function _curve($x1, $y1, $x2, $y2, $x3, $y3)
	{
		$k = $this->k;
		$h = $this->h;
		
		$x1 = $x1 * $k;
		$y1 = ($h - $y1) * $k;
		$x2 = $x2 * $k;
		$y2 = ($h - $y2) * $k;
		$x3 = $x3 * $k;
		$y3 = ($h - $y3) * $k;
		
		$this->_out(sprintf(' %.2f %.2f %.2f %.2f %.2f %.2f c ', $x1, $y1, $x2, $y2, $x3, $y3));		
	}
	
	function _arc($x, $y, $rx, $ry, $sTheta, $eTheta, $style = 'D')
	{
		$totalAngle = $eTheta - $sTheta;
		
		$nSeg = 8;
		$dTheta = $totalAngle / $nSeg;
		$dtm = $dTheta / 3;
		
		//	set up these variables
		$curTheta = $sTheta;
		$a0 = $x + $rx * cos($curTheta);
		$b0 = $y + $ry * sin($curTheta);
		$c0 = -$rx * sin($curTheta);
		$d0 = $ry * cos($curTheta);
		
		$this->_line($a0, $b0);
		//	break it up into 8 mini curves and tack them onto the path
		for($i = 1; $i <= $nSeg; $i++)
		{
			$curTheta = $sTheta + ($i * $dTheta);
			$a1 = $x + $rx * cos($curTheta);
			$b1 = $y + $ry * sin($curTheta);
			$c1 = -$rx * sin($curTheta);
			$d1 = $ry * cos($curTheta);
			
			$this->_curve($a0+$c0*$dtm, $b0+$d0*$dtm, $a1-$c1*$dtm, $b1-$d1*$dtm, $a1, $b1);
			
			$a0 = $a1;
			$b0 = $b1;
			$c0 = $c1;
			$d0 = $d1;
		}
	}
	
	function _closePath($style)
	{
		if($style=='F')
			$op='f';
		elseif($style=='FD' or $style=='DF')
			$op='b';
		else
			$op='s';
		
		$this->_out(' ' . $op);
	}
	
	function Curve($startx, $starty, $x1, $y1, $x2, $y2, $x3, $y3)
	{
		$this->_curve($startx, $starty, $x1, $y1, $x2, $y2, $x3, $y3);
		$this->_closePath();
	}
	
	function Polygon($points, $style='D')
	{
		//Draw a polygon
		if($style=='F')
			$op='f';
		elseif($style=='FD' or $style=='DF')
			$op='b';
		else
			$op='s';

		$h = $this->h;
		$k = $this->k;

		$points_string = '';
		for($i=0; $i<count($points); $i+=2)
		{
			$points_string .= sprintf('%.2f %.2f', $points[$i]*$k, ($h-$points[$i+1])*$k);
			if($i==0)
				$points_string .= ' m ';
			else
				$points_string .= ' l ';
		}
		$this->_out($points_string . $op);
	}
	
	function Raw($rawPdfData)
	{
		$this->_out($rawPdfData);
	}
}

/*
$pdf=new PDF();
$pdf->Open();
$pdf->AddPage();
$pdf->Ellipse(100,50,30,20);
$pdf->SetFillColor(255,255,0);
$pdf->Circle(110,47,7,'F');
$pdf->Output();
*/
?>