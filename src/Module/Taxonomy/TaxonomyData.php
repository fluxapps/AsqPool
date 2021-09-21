<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

use srag\CQRS\Aggregate\AbstractValueObject;

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
                                ?array $question_mapping = null)
    {
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