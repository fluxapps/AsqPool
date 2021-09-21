<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Model;

use Fluxlabs\CQRS\Aggregate\AbstractAggregateRoot;
use Fluxlabs\CQRS\Aggregate\AbstractValueObject;
use Fluxlabs\CQRS\Event\Standard\AggregateCreatedEvent;
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
class QuestionPoolData extends AbstractValueObject
{
    public ?string $name;

    public ?string $title;

    public function __construct(?string $name = null,
                                ?string $title = null) {
        $this->name = $name;
        $this->title = $title;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }
}