<?php
declare(strict_types=1);

namespace srag\asq\QuestionPool\Infrastructure\Setup\lang;

use srag\asq\Infrastructure\Setup\lang\SetupLanguages;

/**
 * Class SetupAsqTestLanguages
 *
 * @package Fluxlabs\Assessment\Test
 *
 * @author Fluxlabs AG - Adrian Lüthi <adi@fluxlabs.ch>
 */
class SetupAsqPoolLanguages extends SetupLanguages
{
    const ASQ_POOL_LANGUAGE_PREFIX = 'asqp';

    public function getLanguagePrefix() : string
    {
        return self::ASQ_POOL_LANGUAGE_PREFIX;
    }
}
