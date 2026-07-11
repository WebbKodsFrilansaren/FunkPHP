<?php

namespace funkphp\classes;

class Test
{
    public function __construct()
    {
        // This only runs when explicitly called with 'new Test()'
        echo "Test class instantiated!";
    }
    public function hello()
    {
        echo "Hello from Test class!";
    }
}

class Test2
{
    public function __construct()
    {
        // This only runs when explicitly called with 'new Test()'
        echo "Test class instantiated!";
    }
    public function hello()
    {
        echo "Hello from Test class!";
    }
}
