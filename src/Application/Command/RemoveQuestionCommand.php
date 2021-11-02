<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application\Command;

use ILIAS\Data\UUID\Uuid;
use Fluxlabs\CQRS\Command\AbstractCommand;

/**
 * Class RemoveQuestionCommand
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class RemoveQuestionCommand extends AbstractCommand
{
    public Uuid $question_pool_id;

    public Uuid $question_id;

    public function __construct(Uuid $question_pool_id, Uuid $question_id)
    {
        $this->question_pool_id = $question_pool_id;
        $this->question_id = $question_id;
        parent::__construct();
    }

    public function getQuestionPoolId() : Uuid
    {
        return $this->question_pool_id;
    }

    public function getQuestionId() : Uuid
    {
        return $this->question_id;
    }
}
