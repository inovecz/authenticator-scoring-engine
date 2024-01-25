<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public static function callMethod($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        try {
            $method = $class->getMethod($name);
            $method->setAccessible(true);
            return $method->invokeArgs($obj, $args);
        } catch (\ReflectionException $e) {
            echo $e->getMessage();
        }
    }
}
