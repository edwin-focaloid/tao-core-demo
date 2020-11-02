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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\resources\relation\service;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\resources\relation\FindAllQuery;
use oat\tao\model\resources\relation\ResourceRelationCollection;

class ResourceRelationServiceProxy extends ConfigurableService implements ResourceRelationServiceInterface
{
    public const SERVICE_ID = 'tao/ResourceRelationServiceProxy';
    public const OPTION_SERVICES = 'services';

    public function addService(string $type, string $serviceId): void
    {
        $services = (array)$this->getOption(self::OPTION_SERVICES, []);

        if (!isset($services[$type])) {
            $services[$type] = [];
        }

        if (!in_array($serviceId, $services[$type])) {
            $services[$type][] = $serviceId;
        }

        $this->setOption(self::OPTION_SERVICES, $services);
    }

    public function relations(FindAllQuery $query): ResourceRelationCollection
    {
        $relations = [];

        foreach ($this->getOption(self::OPTION_SERVICES, []) as $type => $services) {
            foreach ($services as $serviceId) {
                if ($query->getType() === $type) {
                    /** @var ResourceRelationServiceInterface $service */
                    $service = $this->getServiceLocator()->get($serviceId);

                    $relations = array_merge(
                        $relations,
                        $service->relations($query)->getIterator()->getArrayCopy()
                    );
                }
            }
        }

        return new ResourceRelationCollection(...$relations);
    }
}
