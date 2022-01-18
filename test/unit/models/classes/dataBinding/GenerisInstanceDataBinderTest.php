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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\action;

use common_Object;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use oat\generis\test\MockObject;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\TestCase;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\event\MetadataModified;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;

class GenerisInstanceDataBinderTest extends TestCase
{
    use OntologyMockTrait;

    const URI_CLASS_TYPE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
    const URI_PROPERTY_1 = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#p1';
    const URI_PROPERTY_2 = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#p2';
    const URI_TYPE_1 = 'http://test.com/Type1';
    const URI_TYPE_2 = 'http://test.com/Type2';

    /** @var tao_models_classes_dataBinding_GenerisInstanceDataBinder */
    private $sut;

    /** @var core_kernel_classes_Resource|MockObject */
    private $resource;

    /** @var core_kernel_classes_Resource|EventManager */
    private $eventManagerMock;

    /** @var core_kernel_classes_Resource|ServiceManager */
    private $serviceManagerMock;

    public function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(EventManager::class);
        $this->serviceManagerMock = $this->createMock(ServiceManager::class);
        $services = [
            EventManager::SERVICE_ID => $this->eventManagerMock,
        ];

        $this->serviceManagerMock
            ->method('get')
            ->willReturnCallback(function ($id) use ($services) {
                return $services[$id];
            });

        $this->resource = $this->createMock(
            core_kernel_classes_Resource::class
        );
        $this->resource->setModel($this->getOntologyMock());

        $this->sut =
            new tao_models_classes_dataBinding_GenerisInstanceDataBinder(
                $this->resource,
                $this->eventManagerMock
            );
    }

    public function testBindScalarWithPreviousValue(): void
    {
        $this->eventManagerMock
            ->expects(self::once())
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == 'Value 1'));
            }));

        $this->resource
            ->expects(self::once())
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1);
            }))
            ->willReturn(true);

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1)
            ]);

        // There is a previous value for prop1 and its new value is a scalar:
        // The data binder should call editPropertyValues() on the resource.
        //
        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $p) {
                    if ($p->getUri() == self::URI_PROPERTY_1) {
                        $ret = new core_kernel_classes_ContainerCollection(
                            new common_Object()
                        );
                        $ret->add(
                            new core_kernel_classes_Resource(
                                'http://a.resource/'
                            )
                        );

                        return $ret;
                    }
                }
            ));

        $this->resource
            ->expects($this->once())
            ->method('editPropertyValues')
            ->with($this->callback(
                function (\core_kernel_classes_Property $property, $_ = null) {
                    return ($property->getUri() == self::URI_PROPERTY_1);
                }
            ));

        // Binding a single class type and a single, non-empty value for
        // URI_PROPERTY_1 which should trigger editPropertyValues().
        //
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => self::URI_TYPE_1,
            self::URI_PROPERTY_1 => 'Value 1'
        ]);

        $this->assertClassesMatch([self::URI_TYPE_1], $resource);
    }

    public function testBindEmptyValue(): void
    {
        $this->eventManagerMock
            ->expects(self::at(0))
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_1)
                    && ($e->getMetadataValue() == ' '));
            }));

        $this->eventManagerMock
            ->expects(self::at(1))
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $e): bool {
                return (
                    $e->getResource()->getLabel() == $this->resource->getUri()
                    && ($e->getMetadataUri() == self::URI_PROPERTY_2)
                    && ($e->getMetadataValue() == 'Value 2'));
            }));

        $this->resource
            ->expects(self::exactly(2))
            ->method('setType')
            ->with($this->callback(function (core_kernel_classes_Class $c) {
                return ($c->getUri() == self::URI_TYPE_1)
                    || ($c->getUri() == self::URI_TYPE_2);
            }))
            ->willReturn(true);

        $this->resource
            ->method('getTypes')
            ->willReturn([
                new core_kernel_classes_Class(self::URI_TYPE_1),
                new core_kernel_classes_Class(self::URI_TYPE_2)
            ]);

        // There is a previous value for prop1 and its new value is empty:
        // The data binder will call removePropertyValues() for the property.
        //
        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (core_kernel_classes_Property $property) {
                    if ($property->getUri() == self::URI_PROPERTY_1) {
                        $c = new core_kernel_classes_ContainerCollection(
                            new common_Object()
                        );
                        $c->add(new core_kernel_classes_Resource(
                            'http://a.resource/1'
                        ));

                        return $c;
                    }

                    if ($property->getUri() == self::URI_PROPERTY_2) {
                        $c = new core_kernel_classes_ContainerCollection(
                            new common_Object()
                        );
                        $c->add(new core_kernel_classes_Resource(
                            'http://a.resource/2'
                        ));
                        return $c;
                    }

                    $this->fail(
                        "Unexpected property: {$property->getUri()}"
                    );
                }
            ));

        $this->resource
            ->expects($this->exactly(1))
            ->method('editPropertyValues')
            ->willReturnCallback(
                function (core_kernel_classes_Property $p, $v = null) {
                    $this->assertEquals('Value 2', $v);
                    $this->assertEquals(self::URI_PROPERTY_2, $p->getUri());
                }
            );

        $this->resource
            ->expects($this->exactly(1))
            ->method('removePropertyValues')
            ->willReturnCallback(
                function (core_kernel_classes_Property $p, $opts = []) {
                    $this->assertEquals(self::URI_PROPERTY_1, $p->getUri());
                }
            );

        // Binding multiple values for the class type, and an empty value for
        // URI_PROPERTY_1 that should trigger removePropertyValues().
        //
        $resource = $this->sut->bind([
            self::URI_CLASS_TYPE => [self::URI_TYPE_1, self::URI_TYPE_2],
            self::URI_PROPERTY_1 => ' ',
            self::URI_PROPERTY_2 => 'Value 2',
        ]);

        $this->assertClassesMatch(
            ['http://test.com/Type1', 'http://test.com/Type2'],
            $resource
        );
    }

    private function assertClassesMatch(
        array $expected,
        core_kernel_classes_Resource $resource
    ): void {
        $classes = array_map(
            function (core_kernel_classes_Class $class) {
                return $class->getUri();
            },
            $resource->getTypes()
        );

        // Guarantee a consistent class order in tests
        sort($classes);

        $this->assertEquals($expected, $classes);
    }
}
