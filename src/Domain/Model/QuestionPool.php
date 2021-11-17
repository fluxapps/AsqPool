<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Model;

use Fluxlabs\Assessment\Tools\Domain\Model\PluginAggregateRoot;
use srag\asq\QuestionPool\Domain\Event\PoolConfigurationSetEvent;
use srag\asq\QuestionPool\Domain\Event\PoolDataSetEvent;
use Fluxlabs\CQRS\Event\DomainEvent;
use Fluxlabs\CQRS\Event\Standard\AggregateCreatedEvent;
use ILIAS\Data\UUID\Uuid;
use DateTimeImmutable;
use srag\asq\Application\Exception\AsqException;
use srag\asq\QuestionPool\Domain\Event\QuestionAddedEvent;
use srag\asq\QuestionPool\Domain\Event\QuestionRemovedEvent;

/**
 * Class QuestionPool
 *
 * @package srag\asq\QuestionPool
 *
 * @author fluxlabs ag - Adrian Lüthi <adi@fluxlabs.ch>
 */
class QuestionPool extends PluginAggregateRoot
{
    const DATA = 'qpd';

    /**
     * @var Uuid[]
     */
    protected array $questions = [];

    protected QuestionPoolData $data;

    public static function create(
        Uuid $uuid,
        QuestionPoolData $data
    ) : QuestionPool {
        $pool = new QuestionPool();
        $pool->ExecuteEvent(
            new AggregateCreatedEvent(
                $uuid,
                new DateTimeImmutable(),
                [
                    self::DATA => $data
                ]
            )
        );

        return $pool;
    }

    protected function applyAggregateCreatedEvent(DomainEvent $event) : void
    {
        parent::applyAggregateCreatedEvent($event);

        $this->data = $event->getAdditionalData()[self::DATA];
    }

    public function getData() : QuestionPoolData
    {
        return $this->data;
    }

    public function setData(QuestionPoolData $data) : void
    {
        $this->ExecuteEvent(
            new PoolDataSetEvent(
                $this->aggregate_id,
                new DateTimeImmutable(),
                $data)
        );
    }

    protected function applyPoolDataSetEvent(PoolDataSetEvent $event) : void
    {
        $this->data = $event->getData();
    }

    public function addQuestion(Uuid $question_id) : void
    {
        if (!in_array($question_id, $this->questions)) {
            $this->ExecuteEvent(
                new QuestionAddedEvent(
                    $this->aggregate_id,
                    new DateTimeImmutable(),
                    $question_id)
                );
        }
        else {
            throw new AsqException('Section is already part of Test');
        }
    }

    protected function applyQuestionAddedEvent(QuestionAddedEvent $event) : void
    {
        $this->questions[] = $event->getQuestionId();
    }

    public function removeQuestion(Uuid $question_id) : void
    {
        if (in_array($question_id, $this->questions)) {
            $this->ExecuteEvent(
                new QuestionRemovedEvent(
                    $this->aggregate_id,
                    new DateTimeImmutable(),
                    $question_id)
                );
        }
        else {
            throw new AsqException('Section is not part of Test');
        }
    }

    protected function applyQuestionRemovedEvent(QuestionRemovedEvent $event) : void
    {
        $this->questions = array_diff($this->questions, [$event->getQuestionId()]);
    }

    public function getQuestions() : array
    {
        return $this->questions;
    }
}