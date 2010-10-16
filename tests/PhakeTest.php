<?php
/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010, Mike Lively <mike.lively@sellingsource.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  *  Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  *  Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *  *  Neither the name of Mike Lively nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    Phake
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

require_once('Phake.php');
require_once('PhakeTest/AbstractClass.php');
require_once('PhakeTest/MockedClass.php');
require_once('PhakeTest/MockedInterface.php');

/**
 * Tests the behavior of the Phake class.
 *
 * The tests below are really all integration tests.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class PhakeTest extends PHPUnit_Framework_TestCase
{
	/**
	 * General test for Phake::mock() that it returns a class that inherits from the passed class.
	 */
	public function testMock()
	{
		$this->assertThat(Phake::mock('stdClass'), $this->isInstanceOf('stdClass'));
	}

	/**
	 * Tests that a simple method call can be verified
	 */
	public function testSimpleVerifyPasses()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->foo();

		Phake::verify($mock)->foo();
	}

	/**
	 * Tests that a simple method call verification with throw an exception if that method was not
	 * called.
	 *
	 * @expectedException Exception
	 */
	public function testSimpleVerifyThrowsExceptionOnFail()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::verify($mock)->foo();
	}

	/**
	 * Tests that a simple method call can be stubbed to return an expected value.
	 */
	public function testSimpleStub()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::when($mock)->foo()
			->thenReturn(42);

		$this->assertEquals(42, $mock->foo());
	}

	/**
	 * Tests passing a basic equals matcher to the verify method will correctly verify a call.
	 */
	public function testVerifyCallWithEqualsMatcher()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('bar');

		Phake::verify($mock)->fooWithArgument(Phake::equalTo('bar'));
	}

	/**
	 * Tests passing a basic equals matcher to the verify method will correctly fail when matcher is not satisfied.
	 *
	 * @expectedException Exception
	 */
	public function testVerifyCallWithEqualsMatcherFails()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('test');

		Phake::verify($mock)->fooWithArgument(Phake::equalTo('bar'));
	}

	/**
	 * Tests that we can implicitely indicate an equalTo matcher when we pass in a non-matcher value.
	 */
	public function testVerifyCallWithDefaultMatcher()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('bar');

		Phake::verify($mock)->fooWithArgument('bar');
	}

	/**
	 * Tests passing a default matcher type to the verify method will correctly fail when matcher is not satisfied.
	 *
	 * @expectedException Exception
	 */
	public function testVerifyCallWithDefaultMatcherFails()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('test');

		Phake::verify($mock)->fooWithArgument('bar');
	}

	/**
	 * Tests passing in a PHPUnit constraint to the verifier
	 */
	public function testVerifyCallWithPHPUnitMatcher()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('bar');

		Phake::verify($mock)->fooWithArgument($this->equalTo('bar'));
	}

	/**
	 * Tests passing in a PHPUnit constraint to the verifier fails when constraint not met.
	 *
	 * @expectedException Exception
	 */
	public function testVerifyCallWithPHPUnitMatcherFails()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('test');

		Phake::verify($mock)->fooWithArgument($this->equalTo('bar'));
	}

	/**
	 * Tests passing in a Hamcrest matcher to the verifier
	 */
	public function testVerifyCallWithHamcrestMatcher()
	{
		if (!HAMCREST_LOADED)
		{
			$this->markTestSkipped('Hamcrest library not available');
		}

		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('bar');

		Phake::verify($mock)->fooWithArgument(equalTo('bar'));
	}

	/**
	 * Tests passing in a Hamcrest matcher to the verifier fails when constraint not met.
	 *
	 * @expectedException Exception
	 */
	public function testVerifyCallWithHamcrestMatcherFails()
	{
		if (!HAMCREST_LOADED)
		{
			$this->markTestSkipped('Hamcrest library not available');
		}

		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('test');

		Phake::verify($mock)->fooWithArgument(equalTo('bar'));
	}

	/**
	 * Tests using an equalTo argument matcher with a method stub
	 */
	public function testStubWithEqualsMatcher()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::when($mock)->fooWithArgument(Phake::equalTo('bar'))->thenReturn(42);

		$this->assertEquals(42, $mock->fooWithArgument('bar'));
		$this->assertNull($mock->fooWithArgument('test'));
	}

	/**
	 * Tests using an implicit equalTo argument matcher with a method stub
	 */
	public function testStubWithDefaultMatcher()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::when($mock)->fooWithArgument('bar')->thenReturn(42);

		$this->assertEquals(42, $mock->fooWithArgument('bar'));
		$this->assertNull($mock->fooWithArgument('test'));
	}

	/**
	 * Tests using a phpunit constraint with a method stub
	 */
	public function testStubWithPHPUnitConstraint()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::when($mock)->fooWithArgument($this->equalTo('bar'))->thenReturn(42);

		$this->assertEquals(42, $mock->fooWithArgument('bar'));
		$this->assertNull($mock->fooWithArgument('test'));
	}

	/**
	 * Tests using a hamcrest matcher with a method stub
	 */
	public function testStubWithHamcrestConstraint()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::when($mock)->fooWithArgument(equalTo('bar'))->thenReturn(42);

		$this->assertEquals(42, $mock->fooWithArgument('bar'));
		$this->assertNull($mock->fooWithArgument('test'));
	}

	/**
	 * Tests that resetting a mock clears the call recorder
	 */
	public function testResettingCallRecorder()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->foo();

		Phake::verify($mock)->foo();

		Phake::reset($mock);

		$this->setExpectedException('Exception');

		Phake::verify($mock)->foo();
	}

	/**
	 * Tests that resetting a mock clears the stubber
	 */
	public function testResettingStubMapper()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::when($mock)->foo()->thenReturn(42);

		$this->assertEquals(42, $mock->foo());

		Phake::reset($mock);

		$this->assertNull($mock->foo());
	}

	/**
	 * Tests setting a default answer for stubs
	 */
	public function testDefaultAnswerForStubs()
	{
		$mock = Phake::mock('PhakeTest_MockedClass', Phake::ifUnstubbed()->thenReturn(42));

		$this->assertEquals(42, $mock->foo());
	}

	/**
	 * Tests stubbing a mocked method to call its parent.
	 */
	public function testStubbingMethodToCallParent()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::when($mock)->fooWithReturnValue()->thenCallParent();

		$this->assertEquals('blah', $mock->fooWithReturnValue());
	}

	/**
	 * Tests calling through a chain of calls
	 */
	public function testStubbingChainedMethodsToCallParent()
	{
		$mock = Phake::mock('PhakeTest_MockedClass', Phake::ifUnstubbed()->thenCallParent());

		$this->assertEquals('test', $mock->callInnerFunc());
	}

	/**
	 * Tests spy functionality to make sure original method is called.
	 */
	public function testSpyCallsOriginal()
	{
		$spiedOn = $this->getMock('PhakeTest_MockedClass');
		$spiedOn->expects($this->once())
			->method('foo');

		$spy = Phake::spy($spiedOn);
		$spy->foo();
	}

	/**
	 * Tests spy functionality to make sure original value is returned.
	 */
	public function testSpyReturnsValue()
	{
		$spiedOn = $this->getMock('PhakeTest_MockedClass');
		$spiedOn->expects($this->any())
			->method('foo')
			->will($this->returnValue(42));


		$spy = Phake::spy($spiedOn);

		$this->assertEquals(42, $spy->foo());
	}

	/**
	 * Tests spy calls are recorded
	 */
	public function testSpyRecordsCall()
	{
		$spiedOn = $this->getMock('PhakeTest_MockedClass');
		$spiedOn->expects($this->any())
			->method('foo');


		$spy = Phake::spy($spiedOn);
		$spy->foo();

		Phake::verify($spy)->foo();
	}

	/**
	 * Tests mocking of an interface
	 */
	public function testMockingInterface()
	{
		$mock = Phake::mock('PhakeTest_MockedInterface');

		Phake::when($mock)->foo()->thenReturn('bar');

		$this->assertEquals('bar', $mock->foo());
	}

	/**
	 * Tests mocking of an abstract class
	 */
	public function testMockingAbstract()
	{
		$mock = Phake::mock('PhakeTest_AbstractClass');

		Phake::when($mock)->foo()->thenReturn('bar');

		$this->assertEquals('bar', $mock->foo());
	}

	/**
	 * Tests verifying the call order of particular methods within an object
	 */
	public function testCallOrderInObject()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->foo();
		$mock->fooWithReturnValue();
		$mock->callInnerFunc();

		Phake::inOrder(
						Phake::verify($mock)->foo(),
						Phake::verify($mock)->fooWithReturnValue(),
						Phake::verify($mock)->callInnerFunc()
		);
	}

	/**
	 * Tests verifying the call order of particular methods within an object
	 */
	public function testCallOrderInObjectFails()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->foo();
		$mock->callInnerFunc();
		$mock->fooWithReturnValue();

		$this->setExpectedException('Exception');

		Phake::inOrder(
						Phake::verify($mock)->foo(),
						Phake::verify($mock)->fooWithReturnValue(),
						Phake::verify($mock)->callInnerFunc()
		);
	}

	/**
	 * Tests verifying the call order of particular methods across objects
	 */
	public function testCallOrderAccrossObjects()
	{
		$mock1 = Phake::mock('PhakeTest_MockedClass');
		$mock2 = Phake::mock('PhakeTest_MockedClass');

		$mock1->foo();
		$mock2->foo();
		$mock1->fooWithReturnValue();
		$mock2->fooWithReturnValue();
		$mock1->callInnerFunc();
		$mock2->callInnerFunc();

		Phake::inOrder(
						Phake::verify($mock1)->foo(),
						Phake::verify($mock2)->foo(),
						Phake::verify($mock2)->fooWithReturnValue(),
						Phake::verify($mock1)->callInnerFunc()
		);
	}

	/**
	 * Tests verifying the call order of particular methods across objects
	 */
	public function testCallOrderAccrossObjectsFail()
	{
		$mock1 = Phake::mock('PhakeTest_MockedClass');
		$mock2 = Phake::mock('PhakeTest_MockedClass');

		$mock1->foo();
		$mock2->foo();
		$mock1->fooWithReturnValue();
		$mock1->callInnerFunc();
		$mock2->fooWithReturnValue();
		$mock2->callInnerFunc();

		$this->setExpectedException('Exception');

		Phake::inOrder(
						Phake::verify($mock2)->fooWithReturnValue(),
						Phake::verify($mock1)->callInnerFunc()
		);
	}

	/**
	 * Tests freezing mocks
	 */
	public function testMockFreezing()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->foo();

		Phake::verifyNoFurtherInteraction($mock);

		$this->setExpectedException('Exception');

		$mock->foo();
	}

	/**
	 * Tests verifying that no interaction occured
	 */
	public function testVerifyingZeroInteraction()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		Phake::verifyNoInteraction($mock);

		$mock->foo();

		$this->setExpectedException('Exception');
		Phake::verifyNoInteraction($mock);
	}

	/**
	 * Tests argument capturing
	 */
	public function testArugmentCapturing()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('TEST');

		Phake::verify($mock)->fooWithArgument(Phake::capture($toArgument));

		$this->assertSame('TEST', $toArgument);
	}

	/**
	 * Tests conditional argument capturing
	 */
	public function testConditionalArugmentCapturing()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');

		$mock->fooWithArgument('FOO');

		$mock->fooWithArgument('BAR');


		Phake::verify($mock)->fooWithArgument(Phake::capture($toArgument)->when('BAR'));

		$this->assertSame('BAR', $toArgument);
	}
	
	/**
	 * Tests times matches exactly
	 */
	public function testVerifyTimesExact()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');
		
		$mock->foo();
		$mock->foo();
		
		Phake::verify($mock, Phake::times(2))->foo();
	}
	
	/**
	 * Tests times doesn't match
	 * @expectedException Exception
	 */
	public function testVerifyTimesMismatch()
	{
		$mock = Phake::mock('PhakeTest_MockedClass');
		
		$mock->foo();
		$mock->foo();
		
		Phake::verify($mock)->foo();
	}
}

?>
