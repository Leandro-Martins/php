<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/TextUI/ResultPrinter.php');

global $suite;

function rodaTest($class, $run = true)
{
    global $suite;
    if (count($_SERVER['argv'])) {
        if (!$suite) {
            $suite = new PHPUnit_Framework_TestSuite();
        }
        $suite->addTestSuite($class);
        if ($run) {
            runTest();
        }
    }
}

function runTest()
{
    global $suite;
    $result = $suite->run();
    $reporter = new PHPUnit_TextUI_ResultPrinter(NULL, TRUE, TRUE, TRUE);
    $reporter->printResult($result);
}

if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    foreach(array('pagseguro', 'carrinho') as $test) {
        include_once $test.'Test.php';
        rodaTest(ucfirst($test).'Test', false);
    }
    runTest();
}
