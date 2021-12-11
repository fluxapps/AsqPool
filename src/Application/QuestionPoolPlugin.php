<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application;

use Fluxlabs\Assessment\Tools\DIC\LanguageTrait;
use Fluxlabs\Assessment\Tools\Domain\AbstractAsqPlugin;
use Fluxlabs\Assessment\Tools\Domain\ILIASReference;
use ILIAS\Data\UUID\Uuid;
use srag\asq\QuestionPool\Infrastructure\Setup\lang\SetupAsqPoolLanguages;
use srag\asq\QuestionPool\Module\QuestionService\ASQModule;
use srag\asq\QuestionPool\Module\Storage\QuestionPoolStorage;
use srag\asq\QuestionPool\Module\Taxonomy\TaxonomyModule;
use srag\asq\QuestionPool\Module\UI\QuestionListGUI;

/**
 * Class QuestionPoolPlugin
 *
 * @package srag\asq\QuestionPool
 *
 * @author Fluxlabs AG - Adrian Lüthi <adi@fluxlabs.ch>
 */
class QuestionPoolPlugin extends AbstractAsqPlugin
{
    use LanguageTrait;

    private function __construct(ILIASReference $reference)
    {
        parent::__construct($reference);

        $this->loadLanguageModule('asq');
        $this->loadLanguageModule(SetupAsqPoolLanguages::ASQ_POOL_LANGUAGE_PREFIX);

        $this->addModule(QuestionPoolStorage::class);
        $this->addModule(ASQModule::class);
        $this->addModule(TaxonomyModule::class);
        $this->addModule(QuestionListGUI::class);
    }

    public static function load(ILIASReference $reference) : QuestionPoolPlugin
    {
        return new QuestionPoolPlugin($reference);
    }

    public static function create(ILIASReference $reference, string $title, string $description) : QuestionPoolPlugin
    {
        $service = new QuestionPoolService();
        $service->createQuestionPool($title, $description, $reference->getId());

        return new QuestionPoolPlugin($reference);
    }

    public static function getInitialCommand(): string
    {
        return QuestionListGUI::CMD_SHOW_QUESTIONS;
    }
}