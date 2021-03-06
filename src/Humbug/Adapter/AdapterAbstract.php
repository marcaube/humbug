<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter;

use Humbug\Container;

abstract class AdapterAbstract
{
    
    /**
     * Runs the tests suite according to Runner set options and the execution
     * order of test case (if any). It then returns an array of two elements.
     * First element is a boolean result value indicating if tests passed or not.
     * Second element is an array containing the key "stdout" which stores the
     * output from the last test run.
     *
     * @param   \Humbug\container $container
     * @param   bool              $useStdout
     * @param   bool              $firstRun
     * @param   array             $mutation
     * @param   array             $testCases
     * @return  array
     */
    abstract public function getProcess(
        Container $container,
        $firstRun = false,
        $interceptFile = null,
        $mutantFile = null,
        array $testCases = []
    );

    /**
     * Parse the test adapter result output to see if there were any failures.
     * In the context of mutation testing, a test failure is good (i.e. the
     * mutation was detected by the test suite).
     *
     * This assume the output is in Test Anywhere Protocol (TAP) format.
     *
     * @param string $output
     * @return bool
     */
    public static function ok($output)
    {
        if (preg_match("%[\n\r]+not ok \\d+%", $output)) {
            return false;
        }
        return true;
    }
}
