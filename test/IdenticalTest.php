<?php

declare(strict_types=1);

namespace EstasiTest\Validator;

use Ds\Map;
use Estasi\Validator\Identical;
use PHPUnit\Framework\TestCase;
use stdClass;

class IdenticalTest extends TestCase
{
    /**
     * @param mixed $token
     * @param bool  $strict
     * @param mixed $value
     * @param bool  $expected
     *
     * @dataProvider identicalTestWithoutContextProvider
     */
    public function testIdenticalWithoutContext($token, bool $strict, $value, bool $expected)
    {
        $identical = new Identical($token, $strict);
        $this->assertEquals($expected, $identical->isValid($value));
    }

    /**
     * @param mixed $token
     * @param bool  $strict
     * @param mixed $value
     * @param mixed $context
     * @param bool  $expected
     *
     * @dataProvider identicalTestWithContextProvider
     */
    public function testIdenticalWithContext($token, bool $strict, $value, $context, bool $expected)
    {
        $identical = new Identical($token, $strict);
        $this->assertEquals($expected, $identical->isValid($value, $context));
    }

    public function identicalTestWithoutContextProvider(): array
    {
        // token strict value expected
        return [
            // string
            ['10', Identical::STRICT_IDENTITY_VERIFICATION, '10', true],
            // string ad int
            ['10', Identical::STRICT_IDENTITY_VERIFICATION, 10, false],
            ['10', Identical::NON_STRICT_IDENTITY_VERIFICATION, 10, true],
            // string ad float
            ['10.0', Identical::STRICT_IDENTITY_VERIFICATION, 10.0, false],
            ['10.0', Identical::NON_STRICT_IDENTITY_VERIFICATION, 10.0, true],
            ['10.0', Identical::NON_STRICT_IDENTITY_VERIFICATION, 10, true],
            ['10', Identical::NON_STRICT_IDENTITY_VERIFICATION, 10.0, true],
            // int and float
            [10.0, Identical::STRICT_IDENTITY_VERIFICATION, 10.0, true],
            [10.0, Identical::STRICT_IDENTITY_VERIFICATION, 10, false],
            [10.0, Identical::NON_STRICT_IDENTITY_VERIFICATION, 10, true],
            // array
            [[], Identical::STRICT_IDENTITY_VERIFICATION, [], true],
            [['a'], Identical::STRICT_IDENTITY_VERIFICATION, [], false],
            // object
            [new stdClass(), Identical::STRICT_IDENTITY_VERIFICATION, new stdClass(), false],
            [new stdClass(), Identical::NON_STRICT_IDENTITY_VERIFICATION, new stdClass(), true],
            // token is null
            [null, Identical::STRICT_IDENTITY_VERIFICATION, 10, false],
            [null, Identical::STRICT_IDENTITY_VERIFICATION, null, false],
        ];
    }

    public function identicalTestWithContextProvider(): array
    {
        $contextArray       = ['name' => ['first' => 'John'], 'email' => 'john@sample.com', 10 => 'number'];
        $contextTraversable = new Map($contextArray);

        // token strict value context expected
        return [
            // context array
            ['email', Identical::STRICT_IDENTITY_VERIFICATION, 'john@sample.com', $contextArray, true],
            ['email', Identical::STRICT_IDENTITY_VERIFICATION, 'mike@sample.com', $contextArray, false],
            ['name', Identical::STRICT_IDENTITY_VERIFICATION, ['first' => 'John'], $contextArray, true],
            ['name', Identical::STRICT_IDENTITY_VERIFICATION, ['first' => 'Mike'], $contextArray, false],
            ['name.first', Identical::STRICT_IDENTITY_VERIFICATION, 'John', $contextArray, true],
            ['name.first', Identical::STRICT_IDENTITY_VERIFICATION, 'Mike', $contextArray, false],
            [10, Identical::STRICT_IDENTITY_VERIFICATION, 'number', $contextArray, true],
            // context Traversable
            ['email', Identical::STRICT_IDENTITY_VERIFICATION, 'john@sample.com', $contextTraversable, true],
            ['email', Identical::STRICT_IDENTITY_VERIFICATION, 'mike@sample.com', $contextTraversable, false],
            ['name', Identical::STRICT_IDENTITY_VERIFICATION, ['first' => 'John'], $contextTraversable, true],
            ['name', Identical::STRICT_IDENTITY_VERIFICATION, ['first' => 'Mike'], $contextTraversable, false],
            ['name.first', Identical::STRICT_IDENTITY_VERIFICATION, 'John', $contextTraversable, true],
            ['name.first', Identical::STRICT_IDENTITY_VERIFICATION, 'Mike', $contextTraversable, false],
            [10, Identical::STRICT_IDENTITY_VERIFICATION, 'number', $contextTraversable, true],
            // context scalar without token
            [null, Identical::STRICT_IDENTITY_VERIFICATION, 'John', 'John', true],
            [null, Identical::STRICT_IDENTITY_VERIFICATION, 'John', 'Mike', false],
            // priority of the context over the token in the constructor
            ['John', Identical::STRICT_IDENTITY_VERIFICATION, 'John', 'Mike', false],
            ['Mike', Identical::STRICT_IDENTITY_VERIFICATION, 'John', 'John', true],
            // token is null
            [null, Identical::STRICT_IDENTITY_VERIFICATION, 10, '', false],
            [null, Identical::STRICT_IDENTITY_VERIFICATION, null, null, false],
            // context is zero
            [null, Identical::STRICT_IDENTITY_VERIFICATION, 0, 0, false],
            // token is zero and context is zero
            [0, Identical::STRICT_IDENTITY_VERIFICATION, 0, 0, true],
        ];
    }
}
