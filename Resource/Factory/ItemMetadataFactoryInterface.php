<?php

/*
 * This file is part of the API Platform Builder package.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiPlatform\Core\Metadata\Resource\Factory;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\ItemMetadata;

/**
 * Creates an item metadata value object.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface ItemMetadataFactoryInterface
{
    /**
     * Creates a resource item metadata.
     *
     * @param string $resourceClass
     *
     * @return ItemMetadata
     *
     * @throws ResourceClassNotFoundException
     */
    public function create(string $resourceClass) : ItemMetadata;
}
