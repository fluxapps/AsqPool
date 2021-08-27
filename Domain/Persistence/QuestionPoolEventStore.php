<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Persistence;

use srag\CQRS\Event\EventStore;

/**
 * Class QuestionPoolEventStore
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class QuestionPoolEventStore extends EventStore
{
    protected function getEventArClass() : string
    {
        return QuestionPoolEventStoreAr::class;
    }
}
