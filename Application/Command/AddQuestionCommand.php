<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application\Command;

use ILIAS\Data\UUID\Uuid;
use srag\CQRS\Command\AbstractCommand;

/**
 * Class AddQuestionCommand
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class AddQuestionCommand extends AbstractCommand
{
    /**
     * @var Uuid
     */
    public $question_pool_id;

    /**
     * @var Uuid
     */
    public $question_id;

    /**
     * @param Uuid $section_id
     * @param int $user_id
     * @param Uuid $question_id
     */
    public function __construct(Uuid $question_pool_id, int $user_id, Uuid $question_id)
    {
        $this->question_pool_id = $question_pool_id;
        $this->question_id = $question_id;
        parent::__construct($user_id);
    }

    /**
     * @return Uuid
     */
    public function getQuestionPoolId() : Uuid
    {
        return $this->question_pool_id;
    }

    /**
     * @return Uuid
     */
    public function getQuestionId() : Uuid
    {
        return $this->question_id;
    }
}
