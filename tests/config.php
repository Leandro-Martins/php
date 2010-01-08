<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

function rodaTest($class)
{
    if (count($_SERVER['argv'])) {
        require_once('PHPUnit/Framework.php');
        require_once('PHPUnit/TextUI/ResultPrinter.php');

        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite($class);
        $result = $suite->run();
        $reporter = new PHPUnit_TextUI_ResultPrinter;
        $reporter->printResult($result);
    }
}

