<?php
namespace MyCompany\Package;

use Ndm\JsonRpc2\Server as Server;

/**
 * A simple test script which exercises the core server components, utilising the FunctionListDispatch, and TestTransport implementations.
 *
 * This should be considered separate from the systems unit tests, and serves only as a proof of concept
 *
 * @author Nathan Muir
 * @version 2012-12-28
 */

require ('../../vendor/autoload.php');


// include the functions necessary to exercise the specification
require_once('mapMethods.php');

// create a simple dispatch object from the function mapping
$dispatch = new Server\Dispatch\MapDispatch();

$specificationTestMethods = new \SpecificationTestMethods();

$dispatch->registerAll(
    Server\Dispatch\ReflectionMethod::createFrom(
        $specificationTestMethods,
        true,
        function ($m) {
            return $m->name;
        }
    )
);


// retrieve the examples, [ { "description": "string", "request": "string", "expected_response": "string" } ]
$specExamples = (require('specificationTests.php'));
// create an iterator over the examples


// initialise variables for totals
$testsPassed = 0;
$testsFailed = 0;
$testsTotal = count($specExamples);
$currentTest = 1;
// go through each example, process the request and check the response
foreach ($specExamples as $specExample) {
    // print status message
    echo "Running Test {$currentTest}/{$testsTotal}... ";
    $currentTest++;
    // create a new transport
    $transport = new Server\Transport\TestTransport($specExample['request']);

    $server = new Server\Server($transport, $dispatch);

    try {
        $server->process();
    } catch (\Exception $ex) {
        echo "Failed \"{$specExample['description']}\"", PHP_EOL;
        $testsFailed++;
        continue;
    }

    if (strcmp($transport->getResponse(), $specExample['expected_response'])!=0) {
        echo "Failed \"{$specExample['description']}\" ", PHP_EOL;
        $testsFailed++;
        continue;
    }

    echo "Passed", PHP_EOL;
    $testsPassed++;
}

echo <<<TEXT


Tests Passed: {$testsPassed}/{$testsTotal}
TEXT;

// if tests failed is 0, then send exit-code for success (0)
exit ($testsFailed == 0 ? 0 : 1);