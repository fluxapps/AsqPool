<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application;

use Fluxlabs\Assessment\Tools\Domain\AbstractAsqPlugin;
use ILIAS\Data\UUID\Uuid;
use srag\asq\QuestionPool\Module\QuestionService\ASQModule;
use srag\asq\QuestionPool\Module\Storage\QuestionPoolStorage;
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
    private function __construct(Uuid $pool_id)
    {
        parent::__construct();

        $storage = new QuestionPoolStorage($this->event_queue, $this->access, $pool_id);
        $this->addModule($storage);

        $this->addModule(new ASQModule($this->event_queue, $this->access));

        $this->addModule(new QuestionListGUI(
            $this->event_queue,
            $this->access,
            $storage
        ));
    }

    public static function load(Uuid $test_id) : QuestionPoolPlugin
    {
        return new QuestionPoolPlugin($test_id);
    }

    public static function create(Uuid $test_id, string $title, string $description) : QuestionPoolPlugin
    {
        $service = new QuestionPoolService();
        $service->createQuestionPool($title, $description, $test_id);

        return new QuestionPoolPlugin($test_id);
    }

    public static function getInitialCommand(): string
    {
        return QuestionListGUI::CMD_SHOW_QUESTIONS;
    }
}