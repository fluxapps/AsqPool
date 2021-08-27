<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Model;

use srag\asq\QuestionPool\Domain\Event\PoolConfigurationSetEvent;
use srag\asq\QuestionPool\Domain\Event\PoolDataSetEvent;
use srag\CQRS\Aggregate\AbstractAggregateRoot;
use srag\CQRS\Aggregate\AbstractValueObject;
use srag\CQRS\Event\DomainEvent;
use srag\CQRS\Event\Standard\AggregateCreatedEvent;
use ILIAS\Data\UUID\Uuid;
use ilDateTime;
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
class QuestionPool extends AbstractAggregateRoot
{
    const DATA = 'qpd';

    /**
     * @var Uuid[]
     */
    protected array $questions = [];

    protected QuestionPoolData $data;

    /**
     * @var AbstractValueObject[]
     */
    protected array $configurations = [];

    public static function create(
        Uuid $uuid,
        int $initiating_user_id,
        QuestionPoolData $data
    ) : QuestionPool {
        $pool = new QuestionPool();
        $pool->ExecuteEvent(
            new AggregateCreatedEvent(
                $uuid,
                new ilDateTime(time(), IL_CAL_UNIX),
                $initiating_user_id,
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

    public function setData(QuestionPoolData $data, int $user_id) : void
    {
        $this->ExecuteEvent(
            new PoolDataSetEvent(
                $this->aggregate_id,
                new ilDateTime(time(), IL_CAL_UNIX),
                $user_id,
                $data)
        );
    }

    protected function applyPoolDataSetEvent(PoolDataSetEvent $event) : void
    {
        $this->data = $event->getData();
    }

    public function getConfiguration(string $config_for) : AbstractValueObject
    {
        return $this->configurations[$config_for];
    }

    public function setConfiguration(string $config_for, AbstractValueObject $config, int $user_id) : void
    {
        $this->ExecuteEvent(
            new PoolConfigurationSetEvent(
                $this->aggregate_id,
                new ilDateTime(time(), IL_CAL_UNIX),
                $user_id,
                $config,
                $config_for)
        );
    }

    protected function applyPoolConfigurationSetEvent(PoolConfigurationSetEvent $event) :void
    {
        $this->configurations[$event->getConfigFor()] = $event->getConfig();
    }

    public function addQuestion(Uuid $question_id, int $user_id) : void
    {
        if (!in_array($question_id, $this->questions)) {
            $this->ExecuteEvent(
                new QuestionAddedEvent(
                    $this->aggregate_id,
                    new ilDateTime(time(), IL_CAL_UNIX),
                    $user_id,
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

    public function removeQuestion(Uuid $question_id, int $user_id) : void
    {
        if (in_array($question_id, $this->questions)) {
            $this->ExecuteEvent(
                new QuestionRemovedEvent(
                    $this->aggregate_id,
                    new ilDateTime(time(), IL_CAL_UNIX),
                    $user_id,
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