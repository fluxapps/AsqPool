<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Domain\Model;

use ActiveRecord;
use Symfony\Component\Console\Question\Question;

/**
 * Class QuestionPool
 *
 * @package srag\asq\QuestionPool
 *
 * @author fluxlabs ag - Adrian Lüthi <adi@fluxlabs.ch>
 */
class QuestionPoolListItem extends ActiveRecord
{
    const STORAGE_NAME = 'asq_qp_li';

    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected $id;

    /**
     * @var string
     *
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     36
     * @con_sequence   true
     */
    protected string $uuid;

    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     * @con_is_notnull true
     */
    protected string $title;

    /**
     * @var ?string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     * @con_length     200
     */
    protected ?string $description;

    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_is_notnull true
     */
    protected int $creator_id;

    public static function new(string $uuid, string $title, int $creator_id, string $description = null) : QuestionPoolListItem {
        $object = new QuestionPoolListItem();
        $object->uuid = $uuid;
        $object->title = $title;
        $object->description = $description;
        $object->creator_id = $creator_id;
        return $object;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUuid() : string
    {
        return $this->uuid;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatorId(): int
    {
        return $this->creator_id;
    }

    public static function returnDbTableName() : string
    {
        return self::STORAGE_NAME;
    }
}