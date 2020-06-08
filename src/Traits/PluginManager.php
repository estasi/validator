<?php

declare(strict_types=1);

namespace Estasi\Validator\Traits;

use Estasi\PluginManager\{
    Interfaces,
    Plugin,
    PluginsList
};
use Estasi\Validator\{
    Between,
    Boolval,
    Callback,
    Date,
    DateStep,
    Each,
    Email,
    GreaterThan,
    Identical,
    InList,
    Interfaces\Validator,
    Ip,
    IsCountable,
    LessThan,
    Regex,
    Step,
    StringLength,
    Uri,
    UUID
};

/**
 * Trait PluginManager
 *
 * @package Estasi\Validator\Traits
 */
trait PluginManager
{
    /**
     * @inheritDoc
     */
    public function getInstanceOf(): ?string
    {
        return Validator::class;
    }

    /**
     * @inheritDoc
     */
    public function getPlugins(): Interfaces\PluginsList
    {
        return new PluginsList(
            new Plugin(Between::class, ['between', 'Between', 'range', 'Range']),
            new Plugin(
                Boolval::class,
                [
                    'boolval',
                    'Boolval',
                    'not_empty',
                    'notEmpty',
                    'NotEmpty',
                    'not_blank',
                    'notBlank',
                    'NotBlank',
                    'isset',
                ]
            ),
            new Plugin(Callback::class, ['callback', 'callable', 'Callback', 'Callable']),
            new Plugin(Date::class, ['date', 'Date']),
            new Plugin(DateStep::class, ['date_step', 'dateStep', 'DateStep']),
            new Plugin(Email::class, ['email', 'Email']),
            new Plugin(GreaterThan::class, ['greater_than', 'greaterThen', 'GreaterThen']),
            new Plugin(Ip::class, ['ip', 'Ip', 'IP', 'IPAddress']),
            new Plugin(IsCountable::class, ['is_countable', 'isCountable', 'IsCountable']),
            new Plugin(LessThan::class, ['less_than', 'lessThan', 'LessThan']),
            new Plugin(Regex::class, ['regex', 'Regex', 'preg_match', 'pregMatch', 'PregMatch']),
            new Plugin(Step::class, ['step', 'Step']),
            new Plugin(StringLength::class, ['string_length', 'stringLength', 'StringLength', 'strlen']),
            new Plugin(Identical::class, ['identical', 'Identical', 'eq', 'equivalent', 'Equivalent']),
            new Plugin(InList::class, ['in_list', 'inList', 'InList', 'in_array', 'inArray', 'InArray']),
            new Plugin(Uri::class, ['uri', 'url', 'URI', 'URL']),
            new Plugin(UUID::class, ['uuid', 'UUID']),
            new Plugin(Each::class, ['each', 'Each'])
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidator(string $name, iterable $options = null): Validator
    {
        /** @var \Estasi\Validator\Interfaces\Validator $validator */
        $validator = $this->build($name, $options);

        return $validator;
    }
}
