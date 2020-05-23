<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\Utility\Interfaces\VariableType;

use function boolval;
use function filter_var;
use function is_string;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_FLAG_NO_RES_RANGE;

/**
 * Class Ip
 *
 * Validates value as IP address, optionally only IPv4 or IPv6 or not from private or reserved ranges.
 *
 * @package Estasi\Validator
 */
final class Ip extends Abstracts\Validator
{
    // names of constructor parameters to create via the factory
    public const OPT_IP_VERSION       = 'IPVersion';
    public const OPT_FORBIDDEN_RANGES = 'forbiddenRanges';
    // default values for constructor parameters
    public const ONLY_IP_VERSION_4 = FILTER_FLAG_IPV4;
    public const ONLY_IP_VERSION_6 = FILTER_FLAG_IPV6;
    public const ANY_IP_VERSION    = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
    /** @var int any ip range is allowed */
    public const NO_FORBIDDEN_RANGES = 0;
    /**
     * Fails validation for the following private IPv4 ranges: 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16
     * Fails validation for the IPv6 addresses starting with FD or FC
     */
    public const NO_PRIVATE_RANGE = FILTER_FLAG_NO_PRIV_RANGE;
    /**
     * Fails validation for the following reserved IPv4 ranges: 0.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8 and 240.0.0.0/4
     * Fails validation for the following reserved IPv6 ranges: ::1/128, ::/128, ::ffff:0:0/96 and fe80::/10
     */
    public const NO_RESERVED_RANGE = FILTER_FLAG_NO_RES_RANGE;
    /**
     * Fails validation for the following private IPv4 ranges: 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16
     * Fails validation for the IPv6 addresses starting with FD or FC
     * Fails validation for the following reserved IPv4 ranges: 0.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8 and 240.0.0.0/4
     * Fails validation for the following reserved IPv6 ranges: ::1/128, ::/128, ::ffff:0:0/96 and fe80::/10
     */
    public const NO_RESERVED_AND_PRIVATE_RANGES = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
    // errors codes
    public const E_INVALID_IP = 'eInvalidIp';

    private int $filterOptions;

    /**
     * Ip constructor.
     *
     * @param int           $IPVersion       Allows the IP address to be in IPv4 and/or IPv6 format
     * @param int           $forbiddenRanges Any, private or reserved ranges
     * @param iterable|null $options         Secondary validator options, such as the Translator, the length of the
     *                                       error message, hiding the value being checked, defining your own error
     *                                       messages, and so on
     */
    public function __construct(
        int $IPVersion = self::ANY_IP_VERSION,
        int $forbiddenRanges = self::NO_FORBIDDEN_RANGES,
        iterable $options = null
    ) {
        $this->filterOptions = $IPVersion | $forbiddenRanges;
        parent::__construct(...$this->createProperties($options));
        $this->initErrorMessagesTemplates([self::E_INVALID_IP => 'IP address "%value%" is not correct!']);
        $this->initErrorMessagesVars([self::MESSAGE_VAR_TYPES_EXPECTED => VariableType::STRING]);
    }

    /**
     * @param string     $value
     * @param mixed|null $context
     *
     * @return bool
     */
    public function isValid($value, $context = null): bool
    {
        if (false === is_string($value)) {
            $this->error(self::E_INVALID_TYPE);

            return false;
        }
        if (boolval(filter_var($value, FILTER_VALIDATE_IP, $this->filterOptions))) {
            return true;
        }
        $this->error(self::E_INVALID_IP, [self::MESSAGE_VAR_VALUE => $value]);

        return false;
    }
}
