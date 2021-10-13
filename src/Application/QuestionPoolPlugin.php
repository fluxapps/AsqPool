<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application;

use Fluxlabs\Assessment\Tools\Domain\AbstractAsqPlugin;
use Fluxlabs\Assessment\Tools\Domain\ILIASReference;
use ILIAS\Data\UUID\Uuid;
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
    private function __construct(ILIASReference $reference)
    {
        parent::__construct($reference);

        $storage = new QuestionPoolStorage($this->event_queue, $this->access, $reference->getId());
        $this->addModule($storage);

        $this->addModule(new ASQModule($this->event_queue, $this->access));

        $this->addModule(new TaxonomyModule($this->event_queue, $this->access));

        $this->addModule(new QuestionListGUI(
            $this->event_queue,
            $this->access
        ));
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