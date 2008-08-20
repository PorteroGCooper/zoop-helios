<?

include('includes.php');

class TestOfLogging extends UnitTestCase {
    
    function testCreatingNewFile() {
        $this->assertTrue(true);
    }
}


$test = &new TestOfLogging();
$test->run(new HtmlReporter());
