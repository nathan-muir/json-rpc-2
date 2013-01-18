<?php
/**
 * A simple test script which exercises the core server components, utilising the FunctionListDispatch, and TestTransport implementations.
 *
 * This should be considered separate from the systems unit tests, and serves only as a proof of concept
 *
 * @author Nathan Muir
 * @version 2012-12-28
 */

// include the JsonRpc library, and configure the include path
// the src directory relative to the current
$libPath = realpath(__DIR__ . '/../../src');
// create a new include path, adding $libPath
$newIncludePath = get_include_path() . PATH_SEPARATOR . $libPath;
set_include_path($newIncludePath);
// incldue the loader for the library
require_once('JsonRpc/Loader.php');
// register the PSR-0 compliant loader, using the src-directory
\JsonRpc\Loader::register();


// include the functions necessary to exercise the specification
require_once('mapMethods.php');

// create a simple dispatch object from the function mapping
$dispatch = new \JsonRpc\Dispatch\MapDispatch();

$specificationTestMethods = new SpecificationTestMethods();

$dispatch->registerAll(
    \JsonRpc\Dispatch\ReflectionMethod::createFrom(
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
    $transport = new \JsonRpc\Transport\TestTransport($specExample['request'], $specExample['expected_response']);

    $server = new \JsonRpc\Server($transport, $dispatch);

    try {
        $server->process();
    } catch (Exception $ex) {

        echo "Failed \"{$specExample['description']}\"", PHP_EOL;
        $testsFailed++;
        continue;
    }

    if (!$transport->checkResponse()) {
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