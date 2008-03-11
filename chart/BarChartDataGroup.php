<?
class BarChartDataGroup
{
	var $entries;
	var $text;
	var $total;
	var $max;
	var $display;
	var $converted;
	var $summary;
	var $parent;
	
	function BarChartDataGroup()
	{
		$entries = array();
		$text = '';
		$total = 0;
		$max = 0;
		$this->display="raw";
		$this->converted = 0;
		$this->summary = array();
		$this->parent = null;
	}
	
	function setParent(&$obj)
	{
		$this->parent = &$obj;
	}
	
	function setText($text)
	{
		$this->text = $text;
	}
	
	function getText()
	{
		return $this->text;
	}
	
	function setDisplay($display)
	{
		$this->display = $display;
	}
	
	function getDisplay()
	{
		return $this->display;
	}
	
	function addSummary($name, $type)
	{
		$this->summary[$name] = $type;
	}
	
	function getSummaryData($name)
	{
		$answer = '';
		if(isset($this->summary[$name]))
		{
			switch($this->summary[$name])
			{
				case 'average':
					$item = 0;
					foreach($this->entries as $entry)
					{
						$item += $entry['value'] * $entry['catValue'];
					}
					$answer = round($item / $this->total, 1);
					break;
				case 'count':
					if($this->converted)
						$answer = $this->origTotal;
					else
						$answer = $this->total;
					break;
			}
		}
		return $answer;
	}
	
	function addEntry($info)
	{
		$this->entries[] = $info;
		
		$this->total += $info['value'];
		
		if($info['value'] > $this->max)
			$this->max = $info['value'];
	}
	
	function getEntries()
	{
		if($this->display == 'percent' && !$this->converted)
		{
			$answer = array();
			$this->max = 0;
			foreach($this->entries as $index => $entry)
			{
				$this->entries[$index] = $entry;
				$this->entries[$index]['value'] = round(($entry['value'] / $this->total) * 100, 1);
				if($this->entries[$index]['value'] > $this->max)
					$this->max = $this->entries[$index]['value'];
			}
			$this->origTotal = $this->total;
			$this->total = 100;
			$this->converted = 1;
			
		}
		return $this->entries;
	}
	
	function getTotal()
	{
		return $this->total;
	}
	
	function getMax()
	{
		return $this->max;
	}
	
	function entryCount()
	{
		return count($this->entries);
	}
	
	function catCount($catName)
	{
		$count = 0;
		foreach($this->entries as $thisEntry)
		{
			if($thisEntry['category'] == $catName)
				$count++;
		}
		
		return $count;
	}
}
?>