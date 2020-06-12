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
 *
 */

declare(strict_types=1);

namespace oat\tao\model\listener;

use core_kernel_classes_Property;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\PropertiesChangedEvent;
use oat\tao\model\search\tasks\RenameIndexProperties;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use RuntimeException;

/**
 * Class ResourceWatcher
 * @package oat\tao\model\resources
 * @author Aleksej Tikhanovich, <aleksej@taotesting.com>
 */
class PropertiesChangedListener extends ConfigurableService
{
    const SERVICE_ID = 'tao/PropertiesChangedListener';

    public function catchPropertiesChangedEvent(PropertiesChangedEvent $event)
    {
        $taskMessage = __('Updating Indexes');

        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        $queueDispatcher->createTask(
            new RenameIndexProperties(),
            array_map(
                function (array $property) {
                    if (!isset($property['oldProperty'], $property['property'])) {
                        throw new RuntimeException('property data are not set');
                    }

                    /** @var core_kernel_classes_Property $oldPropertyType */
                    $oldPropertyType = $property['oldProperty']->getPropertyType();
                    return [
                        'uri' => $property['property']->getUri(),
                        'oldLabel' => $property['oldProperty']->getLabel(),
                        'oldPropertyType' => $oldPropertyType === null ? null : $oldPropertyType->getUri(),
                    ];
                },
                $event->getProperties()
            ),
            $taskMessage
        );
    }
}
