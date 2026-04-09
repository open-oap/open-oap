<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Upgrades;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('openOapPluginCTypeMigration')]
final class PluginCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'openoap_applicant' => 'openoap_applicant',
            'openoap_applicantform' => 'openoap_applicantform',
            'openoap_dashboard' => 'openoap_dashboard',
            'openoap_form' => 'openoap_form',
            'openoap_notifications' => 'openoap_notifications',
            'openoap_proposals' => 'openoap_proposals',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrate "OpenOAP" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "OpenOAP" plugin is now registered as content element. Update migrates existing records and backend user permissions.';
    }
}
