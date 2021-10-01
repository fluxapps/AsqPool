<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\UI;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use Fluxlabs\Assessment\Tools\DIC\KitchenSinkTrait;
use Fluxlabs\Assessment\Tools\Domain\IObjectAccess;
use Fluxlabs\Assessment\Tools\Domain\Modules\AbstractAsqModule;
use Fluxlabs\Assessment\Tools\Event\IEventQueue;
use Fluxlabs\Assessment\Tools\Event\Standard\AddTabEvent;
use Fluxlabs\Assessment\Tools\Event\Standard\SetUIEvent;
use Fluxlabs\Assessment\Tools\UI\Components\AsqTable;
use Fluxlabs\Assessment\Tools\UI\System\TabDefinition;
use Fluxlabs\Assessment\Tools\UI\System\UIData;
use ILIAS\Data\UUID\Factory;
use ILIAS\Data\UUID\Uuid;
use ilUtil;
use srag\asq\Application\Service\AsqServices;
use srag\asq\Domain\QuestionDto;
use srag\asq\Infrastructure\Helpers\PathHelper;
use srag\asq\QuestionPool\Module\QuestionService\ASQModule;
use srag\asq\QuestionPool\Module\Storage\QuestionPoolStorage;
use srag\asq\QuestionPool\Module\Taxonomy\TaxonomyModule;

/**
 * Class QuestionListGUI
 *
 * @package srag\asq\QuestionPool
 *
 * @author studer + raimann ag - Team Core 2 <al@studer-raimann.ch>
 */
class QuestionListGUI extends AbstractAsqModule
{
    use PathHelper;
    use CtrlTrait;
    use KitchenSinkTrait;

    const CMD_DELETE_QUESTION = 'deleteQuestion';
    const CMD_SHOW_QUESTIONS = "showQuestions";

    const COL_ID = 'QUESTION_ID';
    const COL_TITLE = 'QUESTION_TITLE';
    const COL_TYPE = 'QUESTION_TYPE';
    const COL_AUTHOR = 'QUESTION_AUTHOR';
    const COL_VERSIONS = 'QUESTION_VERSIONS';
    const COL_STATUS = 'QUESTION_STATUS';
    const COL_TAXONOMY = 'QUESTION_TAXONOMY';
    const COL_EDIT_LINK = "QUESTION_EDIT_LINK";


    const VAL_NO_TITLE = '-----';

    private Uuid $pool_id;

    private QuestionPoolStorage $data;

    private AsqServices $asq_service;

    private Factory $uuid_factory;

    private TaxonomyModule $taxonomies;

    public function __construct(IEventQueue $event_queue, IObjectAccess $access)
    {
        parent::__construct($event_queue, $access);

        global $ASQDIC, $DIC;
        $this->asq_service = $ASQDIC->asq();
        $this->uuid_factory = new Factory();
        $this->data = $this->access->getStorage();
        $this->taxonomies = $this->access->getModule(TaxonomyModule::class);

        $this->raiseEvent(new AddTabEvent(
            $this,
            new TabDefinition(self::class, 'Questions', self::CMD_SHOW_QUESTIONS)
        ));
    }

    public function showQuestions() : void
    {
        $this->raiseEvent(new SetUIEvent($this, new UIData(
            'Questions',
            $this->renderContent(),
            null,
            $this->getToolbarButtons()
        )));
    }

    public function renderContent() : string
    {
        $question_table = new AsqTable([
            self::COL_ID => '',
            self::COL_TITLE => 'TODO header_title',
            self::COL_TYPE => 'TODO header_type',
            self::COL_AUTHOR => 'TODO header_creator',
            self::COL_VERSIONS => 'TODO header_versions',
            self::COL_STATUS => 'TODO header_status',
            self::COL_TAXONOMY => 'TODO header_taxonomy',
            self::COL_EDIT_LINK => ''
        ],
        $this->getQuestionsAsAssocArray(),
        [
            'TODO Delete Questions' => $this->getCommandLink(self::CMD_DELETE_QUESTION),
            'TODO Save Taxonomies' => $this->getCommandLink(TaxonomyModule::COMMAND_SAVE_TAXONOMY_MAPPINGS)
        ]);


        return '<form>' . $question_table->render() . '</form>';
    }

    public function getToolbarButtons() : array
    {
        $buttons = [];

        $link = $this->asq_service->link()->getCreationLink();

        $buttons[] = $this->getKSFactory()->button()->standard(
            $link->getLabel(),
            $link->getAction()
        );;

        if (!$this->taxonomies->hasTaxonomy()) {
            $buttons[] = $this->getKSFactory()->button()->standard(
                'TODO CreateTaxonomy',
                $this->getCommandLink(TaxonomyModule::COMMAND_SHOW_CREATION_GUI)
            );
        }

        return $buttons;
    }


    private function getQuestionsAsAssocArray() : array
    {
        $assoc_array = [];
        $items = $this->data->getQuestionsOfPool();

        if (is_null($items)) {
            return $assoc_array;
        }

        foreach ($items as $item) {
            $question_dto = $this->asq_service->question()->getQuestionByQuestionId($item);

            $data = $question_dto->getData();

            $question_array[self::COL_TITLE] = is_null($data) ? self::VAL_NO_TITLE : (empty($data->getTitle()) ? self::VAL_NO_TITLE : $data->getTitle());
            $question_array[self::COL_TYPE] = 'TODO TRANS ' . $question_dto->getType()->getTitleKey();
            $question_array[self::COL_AUTHOR] = is_null($data) ? '' : $data->getAuthor();
            $question_array[self::COL_EDIT_LINK] = $this->getRowActions($question_dto);
            $question_array[self::COL_VERSIONS] = $this->getVersionsInfo($item);
            $question_array[self::COL_STATUS] = $this->getStatus($question_dto);
            $question_array[self::COL_ID] = $question_dto->getId();
            $question_array[self::COL_TAXONOMY] = $this->taxonomies->renderTaxonomySelection($question_dto->getId());

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

    private function getRowActions(QuestionDto $question) : string
    {
        $link = $this->asq_service->link()->getEditLink($question->getId());

        $button = $this->getKSFactory()->button()->shy($link->getLabel(), $link->getAction());

        return $this->renderKSComponent($button);
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

    public function getCommands(): array
    {
        return [
            self::CMD_SHOW_QUESTIONS,
            self::CMD_DELETE_QUESTION
        ];
    }
}
