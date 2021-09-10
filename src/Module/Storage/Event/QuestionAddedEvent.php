<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Storage\Event;

use Fluxlabs\Assessment\Tools\Event\Event;
use Fluxlabs\Assessment\Tools\Event\IEventUser;
use ILIAS\Data\UUID\Uuid;

/**
 * Class QuestionAddedEvent
 *
 * @package srag\asq\QuestionPool
 *
 * @author Fluxlabs AG - Adrian Lüthi <adi@fluxlabs.ch>
 */
class QuestionAddedEvent extends Event
{
    private Uuid $question_id;

    public function __construct(IEventUser $sender, Uuid $question_id)
    {
        parent::__construct($sender);

        $this->question_id = $question_id;
    }

    public function getQuestionId() : Uuid
    {
        return $this->question_id;
    }
}