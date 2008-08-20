<?

include('includes.php');

class TestOfZcache extends UnitTestCase {

	var $id;
	var $test_array;
	var $base;
	var $group;

	function setUp() {
		$this->id = "test_id";
		$this->test_array = array('1', '2', '3');
		$this->base = "MyCacheDirectory";
		$this->group = "MyCacheGroup";
	}
    
	function testClearAll()
	{
		zcache::clearAllCache();
	}

	function testGetDataEmpty() 
	{
		$array = zcache::getData($this->id, array('base' => $this->base, 'group'=>$this->group));
		$this->assertTrue(!$array, "array has data that shouldn't exist");
	}

	function testCacheData() 
	{
		zcache::cacheData($this->id, $this->test_array, array('base' => $this->base, 'group'=>$this->group));
		$ret_array = zcache::getData($this->id, array('base' => $this->base, 'group'=>$this->group));
		$this->assertTrue(isset($ret_array), "array has no data");
		$this->assertTrue($this->test_array==$ret_array, "array data isnt valid");
	}

	function testClearNode() 
	{
        zcache::clear($this->id, array('base' => $this->base, 'group' => $this->group));
		$ret_array = zcache::getData($this->id, array('base' => $this->base, 'group'=>$this->group));
		$this->assertTrue(!$ret_array, "array has data, but shouldn't exist");
	}

	function testClearGroup()
	{
		// cache the node again
		$this->testCacheData();
        zcache::clearGroup($this->group, array('base' => $this->base, 'group' => $this->group));
		$ret_array = zcache::getData($this->id, array('base' => $this->base, 'group'=>$this->group));
		$this->assertTrue(!$ret_array, "array has data, but shouldn't exist");
	}

	function testClearBase()
	{
		// cache the node again
		$this->testCacheData();
        zcache::clearBase($this->base);
		$ret_array = zcache::getData($this->id, array('base' => $this->base, 'group'=>$this->group));
		$this->assertTrue(!$ret_array, "array has data, but shouldn't exist");
	}

}


$test = &new TestOfZcache();
$test->run(new TextReporter());
