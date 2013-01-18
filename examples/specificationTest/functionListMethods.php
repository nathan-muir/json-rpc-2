<?php

function subtract($minuend, $subtrahend)
{
    return $minuend - $subtrahend;
}

function notify_sum($list)
{
    return true;
}

function sum($a, $b, $c)
{
    return $a + $b + $c;
}

function notify_hello($int)
{
    return true;
}

function get_data()
{
    return array("hello", 5);
}

return array(
    'subtract' => 'subtract',
    'sum' => 'sum',
    'notify_hello' => 'notify_hello',
    'notify_sum' => 'notify_sum',
    'get_data' => 'get_data'
);