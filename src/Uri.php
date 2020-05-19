<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\Utility\{
    Interfaces\Uri as UriHandler,
    Interfaces\VariableType,
    Traits\Flags,
    Traits\Uri as UriTrait,
    UriFactory
};

use function is_null;
use function is_string;
use function preg_replace;
use function sprintf;

/**
 * Class Uri
 *
 * @package Estasi\Validator
 */
final class Uri extends Abstracts\Validator
{
    use Flags;
    use UriTrait {
        isValidScheme as private isValidSchemeTrait;
    }

    // names of constructor parameters to create via the factory
    public const OPT_URI_HANDLER = 'uriHandler';
    public const OPT_FLAGS       = 'flags';
    // default values for constructor parameters
    public const DEFAULT_URI_HANDLER = UriFactory::DEFAULT_URI_HANDLER;
    // values for constructor parameters
    public const FLAG_SCHEME_REQUIRED    = 0b0000000001;
    public const FLAG_HOST_REQUIRED      = 0b0000000010;
    public const FLAG_PATH_REQUIRED      = 0b0000000100;
    public const FLAG_QUERY_REQUIRED     = 0b0000001000;
    public const FLAG_FRAGMENT_REQUIRED  = 0b0000010000;
    public const FLAG_ABSOLUTE_URI       = 0b0000100000;
    public const FLAG_NETWORK_PATH       = 0b0001000000;
    public const FLAG_ABSOLUTE_PATH      = 0b0010000000;
    public const FLAG_RELATIVE_PATH      = 0b0100000000;
    public const FLAG_RELATIVE_REFERENCE = 0b1000000000;
    // errors codes
    public const E_INVALID_URI                = 'eInvalidUri';
    public const E_EMPTY_REQUIRED_SCHEME      = 'eEmptyRequiredScheme';
    public const E_EMPTY_REQUIRED_HOST        = 'eEmptyRequiredHost';
    public const E_EMPTY_REQUIRED_PATH        = 'eEmptyRequiredPath';
    public const E_EMPTY_REQUIRED_QUERY       = 'eEmptyRequiredQuery';
    public const E_EMPTY_REQUIRED_FRAGMENT    = 'eEmptyRequiredFragment';
    public const E_INVALID_SCHEME             = 'eInvalidScheme';
    public const E_INVALID_HOST               = 'eInvalidHost';
    public const E_INVALID_PORT               = 'eInvalidPort';
    public const E_INVALID_PATH               = 'eInvalidPath';
    public const E_INVALID_QUERY              = 'eInvalidQuery';
    public const E_INVALID_FRAGMENT           = 'eInvalidFragment';
    public const E_URI_NOT_ABSOLUTE           = 'eUriNotAbsolute';
    public const E_URI_NOT_NETWORK_PATH       = 'eUriNotNetworkPath';
    public const E_URI_NOT_ABSOLUTE_PATH      = 'eUriNotAbsolutePath';
    public const E_URI_NOT_RELATIVE_PATH      = 'eUriNotRelativePath';
    public const E_URI_NOT_RELATIVE_REFERENCE = 'eUriNotRelativeReference';

    private UriHandler $uriHandler;

    /**
     * Uri constructor.
     *
     * @param string|\Estasi\Utility\Interfaces\Uri $uriHandler
     * @param int                                   $flags
     * @param iterable<string, mixed>|null          $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($uriHandler = self::DEFAULT_URI_HANDLER, int $flags = 0, iterable $options = null)
    {
        $this->uriHandler = UriFactory::make(UriFactory::WITHOUT_URI, $uriHandler);
        $this->setFlags($flags);
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates(
            [
                self::E_INVALID_URI                => 'The checked uri "%value%" is not a valid uri with the specified verification settings!',
                self::E_INVALID_SCHEME             => 'The checked uri "%value%" contains an invalid schema!',
                self::E_INVALID_HOST               => 'The checked uri "%value%" contains an invalid host!',
                self::E_INVALID_PORT               => 'The checked uri "%value%" contains an invalid port!',
                self::E_INVALID_PATH               => 'The checked uri "%value%" contains an invalid path!',
                self::E_INVALID_QUERY              => 'The checked uri "%value%" contains an invalid query!',
                self::E_INVALID_FRAGMENT           => 'The checked uri "%value%" contains an invalid fragment!',
                self::E_EMPTY_REQUIRED_SCHEME      => 'The uri "%value%" being checked lacks a required scheme!',
                self::E_EMPTY_REQUIRED_HOST        => 'The uri "%value%" being checked lacks a required host!',
                self::E_EMPTY_REQUIRED_PATH        => 'The uri "%value%" being checked lacks a required path!',
                self::E_EMPTY_REQUIRED_QUERY       => 'The uri "%value%" being checked lacks a required query!',
                self::E_EMPTY_REQUIRED_FRAGMENT    => 'The uri "%value%" being checked lacks a required fragment!',
                self::E_URI_NOT_ABSOLUTE           => 'The checked uri "%value%" is not an absolute reference!',
                self::E_URI_NOT_NETWORK_PATH       => 'The uri "%value%" being checked is not a network path reference!',
                self::E_URI_NOT_ABSOLUTE_PATH      => 'The checked uri "%value%" is not an absolute path reference!',
                self::E_URI_NOT_RELATIVE_PATH      => 'The uri "%value%" being checked is not a relative path reference!',
                self::E_URI_NOT_RELATIVE_REFERENCE => 'The checked uri "%value%" is not a relative reference!',
            ]
        );
        $this->initErrorMessagesVars(
            [self::MESSAGE_VAR_TYPES_EXPECTED => [VariableType::STRING, UriHandler::class]]
        );
    }

    /**
     * @param string|\Estasi\Utility\Interfaces\Uri $value
     * @param mixed|null                            $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        if (false === (is_string($value) || $value instanceof UriHandler)) {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }
        $this->setValue($value);

        $uri = $this->uriHandler->merge($value);
        if ($this->isValidParts($uri)) {
            return $this->isValidTypeUri($uri);
        }

        $this->error(self::E_INVALID_URI);

        return false;
    }

    private function isValidParts(UriHandler $uri): bool
    {
        return $this->isValidScheme($uri->scheme)
               && $this->isValidUserInfo($uri->userinfo)
               && $this->isValidHost($uri->host)
               && $this->isValidPort($uri->port)
               && $this->isValidPath($uri->path)
               && $this->isValidQuery($uri->query)
               && $this->isValidFragment($uri->fragment);
    }

    private function isValidScheme(?string $scheme): bool
    {
        if (is_null($scheme)) {
            return $this->isOptionalPartUri(self::FLAG_SCHEME_REQUIRED, self::E_EMPTY_REQUIRED_SCHEME);
        }
        if ($this->isValidSchemeTrait($scheme)) {
            return true;
        }
        $this->error(self::E_INVALID_SCHEME);

        return false;
    }

    private function isValidUserInfo(?string $userinfo): bool
    {
        if (is_null($userinfo)) {
            return true;
        }
        $patternUserinfo = sprintf(
            '`[%s%s\x3A]|%s`u',
            UriHandler::UNRESERVED_RFC3986,
            UriHandler::SUB_DELIMS_RFC3986,
            UriHandler::PCT_ENCODED_RFC3986
        );

        return empty(preg_replace($patternUserinfo, '', $userinfo));
    }

    private function isValidHost(?string $host): bool
    {
        if (is_null($host)) {
            return $this->isOptionalPartUri(self::FLAG_HOST_REQUIRED, self::E_EMPTY_REQUIRED_HOST);
        }
        $this->isHostIPLiteral($host);
        if ($this->isIPvFuture($host)
            || $this->isIPv6Address($host)
            || $this->isHostIPv4Address($host)
            || $this->isHostRegName($host)) {
            return true;
        }
        $this->error(self::E_INVALID_HOST);

        return false;
    }

    private function isValidPort(?string $port): bool
    {
        if (is_null($port)) {
            return true;
        }
        if (false === $this->isPortOutsideOfTheAllowedRange((int)$port)) {
            return true;
        }
        $this->error(self::E_INVALID_PORT);

        return false;
    }

    private function isValidPath(?string $path): bool
    {
        if (is_null($path)) {
            return $this->isOptionalPartUri(self::FLAG_PATH_REQUIRED, self::E_EMPTY_REQUIRED_PATH);
        }
        $pathPattern = sprintf(
            '`[%s%s\x3A\x40\x2F]|%s`uS',
            UriHandler::UNRESERVED_RFC3986,
            UriHandler::SUB_DELIMS_RFC3986,
            UriHandler::PCT_ENCODED_RFC3986
        );
        if (empty(preg_replace($pathPattern, '', $path))) {
            return true;
        }
        $this->error(self::E_INVALID_PATH);

        return false;
    }

    private function isValidQuery(?string $query): bool
    {
        if (is_null($query)) {
            return $this->isOptionalPartUri(self::FLAG_QUERY_REQUIRED, self::E_EMPTY_REQUIRED_QUERY);
        }
        if (empty(preg_replace($this->getPatternForQueryAndFragment(), '', $query))) {
            return true;
        }
        $this->error(self::E_INVALID_QUERY);

        return false;
    }

    private function isValidFragment(?string $fragment): bool
    {
        if (is_null($fragment)) {
            return $this->isOptionalPartUri(self::FLAG_FRAGMENT_REQUIRED, self::E_EMPTY_REQUIRED_FRAGMENT);
        }
        if (empty(preg_replace($this->getPatternForQueryAndFragment(), '', $fragment))) {
            return true;
        }
        $this->error(self::E_INVALID_FRAGMENT);

        return false;
    }

    private function getPatternForQueryAndFragment(): string
    {
        return sprintf(
            '`[[%s%s\x3A\x40\x2F\x3F]|%s`uS',
            UriHandler::UNRESERVED_RFC3986,
            UriHandler::SUB_DELIMS_RFC3986,
            UriHandler::PCT_ENCODED_RFC3986
        );
    }

    private function isOptionalPartUri(int $flag, string $errorCode): bool
    {
        if ($this->is($flag)) {
            $this->error($errorCode);

            return false;
        }

        return true;
    }

    private function isValidTypeUri(UriHandler $uri): bool
    {
        if ($this->is(self::FLAG_ABSOLUTE_URI)) {
            if ($uri->isAbsoluteUri()) {
                return true;
            }
            $this->error(self::E_URI_NOT_ABSOLUTE);

            return false;
        } elseif ($this->is(self::FLAG_NETWORK_PATH)) {
            if ($uri->isNetworkPath()) {
                return true;
            }
            $this->error(self::E_URI_NOT_NETWORK_PATH);

            return false;
        } elseif ($this->is(self::FLAG_ABSOLUTE_PATH)) {
            if ($uri->isAbsolutePath()) {
                return true;
            }
            $this->error(self::E_URI_NOT_ABSOLUTE_PATH);

            return false;
        } elseif ($this->is(self::FLAG_RELATIVE_PATH)) {
            if ($uri->isRelativePath()) {
                return true;
            }
            $this->error(self::E_URI_NOT_RELATIVE_PATH);

            return false;
        } elseif ($this->is(self::FLAG_RELATIVE_REFERENCE)) {
            if ($uri->isRelativeReference()) {
                return true;
            }
            $this->error(self::E_URI_NOT_RELATIVE_REFERENCE);

            return false;
        }

        return true;
    }
}
