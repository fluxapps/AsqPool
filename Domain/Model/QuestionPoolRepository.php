<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Model;

use srag\asq\QuestionPool\Application\QuestionPoolService;
use srag\CQRS\Aggregate\AbstractAggregateRepository;
use srag\CQRS\Aggregate\AbstractAggregateRoot;
use srag\CQRS\Event\DomainEvents;
use srag\CQRS\Event\EventStore;
use srag\asq\QuestionPool\Domain\Persistence\QuestionPoolEventStore;

/**
 * Class QuestionPoolRepository
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class QuestionPoolRepository extends AbstractAggregateRepository
{
    private QuestionPoolEventStore $event_store;

    public function __construct()
    {
        parent::__construct();
        $this->event_store = new QuestionPoolEventStore();
    }

    protected function getEventStore() : EventStore
    {
        return $this->event_store;
    }

    protected function reconstituteAggregate(DomainEvents $event_history) : AbstractAggregateRoot
    {
        return QuestionPool::reconstitute($event_history);
    }

    public function getPools(?array $filters = null) : array
    {
        $where = [];

        if ($filters !== null) {
            if (array_key_exists(QuestionPoolService::FILTER_NAME, $filters)) {
                $where['title'] = $filters[QuestionPoolService::FILTER_NAME];
            }
        }

        return QuestionPoolListItem::where($where)->get();
    }
}
