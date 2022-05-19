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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\featureFlag\Repository;

use core_kernel_classes_Triple;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\TaoOntology;

class FeatureFlagRepository implements FeatureFlagRepositoryInterface
{
    private const ONTOLOGY_SUBJECT = 'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags';
    private const ONTOLOGY_PREDICATE = 'http://www.tao.lu/Ontologies/TAO.rdf#featureFlags';
    private const FEATURE_FLAG_PREFIX = 'FEATURE_FLAG_';

    /** @var Ontology */
    private $ontology;

    /** @var SimpleCache */
    private $cache;

    /** @var array */
    private $storageOverride;

    public function __construct(Ontology $ontology, SimpleCache $cache, array $storageOverride = null)
    {
        $this->ontology = $ontology;
        $this->cache = $cache;
        $this->storageOverride = $storageOverride ?? $_ENV;
    }

    public function get(string $featureFlagName): bool
    {
        $featureFlagName = $this->getPersistenceName($featureFlagName);

        if ($this->cache->has($featureFlagName)) {
            return $this->filterVar($this->cache->get($featureFlagName));
        }

        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);
        $value = (string)$resource->getOnePropertyValue($this->ontology->getProperty($featureFlagName));
        $value = $this->filterVar($value);

        $this->cache->set($featureFlagName, $value);

        return $value;
    }

    public function list(): array
    {
        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);
        $output = [];

        /** @var core_kernel_classes_Triple $triple */
        foreach ($resource->getRdfTriples() as $triple) {
            $featureFlagName = str_replace(self::ONTOLOGY_PREDICATE . '_', '', $triple->predicate);

            if ($triple->predicate === TaoOntology::PROPERTY_UPDATED_AT) {
                continue;
            }

            $output[$featureFlagName] = $this->get($featureFlagName);
        }

        foreach ($this->storageOverride as $key => $value) {
            if (strpos($key, self::FEATURE_FLAG_PREFIX) === 0) {
                $output[$key] = $this->filterVar($this->storageOverride[$key]);
            }
        }

        return $output;
    }

    public function save(string $featureFlagName, bool $value): void
    {
        if (strpos($featureFlagName, self::FEATURE_FLAG_PREFIX) !== 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'FeatureFlag name needs to start with "%s"',
                    self::FEATURE_FLAG_PREFIX
                )
            );
        }

        $featureFlagName = $this->getPersistenceName($featureFlagName);

        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);
        $resource->editPropertyValues($this->ontology->getProperty($featureFlagName), var_export($value, true));

        if ($this->cache->has($featureFlagName)) {
            $this->cache->delete($featureFlagName);
        }
    }

    public function clearCache(): int
    {
        $resource = $this->ontology->getResource(self::ONTOLOGY_SUBJECT);

        $count = 0;

        /** @var core_kernel_classes_Triple $triple */
        foreach ($resource->getRdfTriples() as $triple) {
            if ($triple->predicate === TaoOntology::PROPERTY_UPDATED_AT) {
                continue;
            }

            if ($this->cache->has($triple->predicate)) {
                $this->cache->delete($triple->predicate);
                $count++;
            }
        }

        return $count;
    }

    private function getPersistenceName(string $featureFlagName): string
    {
        return self::ONTOLOGY_PREDICATE . '_' . $featureFlagName;
    }

    private function filterVar($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ?? false;
    }
}
