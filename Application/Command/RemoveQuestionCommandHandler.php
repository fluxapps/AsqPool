<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application\Command;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use srag\CQRS\Command\CommandContract;
use srag\CQRS\Command\CommandHandlerContract;
use srag\asq\QuestionPool\Domain\Model\QuestionPool;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolRepository;

/**
 * Class RemoveQuestionCommandHandler
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class RemoveQuestionCommandHandler implements CommandHandlerContract
{
    /**
     * @param $command AddQuestionCommand
     */
    public function handle(CommandContract $command) : Result
    {
        $repo = new QuestionPoolRepository();

        /** @var $pool QuestionPool */
        $pool = $repo->getAggregateRootById($command->getQuestionPoolId());

        $pool->removeQuestion($command->getQuestionId(), $command->getIssuingUserId());

        $repo->save($pool);

        return new Ok(null);
    }
}
