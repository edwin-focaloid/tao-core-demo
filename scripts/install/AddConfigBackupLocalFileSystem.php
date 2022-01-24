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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types = 1);

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\config\BackupConfigService;

class AddConfigBackupLocalFileSystem extends InstallAction
{
    public function __invoke($params)
    {
        /** @var FileSystemService $fileSystemManager */
        $fileSystemManager = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);

        if (!$fileSystemManager->hasDirectory(BackupConfigService::FILE_SYSTEM_ID)) {
            $fileSystemManager->createFileSystem(BackupConfigService::FILE_SYSTEM_ID);
            $this->registerService(FileSystemService::SERVICE_ID, $fileSystemManager);
        }
    }
}
