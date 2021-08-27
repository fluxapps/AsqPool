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
 * Class StorePoolCommandHandler
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class StorePoolCommandHandler implements CommandHandlerContract
{
    /**
     * @param $command StorePoolCommand
     */
    public function handle(CommandContract $command) : Result
    {
        $repo = new QuestionPoolRepository();

        $repo->save($command->getPool());

        return new Ok(null);
    }
}
