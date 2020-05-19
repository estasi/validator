<?php

declare(strict_types=1);

namespace Estasi\Validator\Interfaces;

/**
 * Interface PluginManager
 *
 * @package Estasi\Validator\Interfaces
 */
interface PluginManager extends \Estasi\PluginManager\Interfaces\PluginManager
{
    /**
     * Returns the object of the requested class by name
     *
     * When using this method, you can't store the created object in the cache
     * This method MUST always return a newly created object
     *
     * @param string                       $name
     * @param iterable<string, mixed>|null $options
     *
     * @return Validator
     * @throws \Estasi\PluginManager\Exception\NotFoundException
     * @throws \Estasi\PluginManager\Exception\ContainerException
     * @api
     */
    public function getValidator(string $name, iterable $options = null): Validator;
}
