<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\UI;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use ILIAS\Data\UUID\Factory;
use ILIAS\Data\UUID\Uuid;
use ilLinkButton;
use ilObjTaxonomy;
use ilTable2GUI;
use ilTemplate;
use ilUtil;
use srag\asq\Application\Service\AsqServices;
use srag\asq\Domain\QuestionDto;
use srag\asq\Infrastructure\Helpers\PathHelper;
use srag\asq\QuestionPool\Application\QuestionPoolService;
use srag\asq\QuestionPool\Domain\Model\TaxonomyData;

/**
 * Class QuestionPoolEventStore
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class QuestionListGUI
{
    use PathHelper;
    use CtrlTrait;

    const CMD_QUESTION_ACTION = 'questionAction';
    const CMD_ADD_TAXONOMY = 'addTaxonomy';
    const CMD_EDIT_TAXONOMY = 'editTaxonomy';
    const CMD_REMOVE_TAXONOMY = 'removeTaxonomy';

    const COL_TITLE = 'QUESTION_TITLE';
    const COL_TYPE = 'QUESTION_TYPE';
    const COL_AUTHOR = 'QUESTION_AUTHOR';
    const COL_EDIT_LINK = "QUESTION_EDIT_LINK";
    const COL_VERSIONS = 'QUESTION_VERSIONS';
    const COL_STATUS = 'QUESTION_STATUS';
    const COL_ID = 'QUESTION_ID';

    const VAL_NO_TITLE = '-----';
    const VAR_ACTION = 'selectedAction';
    const VAR_ACTION_DELETE = 'deleteQuestion';

    const KEY_TAXONOMY = 'pool_taxonomy';

    private Uuid $pool_id;

    private QuestionPoolService $pool_service;

    private AsqServices $asq_service;

    private Factory $uuid_factory;

    private ?TaxonomyData $taxonomy_data;

    public function __construct(Uuid $pool_id)
    {
        global $ASQDIC;

        $this->asq_service = $ASQDIC->asq();
        $this->pool_service = new QuestionPoolService();
        $this->uuid_factory = new Factory();

        $this->pool_id = $pool_id;
        $this->taxonomy_data = $this->pool_service->getPoolConfiguration($this->pool_id, self::KEY_TAXONOMY);
    }

    public function createQuestionTable($parent) : ilTable2GUI
    {
        $question_table = new ilTable2GUI($parent);
        $question_table->setRowTemplate('tpl.questions_row.html', $this->getBasePath(__DIR__));
        $question_table->addColumn('');
        $question_table->addColumn('TODO header_title', self::COL_TITLE);
        $question_table->addColumn('TODO header_type', self::COL_TYPE);
        $question_table->addColumn('TODO header_creator', self::COL_AUTHOR);
        $question_table->addColumn('TODO header_versions', self::COL_VERSIONS);
        $question_table->addColumn('TODO header_status', self::COL_STATUS);

        $question_table->addMultiItemSelectionButton(
            self::VAR_ACTION,
            [
                self::VAR_ACTION_DELETE => 'TODO delete_question'
            ],
            self::CMD_QUESTION_ACTION,
            'TODO execute'
        );

        $question_table->setData($this->getQuestionsAsAssocArray());

        return $question_table;
    }

    public function getToolbarButtons() : array
    {
        $buttons = [];

        $link = $this->asq_service->link()->getCreationLink();
        $link_button = ilLinkButton::getInstance();
        $link_button->setUrl($link->getAction());
        $link_button->setCaption($link->getLabel(), false);
        $buttons[] = $link_button;

        if ($this->taxonomy_data === null) {
            $add_taxonomy = ilLinkButton::getInstance();
            $add_taxonomy->setUrl($this->getCommandLink(self::CMD_ADD_TAXONOMY));
            $add_taxonomy->setCaption('TODO Add Taxonomy', false);
            $buttons[] = $add_taxonomy;
        }
        else {
            $edit_taxonomy = ilLinkButton::getInstance();
            $edit_taxonomy->setUrl($this->getCommandLink(self::CMD_EDIT_TAXONOMY));
            $edit_taxonomy->setCaption('TODO Edit Taxonomy', false);
            $buttons[] = $edit_taxonomy;

            $remove_taxonomy = ilLinkButton::getInstance();
            $remove_taxonomy->setUrl($this->getCommandLink(self::CMD_REMOVE_TAXONOMY));
            $remove_taxonomy->setCaption('TODO Remove Taxonomy', false);
            $buttons[] = $remove_taxonomy;
        }

        return $buttons;
    }


    private function getQuestionsAsAssocArray() : array
    {
        $assoc_array = [];
        $items = $this->pool_service->getQuestionsOfPool($this->pool_id);

        if (is_null($items)) {
            return $assoc_array;
        }

        foreach ($items as $item) {
            $question_dto = $this->asq_service->question()->getQuestionByQuestionId($item);

            $data = $question_dto->getData();

            $question_array[self::COL_TITLE] = is_null($data) ? self::VAL_NO_TITLE : (empty($data->getTitle()) ? self::VAL_NO_TITLE : $data->getTitle());
            $question_array[self::COL_TYPE] = 'TODO TRANS' . $question_dto->getType()->getTitleKey();
            $question_array[self::COL_AUTHOR] = is_null($data) ? '' : $data->getAuthor();
            $question_array[self::COL_EDIT_LINK] = $this->asq_service->link()->getEditLink($question_dto->getId())->getAction();
            $question_array[self::COL_VERSIONS] = $this->getVersionsInfo($item);
            $question_array[self::COL_STATUS] = $this->getStatus($question_dto);
            $question_array[self::COL_ID] = $question_dto->getId();

            $assoc_array[] = $question_array;
        }

        return $assoc_array;
    }

    private function getVersionsInfo(Uuid $question_id) : string
    {
        $revisions = $this->asq_service->question()->getAllRevisionsOfQuestion($question_id);

        return join('<br />', array_map(function($revision) use ($question_id) {
            return sprintf(
                '<a href="%s">%s</a>',
                $this->asq_service->link()->getPreviewLink($question_id, $revision->getRevisionName())->getAction(),
                $revision->getRevisionName());
        }, $revisions));
    }

    private function getStatus(QuestionDto $question) : string
    {
        $img = '';

        if(!$question->isComplete()) {
            $img = $this->getBasePath(__DIR__) . 'templates/images/wrong.svg';
        }
        else if ($question->hasUnrevisedChanges()) {
            $img = $this->getBasePath(__DIR__) . 'templates/images/ok_yellow.svg';
        }
        else {
            $img = $this->getBasePath(__DIR__) . 'templates/images/ok.svg';
        }

        return sprintf('<img src="%s" style="height: 20px;" />', $img);
    }

    public function deleteQuestion() : void
    {
        if ($_POST['action'] === null) {
            return;
        }

        foreach ($_POST['action'] as $question_id) {
            $this->pool_service->removeQuestion($this->pool_id, $this->uuid_factory->fromString($question_id));
            ilUtil::sendInfo('TODO question_removed');
        }
    }

    public function addTaxonomy() : void
    {
        $taxonomy = new ilObjTaxonomy();
        $taxonomy->create();
        $tax_data = new TaxonomyData($taxonomy->getId());
        $this->pool_service->storePoolConfiguration(self::KEY_TAXONOMY, $tax_data);
    }
}
