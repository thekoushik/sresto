<?php
namespace SRESTO\Tests;
/**
 * Class ExampleTest
 */
class ExampleTestCase extends \PHPUnit_Framework_TestCase{
	/**
	 * Test Case 1
	 */
	public function testTrueIsTrue(){
	    $foo = true;
	    $this->assertTrue($foo);
	}

	/**
     * @dataProvider provider
     */
    public function testMethod($data)
    {
        $this->assertTrue($data);
    }

    public function provider()
    {
        return [
            'my named data' => [true],
            'my data'       => [true]
        ];
    }
}