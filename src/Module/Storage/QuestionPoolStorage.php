<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Storage;

use Fluxlabs\Assessment\Tools\Domain\IObjectAccess;
use Fluxlabs\Assessment\Tools\Domain\Modules\AbstractAsqModule;
use Fluxlabs\Assessment\Tools\Domain\Modules\IStorageModule;
use Fluxlabs\Assessment\Tools\Event\IEventQueue;
use ILIAS\Data\UUID\Uuid;
use srag\asq\QuestionPool\Application\QuestionPoolService;
use srag\asq\QuestionPool\Module\Storage\Event\QuestionAddedEvent;
use srag\asq\QuestionPool\Module\Storage\Event\QuestionDeletedEvent;
use Fluxlabs\CQRS\Aggregate\AbstractValueObject;

/**
 * Class QuestionPoolStorage
 *
 * @package Fluxlabs\Assessment\Test
 *
 * @author Fluxlabs AG - Adrian Lüthi <adi@fluxlabs.ch>
 */
class QuestionPoolStorage extends AbstractAsqModule implements IStorageModule
{
    private Uuid $pool_id;

    protected QuestionPoolService $pool_service;

    protected function initialize() : void
    {
        $this->pool_id = $this->access->getReference()->getId();
        $this->pool_service = new QuestionPoolService();
    }

    public function getId() : Uuid
    {
        return $this->pool_id;
    }

    public function getConfiguration(string $configuration_for): ?AbstractValueObject
    {
        return $this->pool_service->getConfiguration($this->pool_id, $configuration_for);
    }

    public function getConfigurations(): array
    {
        return $this->pool_service->getConfigurations($this->pool_id);
    }

    public function setConfiguration(string $configuration_for, AbstractValueObject $config): void
    {
        $this->pool_service->setConfiguration($this->pool_id, $config, $configuration_for);
    }

    public function removeConfiguration(string $configuration_for): void
    {
        $this->pool_service->removeConfiguration($this->pool_id, $configuration_for);
    }

    public function getQuestionsOfPool() : array
    {
        return $this->pool_service->getQuestionsOfPool($this->pool_id);
    }

    public function processEvent(object $event): void
    {
        if (get_class($event) === QuestionAddedEvent::class) {
            $this->processQuestionAddedEvent($event);
        }
        else if (get_class($event) === QuestionDeletedEvent::class) {
            $this->processQuestionDeletedEvent($event);
        }
    }

    private function processQuestionAddedEvent(QuestionAddedEvent $event) : void
    {
        $this->pool_service->addQuestion($this->pool_id, $event->getQuestionId());
    }

    private function processQuestionDeletedEvent(QuestionDeletedEvent $event) : void
    {
        $this->pool_service->removeQuestion($this->pool_id, $event->getQuestionId());
    }

    public function save(): void
    {
        //no full object save
    }
}