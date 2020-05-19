<?php

declare(strict_types=1);

namespace EstasiTest\Validator;

use Estasi\Validator\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    /**
     * @param string $uri
     * @param bool   $expected
     *
     * @dataProvider uriWithoutFlagsProvider
     */
    public function testUriWithoutFlags(string $uri, bool $expected)
    {
        $uriValidator = new Uri();
        $this->assertEquals($expected, $uriValidator->isValid($uri));
    }

    /**
     * @param string $uri
     * @param int    $flags
     *
     * @dataProvider uriWithFlagsProvider
     */
    public function testUriWithFlags(string $uri, int $flags)
    {
        $uriValidator = new Uri(Uri::DEFAULT_URI_HANDLER, $flags);
        $this->assertFalse($uriValidator->isValid($uri));
    }

    public function uriWithoutFlagsProvider(): array
    {
        return [
            ['http://example.com/path?foo=bar#baz', true],
            ['//sample.com/path?foo=bar#baz', true],
            ['path?foo=bar#baz', true],
            ['?foo=bar#baz', true],
            ['#baz', true],
            ['http://192.168.1.0', true],
            ['//192.168.1.0', true],
            ['192.168.1.0', true],
            ['http://[2001:0db8:85a3:0000:0000:8a2e:0370:7334]', true],
            ['//[2001:0db8:85a3:0000:0000:8a2e:0370:7334]', true],
            ['//[2001:0db8:85a3::8a2e:0370:7334]', true],
            // returns false because the path contains invalid characters "[", "]"
            ['[2001:0db8:85a3:0000:0000:8a2e:0370:7334]', false],
            ['http://user:password@example.com', true],
            ['http://example.com:80', true],
            ['http://example.com:65536', false],
            // returns true, because the parser automatically removes ports eq 0
            ['http://example.com:0', true],
            ['http://example.com:-1', false],
            // international
            ['https://ru.wikipedia.org/wiki/Википедия', true],
            ['https://ru.wikipedia.org/wiki/%D0%92%D0%B8%D0%BA%D0%B8%D0%BF%D0%B5%D0%B4%D0%B8%D1%8F', true],
            // not normalized
            ['http://www.example.com/../a/b/../c/./d.html', true],
            // without authority
            ['http:/path?foo=bar#fragment', true],
            // without host
            ['http://user:password/path', false],
            ['mailto:john@doe.com', true],
        ];
    }

    public function uriWithFlagsProvider(): array
    {
        return [
            ['//example.com/path?foo=bar#fragment', Uri::FLAG_SCHEME_REQUIRED],
            ['/path?foo=bar#fragment', Uri::FLAG_HOST_REQUIRED],
            ['//example.com?foo=bar#fragment', Uri::FLAG_PATH_REQUIRED],
            ['//example.com/path#fragment', Uri::FLAG_QUERY_REQUIRED],
            ['//example.com/path?foo=bar', Uri::FLAG_FRAGMENT_REQUIRED],
            ['//example.com#fragment', Uri::FLAG_PATH_REQUIRED | Uri::FLAG_QUERY_REQUIRED],
            ['//example.com/path?foo=bar#fragment', Uri::FLAG_ABSOLUTE_URI],
            ['/path?foo=bar#fragment', Uri::FLAG_NETWORK_PATH],
            ['//example.com/path?foo=bar#fragment', Uri::FLAG_ABSOLUTE_PATH],
            ['/path?foo=bar#fragment', Uri::FLAG_RELATIVE_PATH],
        ];
    }
}
