<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\UI;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use Fluxlabs\Assessment\Tools\DIC\KitchenSinkTrait;
use Fluxlabs\Assessment\Tools\DIC\LanguageTrait;
use Fluxlabs\Assessment\Tools\Domain\Modules\AbstractAsqModule;
use Fluxlabs\Assessment\Tools\Domain\Modules\Access\AccessConfiguration;
use Fluxlabs\Assessment\Tools\Domain\Modules\Definition\CommandDefinition;
use Fluxlabs\Assessment\Tools\Domain\Modules\Definition\ModuleDefinition;
use Fluxlabs\Assessment\Tools\Domain\Modules\Definition\TabDefinition;
use Fluxlabs\Assessment\Tools\Domain\Modules\IModuleDefinition;
use Fluxlabs\Assessment\Tools\Event\Standard\ForwardToCommandEvent;
use Fluxlabs\Assessment\Tools\Event\Standard\SetUIEvent;
use Fluxlabs\Assessment\Tools\UI\Components\AsqTable;
use Fluxlabs\Assessment\Tools\UI\System\UIData;
use ILIAS\Data\UUID\Factory;
use ILIAS\Data\UUID\Uuid;
use ilUtil;
use srag\asq\Application\Service\AsqServices;
use srag\asq\Domain\QuestionDto;
use srag\asq\Infrastructure\Helpers\PathHelper;
use srag\asq\QuestionPool\Application\QuestionPoolService;
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
    use LanguageTrait;

    const CMD_DELETE_QUESTION = 'deleteQuestion';
    const CMD_SHOW_QUESTIONS = 'showQuestions';
    const PARAM_QUESTION_ID = 'question_id';

    const TAB_QUESTIONS = 'tab_questions';

    const COL_ID = 'QUESTION_ID';
    const COL_TITLE = 'QUESTION_TITLE';
    const COL_TYPE = 'QUESTION_TYPE';
    const COL_AUTHOR = 'QUESTION_AUTHOR';
    const COL_VERSIONS = 'QUESTION_VERSIONS';
    const COL_STATUS = 'QUESTION_STATUS';
    const COL_TAXONOMY = 'QUESTION_TAXONOMY';
    const COL_ACTIONS = "QUESTION_ACTIONS";


    const VAL_NO_TITLE = '-----';

    private QuestionPoolStorage $data;

    private AsqServices $asq_service;

    private Factory $uuid_factory;

    private TaxonomyModule $taxonomies;

    protected function initialize() : void
    {
        global $ASQDIC, $DIC;
        $this->asq_service = $ASQDIC->asq();
        $this->uuid_factory = new Factory();
        $this->data = $this->access->getStorage();
        $this->taxonomies = $this->access->getModule(TaxonomyModule::class);
    }

    public function showQuestions() : void
    {
        $this->raiseEvent(new SetUIEvent($this, new UIData(
            $this->txt('asqp_questions'),
            $this->renderContent(),
            null,
            $this->getToolbarButtons()
        )));
    }

    public function renderContent() : string
    {
        $question_table = new AsqTable([
            self::COL_ID => '',
            self::COL_TITLE => $this->txt('asqp_title'),
            self::COL_TYPE => $this->txt('asqp_type'),
            self::COL_AUTHOR => $this->txt('asqp_creator'),
            self::COL_VERSIONS => $this->txt('asqp_versions'),
            self::COL_STATUS => $this->txt('asqp_status'),
            self::COL_TAXONOMY => $this->txt('asqp_taxonomy'),
            self::COL_ACTIONS => ''
        ],
        $this->getQuestionsAsAssocArray(),
        [
            $this->txt('asqp_save_taxonomies') => $this->getCommandLink(TaxonomyModule::COMMAND_SAVE_TAXONOMY_MAPPINGS)
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
                $this->txt('asqp_create_taxonomy'),
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
            $question_array[self::COL_TYPE] = $this->txt($question_dto->getType()->getTitleKey());
            $question_array[self::COL_AUTHOR] = is_null($data) ? '' : $data->getAuthor();
            $question_array[self::COL_ACTIONS] = $this->getRowActions($question_dto);
            $question_array[self::COL_VERSIONS] = $this->getVersionsInfo($item);
            $question_array[self::COL_STATUS] = $this->getStatus($question_dto);
            $question_array[self::COL_ID] = $question_dto->getId()->toString();
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
        $edit_link = $this->asq_service->link()->getEditLink($question->getId());
        $edit_button = $this->getKSFactory()->button()->shy($edit_link->getLabel(), $edit_link->getAction());

        $this->setLinkParameter(self::PARAM_QUESTION_ID, $question->getId()->toString());
        $delete_button = $this->getKSFactory()->button()->shy(
            $this->txt('asqp_delete_question'),
            $this->getCommandLink(self::CMD_DELETE_QUESTION)
        );

        return $this->renderKSComponent($edit_button) . $this->renderKSComponent($delete_button);
    }

    public function deleteQuestion() : void
    {
        $question_id = $this->getLinkParameter(self::PARAM_QUESTION_ID);
        $pool_service = new QuestionPoolService();

        $pool_service->removeQuestion($this->data->getId(), $this->uuid_factory->fromString($question_id));
        ilUtil::sendInfo($this->txt('asqp_question_removed'));

        $this->raiseEvent(new ForwardToCommandEvent($this, self::CMD_SHOW_QUESTIONS));
    }

    public function getCommands(): array
    {
        return [
            self::CMD_SHOW_QUESTIONS,
            self::CMD_DELETE_QUESTION
        ];
    }

    public function getModuleDefinition(): IModuleDefinition
    {
        return new ModuleDefinition(
            ModuleDefinition::NO_CONFIG,
            [
                new CommandDefinition(
                    self::CMD_SHOW_QUESTIONS,
                    AccessConfiguration::ACCESS_STAFF,
                    self::TAB_QUESTIONS
                ),
                new CommandDefinition(
                    self::CMD_DELETE_QUESTION,
                    AccessConfiguration::ACCESS_ADMIN,
                    self::TAB_QUESTIONS
                )
            ],
            [],
            [
                new TabDefinition(
                    self::TAB_QUESTIONS,
                    'asqp_questions',
                    self::CMD_SHOW_QUESTIONS
                )
            ]
        );
    }
}
