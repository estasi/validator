<?php

declare(strict_types=1);

namespace EstasiTest\Validator;

use Estasi\Validator\Ip;
use PHPUnit\Framework\TestCase;

class IpTest extends TestCase
{
    public function testIp()
    {
        $this->assertTrue((new Ip())->isValid('192.168.1.1'));
    }
}
