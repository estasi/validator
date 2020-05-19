<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Countable;
use Estasi\Utility\Interfaces\VariableType;
use Traversable;

use function compact;
use function gettype;

/**
 * Class IsCountable
 *
 * @package Estasi\Validator
 */
final class IsCountable extends Abstracts\Validator
{
    // error code
    public const E_NOT_COUNTED = 'eNotCounted';

    /**
     * IsCountable constructor.
     *
     * @param iterable<string, mixed>|null $options Secondary validator options, such as the Translator, the length of
     *                                              the error message, hiding the value being checked, defining your
     *                                              own error messages, and so on.
     */
    public function __construct(iterable $options = null)
    {
        parent::__construct(...$this->getValidOptionsForParent($options));
        $this->initErrorMessagesTemplates(
            [self::E_NOT_COUNTED => 'In the "%dataType%" data type, the number of elements cannot be counted!']
        );
    }

    /**
     * @inheritDoc
     */
    public function isValid($value, $context = null): bool
    {
        $dataType = gettype($value);
        switch ($dataType) {
            case VariableType::STRING:
            case VariableType::INTEGER:
            case VariableType::DOUBLE:
            case VariableType::ARRAY:
                return true;
            /** @noinspection PhpMissingBreakStatementInspection */
            case VariableType::OBJECT:
                if ($value instanceof Countable || $value instanceof Traversable) {
                    return true;
                }
            // no break;
            default:
                $this->error(self::E_NOT_COUNTED, compact('dataType'));

                return false;
        }
    }
}
