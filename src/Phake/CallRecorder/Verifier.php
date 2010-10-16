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

/**
 * Can verify calls recorded into the given recorder.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_CallRecorder_Verifier
{
	
	/**
	 * @var Phake_CallRecorder_Recorder
	 */
	protected $recorder;

	/**
	 * @var object
	 */
	protected $obj;

	/**
	 * @param Phake_CallRecorder_Recorder $recorder
	 * @param <type> $obj
	 */
	public function __construct(Phake_CallRecorder_Recorder $recorder, $obj)
	{
		$this->recorder = $recorder;
		$this->obj = $obj;
	}

	/**
	 * Returns whether or not a call has been made in the associated call recorder.
	 * 
	 * @todo Maybe rename this to findMatchedCalls?
	 * @param string $method
	 * @param array $argumentMatcher
	 * @return boolean
	 */
	public function verifyCall($method, array $argumentMatchers)
	{
		$calls = $this->recorder->getAllCalls();

		$matchedCalls = array();
		foreach ($calls as $call)
		{
			/* @var $call Phake_CallRecorder_Call */
			if ($call->getMethod() == $method 
							&& $call->getObject() === $this->obj
							&& count($call->getArguments()) == count($argumentMatchers))
			{
				if ($this->validateArguments($call->getArguments(), $argumentMatchers))
				{
					$matchedCalls[] = $this->recorder->getCallInfo($call);
				}
			}
		}

		return $matchedCalls;
	}

	/**
	 * Returns whether or not the passed in arguments match all of the passed in argument matchers.
	 * @param array $arguments
	 * @param array $argumentMatchers
	 * @return boolean
	 */
	private function validateArguments(array $arguments, array $argumentMatchers)
	{
			reset($argumentMatchers);
			foreach ($arguments as  $i => $argument)
			{
				$matcher = current($argumentMatchers);

				if (!$matcher instanceof Phake_Matchers_IArgumentMatcher)
				{
					throw new InvalidArgumentException("Argument matcher [{$i}] is not a valid matcher");
				}

				/* @var $matcher Phake_Matchers_IArgumentMatcher */
				if (!$matcher->matches($argument))
				{
					return FALSE;
				}

				next($argumentMatchers);
			}

			return TRUE;
	}
}
?>
