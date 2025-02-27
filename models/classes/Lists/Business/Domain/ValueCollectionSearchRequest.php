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
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

class ValueCollectionSearchRequest
{
    /** @var string|null */
    private $propertyUri;

    /** @var string|null */
    private $valueCollectionUri;

    /** @var string|null */
    private $subject;

    /** @var string[] */
    private $excluded = [];

    /** @var int|null */
    private $offset;

    /** @var int|null */
    private $limit;

    /** @var string[] */
    private $uris = [];

    /** @var string */
    private $dataLanguage;

    /** @var string */
    private $defaultLanguage;

    /** @var string[] */
    private $parentListValues;

    /** @var string[] */
    private $selectedValues = [];

    public function hasPropertyUri(): bool
    {
        return null !== $this->propertyUri;
    }

    public function getPropertyUri(): string
    {
        return $this->propertyUri;
    }

    public function setPropertyUri(string $propertyUri): self
    {
        $this->propertyUri = $propertyUri;

        return $this;
    }

    public function hasValueCollectionUri(): bool
    {
        return null !== $this->valueCollectionUri;
    }

    public function getValueCollectionUri(): string
    {
        return $this->valueCollectionUri;
    }

    public function setValueCollectionUri(string $valueCollectionUri): self
    {
        $this->valueCollectionUri = $valueCollectionUri;

        return $this;
    }

    public function hasUris(): bool
    {
        return !empty($this->uris);
    }

    public function setUris(string ...$uri): self
    {
        $this->uris = $uri;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getUris(): array
    {
        return $this->uris;
    }

    public function hasSubject(): bool
    {
        return null !== $this->subject;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = mb_strtolower($subject);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getExcluded(): array
    {
        return $this->excluded;
    }

    public function hasExcluded(): bool
    {
        return !empty($this->excluded);
    }

    public function addExcluded(string $excluded): self
    {
        $this->excluded[] = $excluded;

        return $this;
    }

    public function hasOffset(): bool
    {
        return $this->offset !== null && $this->offset >= 0;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function hasLimit(): bool
    {
        return null !== $this->limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function hasDataLanguage(): bool
    {
        return null !== $this->dataLanguage;
    }

    public function getDataLanguage(): ?string
    {
        return $this->dataLanguage;
    }

    public function setDataLanguage(string $dataLanguage): self
    {
        $this->dataLanguage = $dataLanguage;

        return $this;
    }

    public function getDefaultLanguage(): ?string
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(string $defaultLanguage): void
    {
        $this->defaultLanguage = $defaultLanguage;
    }

    public function setParentListValues(string ...$values): self
    {
        $this->parentListValues = $values;

        return $this;
    }

    public function hasParentListValues(): bool
    {
        return !empty($this->parentListValues);
    }

    public function getParentListValues(): array
    {
        return $this->parentListValues;
    }

    public function getSelectedValues(): array
    {
        return $this->selectedValues;
    }

    public function setSelectedValues(string ...$values): self
    {
        $this->selectedValues = $values;

        return $this;
    }
}
