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

namespace oat\tao\model\StatisticalMetadata\Import\Observer;

use JsonSerializable;
use SplObserver;
use SplSubject;

class Subject implements SplSubject, JsonSerializable
{
    /** @var SplObserver[] */
    private $observers;

    /** @var array */
    private $data;

    public function withData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function attach(SplObserver $observer)
    {
        $this->observers[get_class($observer)] = $observer;
    }

    public function detach(SplObserver $observer)
    {
        unset($this->observers[get_class($observer)]);
    }

    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
