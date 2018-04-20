<?php

namespace PatrickBierans\ReflectiveConstructorInjection;

/**
 * Class RCI.
 * Reflective Constructor Injection.
 *
 * Avoid using it!
 * Learn why!
 *
 * Then decide if at all
 * and under which circumstances you are willing
 * to break the rules
 * and use it anyway.
 */
class RCI {
    protected static $map = [];

    /**
     * @param string $class
     * @param array $values
     *
     * @return object
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    public static function get($class, array $values = []) {
        if (isset (static::$map[$class])) {
            $class = static::$map[$class];
        }
        $reflectionClass = new \ReflectionClass ($class);
        if (!$reflectionClass->isInstantiable()) {
            throw new \RuntimeException ("can not create an instance of class $class");
        }
        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return new $class ();
        }

        $args = [];
        foreach ($constructor->getParameters() as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();
            $reflectionClass = $reflectionParameter->getClass();
            if (isset ($values [$parameterName])) {
                $args[] = $values [$parameterName];
            } else if ($reflectionClass !== null) {
                $args[] = static::get($reflectionClass->name);
            } else if ($reflectionParameter->isDefaultValueAvailable()) {
                $args[] = $reflectionParameter->getDefaultValue();
            } else {
                throw new \RuntimeException (
                    'Can not determine a value to pass to constructor of class ' . $class
                    . ': Parameter $' . $reflectionParameter->getName() . ' at position ' . $reflectionParameter->getPosition()
                    . ' has no default value and is not a class.'
                );
            }
        }

        return $reflectionClass->newInstanceArgs($args);
    }

    /**
     * static globaler Setter.
     *
     * @param string $alias
     * @param string|object $class
     */
    public static function map($alias, $class): void {
        static::$map[$alias] = $class;
    }
}
