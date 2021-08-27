<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application\Command;

use ILIAS\Data\UUID\Uuid;
use srag\asq\QuestionPool\Domain\Model\QuestionPool;
use srag\CQRS\Command\AbstractCommand;

/**
 * Class StorePoolCommand
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class StorePoolCommand extends AbstractCommand
{
    public QuestionPool $pool;

    public function __construct(QuestionPool $pool, int $user_id)
    {
        $this->pool = $pool;
        parent::__construct($user_id);
    }

    public function getPool() : QuestionPool
    {
        return $this->pool;
    }
}
