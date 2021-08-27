<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application\Command;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolListItem;
use srag\CQRS\Command\CommandContract;
use srag\CQRS\Command\CommandHandlerContract;
use srag\asq\QuestionPool\Domain\Model\QuestionPool;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolRepository;
use srag\asq\Test\Application\Section\Command\CreateSectionCommand;

/**
 * Class CreatePoolCommandHandler
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class CreatePoolCommandHandler implements CommandHandlerContract
{
    /**
     * @param $command CreatePoolCommand
     */
    public function handle(CommandContract $command) : Result
    {
        $pool = QuestionPool::create(
            $command->getId(),
            $command->getIssuingUserId(),
            $command->getData()
        );

        $repo = new QuestionPoolRepository();
        $repo->save($pool);

        $list = QuestionPoolListItem::new(
            $command->getId()->toString(),
            $command->getData()->getName(),
            $command->getIssuingUserId(),
            $command->getData()->getTitle()
        );

        $list->save();

        return new Ok(null);
    }
}
