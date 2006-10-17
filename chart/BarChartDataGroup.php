<?
class BarChartDataGroup
{
	var $entries;
	var $text;
	var $total;
	var $max;
	
	function BarChartDataGroup()
	{
		$entries = array();
		$text = '';
		$total = 0;
		$max = 0;
	}
	
	function setText($text)
	{
		$this->text = $text;
	}
	
	function getText()
	{
		return $this->text;
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