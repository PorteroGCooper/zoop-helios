<?php

define('FIBO_PHI',(1+sqrt(5))/2);

class fibo {

	var $sequence;

	function fibo($length=null,$start=null) {
		if(is_numeric($length))
			$this->sequence($length,$start);
	}

	function f($n) {
		return floor(
			( pow(FIBO_PHI,$n) / sqrt(5) )
			+
			.5
		);
	}

	function sequence($length=10,$start=1) {
		$length = ($length < 2) ? 2 : floor($length);
		$start = floor($start);

		$sequence[0] = $this->f($start);
		$sequence[1] = $this->f($start+1);
		$i = 2;
		while($i < $length) {
			$sequence[$i] = $sequence[$i-1] + $sequence[$i-2];	
			$i++;
		}

		$this->sequence = $sequence;
		return $sequence;
	}

	function showseq($preline = "\n") {
		if(is_array($this->sequence) && (($l = count($this->sequence)) > 0))
		for($i=0; $i<$l; $i++) {
			echo $preline;
			echo $this->sequence[$i];
		}
	}

	function __toString() {
		return "Object (fibo)";
	}
}

?>
