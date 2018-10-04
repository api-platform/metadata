<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatform\Core\Metadata\Extractor;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

/**
 * Base file extractor.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
abstract class AbstractExtractor implements ExtractorInterface
{
    private $container;
    protected $paths;
    protected $resources;

    /**
     * @param string[] $paths
     */
    public function __construct(array $paths, ContainerInterface $container = null)
    {
        $this->paths = $paths;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources(): array
    {
        if (null !== $this->resources) {
            return $this->resources;
        }

        $this->resources = [];
        foreach ($this->paths as $path) {
            $this->extractPath($path);
        }

        return $this->resources;
    }

    /**
     * Extracts metadata from a given path.
     */
    abstract protected function extractPath(string $path);

    /**
     * Recursively replaces placeholders with the service container parameters.
     *
     * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Routing/Router.php
     * @copyright (c) Fabien Potencier <fabien@symfony.com>
     *
     * @param mixed $value The source which might contain "%placeholders%"
     *
     * @return mixed The source with the placeholders replaced by the container
     *               parameters. Arrays are resolved recursively.
     *
     * @throws ParameterNotFoundException When a placeholder does not exist as a container parameter
     * @throws RuntimeException           When a container value is not a string or a numeric value
     */
    protected function resolve($value)
    {
        if (null === $this->container) {
            return $value;
        }

        if (\is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolve($val);
            }

            return $value;
        }

        if (!\is_string($value)) {
            return $value;
        }

        $escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            if (preg_match('/^env\(\w+\)$/', $match[1])) {
                throw new RuntimeException(sprintf('Using "%%%s%%" is not allowed in routing configuration.', $match[1]));
            }

            if ($this->container instanceof SymfonyContainerInterface) {
                $resolved = $this->container->getParameter($match[1]);
            } else {
                $resolved = $this->container->get($match[1]);
            }

            if (\is_string($resolved) || is_numeric($resolved)) {
                $this->collectedParameters[$match[1]] = $resolved;

                return (string) $resolved;
            }

            throw new RuntimeException(sprintf('The container parameter "%s", used in the resource configuration value "%s", must be a string or numeric, but it is of type %s.', $match[1], $value, \gettype($resolved)));
        }, $value);

        return str_replace('%%', '%', $escapedValue);
    }
}
