<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020-2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\event;

use oat\oatbox\event\Event;

class DataAccessControlChangedEvent implements Event
{
    /** @var string */
    private $resourceId;

    /** @var ?string */
    private $rootResourceId;

    /** @var array */
    private $addRemove;

    /**
     * @deprecated Use $applyToNestedResources cause processing recursively causes performance issues
     * @var bool
     */
    private $isRecursive;

    private bool $applyToNestedResources;

    public function __construct(
        string $resourceId,
        array $addRemove,
        bool $isRecursive = false,
        bool $applyToNestedResources = false,
        string $rootResourceId = null
    ) {
        $this->resourceId = $resourceId;
        $this->addRemove = $addRemove;
        $this->isRecursive = $isRecursive;
        $this->applyToNestedResources = $applyToNestedResources;
        $this->rootResourceId = $rootResourceId;
    }

    public function getName(): string
    {
        return static::class;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getRootResourceId(): ?string
    {
        return $this->rootResourceId;
    }

    public function getOperations(string $operation): array
    {
        return array_keys($this->addRemove[$operation] ?? []);
    }

    /**
     * @deprecated Use applyToNestedResources because processing recursively causes performance issues
     */
    public function isRecursive(): bool
    {
        return $this->isRecursive;
    }

    public function applyToNestedResources(): bool
    {
        return $this->applyToNestedResources;
    }
}
