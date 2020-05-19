<?php

declare(strict_types=1);

namespace Estasi\Validator;

use Estasi\PluginManager\Abstracts;

/**
 * Class PluginManager
 *
 * @package Estasi\Validator
 */
final class PluginManager extends Abstracts\PluginManager implements Interfaces\PluginManager
{
    use Traits\PluginManager;

    /**
     * @inheritDoc
     */
    public function getValidator(string $name, iterable $options = null): Interfaces\Validator
    {
        /** @var \Estasi\Validator\Interfaces\Validator $validator */
        $validator = $this->build($name, $options);

        return $validator;
    }
}
