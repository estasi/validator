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
}
