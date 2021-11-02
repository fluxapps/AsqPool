<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Application\Command;

use ILIAS\Data\UUID\Uuid;
use srag\asq\QuestionPool\Domain\Model\QuestionPoolData;
use Fluxlabs\CQRS\Command\AbstractCommand;

/**
 * Class CreatePoolCommand
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class CreatePoolCommand extends AbstractCommand
{
    protected Uuid $uuid;

    protected QuestionPoolData $data;

    public function __construct(Uuid $uuid, QuestionPoolData $data)
    {
        $this->uuid = $uuid;
        $this->data = $data;
        parent::__construct();
    }

    public function getId() : Uuid
    {
        return $this->uuid;
    }

    public function getData() : QuestionPoolData
    {
        return $this->data;
    }
}
