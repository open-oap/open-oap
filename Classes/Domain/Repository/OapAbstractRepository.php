<?php

declare(strict_types=1);

namespace OpenOAP\OpenOap\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
* This file is part of the "Open Application Plattform" Extension for TYPO3 CMS.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* (c) 2021 Thorsten Born <thorsten.born@cosmoblonde.de>, cosmoblonde gmbh
*/

/**
* Place for common functions
*/
class OapAbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    private Typo3DbQueryParser $queryParser;

    /**
     * @param Typo3DbQueryParser $queryParser
     */
    public function injectService(Typo3DbQueryParser $queryParser)
    {
        $this->queryParser = $queryParser;
    }

    /**
     * @param QueryInterface $query
     */
    protected function sqlDebug(QueryInterface $query): void
    {
        DebuggerUtility::var_dump($this->queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL());
        DebuggerUtility::var_dump($this->queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters());
    }
}
