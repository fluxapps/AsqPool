<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Model;

use srag\CQRS\Aggregate\AbstractAggregateRoot;
use srag\CQRS\Aggregate\AbstractValueObject;
use srag\CQRS\Event\Standard\AggregateCreatedEvent;
use ILIAS\Data\UUID\Uuid;
use ilDateTime;
use srag\asq\Application\Exception\AsqException;
use srag\asq\QuestionPool\Domain\Event\QuestionAddedEvent;
use srag\asq\QuestionPool\Domain\Event\QuestionRemovedEvent;

/**
 * Class TaxonomyData
 *
 * @package srag\asq\QuestionPool
 *
 * @author fluxlabs ag - Adrian Lüthi <adi@fluxlabs.ch>
 */
class TaxonomyData extends AbstractValueObject
{
    public ?int $taxonomy_id;

    public ?array $question_mapping;

    public function __construct(?int $taxonomy_id = null,
                                ?array $question_mapping = null) {
        $this->taxonomy_id = $taxonomy_id;
        $this->question_mapping = $question_mapping;
    }

    public function getTaxonomyId() : ?int
    {
        return $this->taxonomy_id;
    }

    public function getQuestionMapping() : ?array
    {
        return $this->question_mapping;
    }
}