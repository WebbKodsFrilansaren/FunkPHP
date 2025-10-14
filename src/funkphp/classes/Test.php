<?php

namespace FunkPHP\Classes; // Match your composer.json

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
