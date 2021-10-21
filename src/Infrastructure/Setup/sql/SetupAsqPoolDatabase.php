<?php
declare(strict_types=1);

namespace srag\asq\QuestionPool\Infrastructure\Setup\sql;

use srag\asq\QuestionPool\Domain\Model\QuestionPoolListItem;
use srag\asq\QuestionPool\Domain\Persistence\QuestionPoolEventStoreAr;

/**
 * Class SetupAsqTestDatabase
 *
 * @package Fluxlabs\Assessment\Test
 *
 * @author Fluxlabs AG - Adrian Lüthi <adi@fluxlabs.ch>
 */
class SetupAsqPoolDatabase
{
    public static function run() : void
    {
        QuestionPoolEventStoreAr::updateDB();
        QuestionPoolListItem::updateDB();
    }

    public static function uninstall() : void
    {
        global $DIC;

        $DIC->database()->dropTable(QuestionPoolEventStoreAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionPoolListItem::STORAGE_NAME, false);
    }
}
