<?php
namespace SRESTO\Tests;
/**
 * Class ExampleTest
 */
class ExampleTest extends \PHPUnit_Framework_TestCase{
    
    public function provider()
    {
        return [
            'my named data' => [true],
            'my data'       => [true],
            'data 2'        => [true]
        ];
    }

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

    
}