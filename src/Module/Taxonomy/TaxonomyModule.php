<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

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
use Fluxlabs\Assessment\Tools\Service\Taxonomy\Taxonomy;
use Fluxlabs\Assessment\Tools\UI\System\UIData;
use ILIAS\Data\UUID\Uuid;
use srag\asq\QuestionPool\Module\UI\QuestionListGUI;
use srag\asq\UserInterface\Web\PostAccess;

/**
 * Class ASQModule
 *
 * @package Fluxlabs\Assessment\Pool
 *
 * @author Fluxlabs AG - Adrian Lüthi <adi@fluxlabs.ch>
 */
class TaxonomyModule extends AbstractAsqModule
{
    use CtrlTrait;
    use KitchenSinkTrait;
    use PostAccess;
    use LanguageTrait;

    const TAXONOMY_KEY = 'taxonomy_data';
    const NODE_KEY = 'currentNode';
    const TITLE_KEY = 'taxTitle';
    const DESCRIPTION_KEY = 'taxDescription';

    const TAX_POST_KEY = 'taxonomy_';

    const COMMAND_SHOW_CREATION_GUI = 'showCreateTaxonomy';
    const COMMAND_CREATE_TAXONOMY = 'createTaxonomy';
    const COMMAND_SHOW_EDIT_TAXONOMY_GUI = 'showTaxonomyEdit';
    const COMMAND_EDIT_TAXONOMY_NODE = 'editNode';
    const COMMAND_DELETE_TAXONOMY_NODE = 'deleteNode';
    const COMMAND_ADD_TAXONOMY_NODE = 'addNode';
    const COMMAND_SAVE_TAXONOMY_MAPPINGS = 'saveMapping';

    const TAB_TAXONOMY = 'tab_taxonomy';

    private ?TaxonomyData $data;

    private Taxonomy $taxonomy;

    protected function initialize() : void
    {
        $this->data = $this->access->getStorage()->getConfiguration(self::TAXONOMY_KEY);

        if ($this->hasTaxonomy())
        {
            $this->taxonomy = new Taxonomy($this->data->getTaxonomyId());
        }
    }

    public function hasTaxonomy() : bool
    {
        return $this->data !== null;
    }

    public function showCreateTaxonomy() : void
    {
        $gui = new TaxonomyCreateGUI();

        $this->raiseEvent(new SetUIEvent($this, new UIData(
            $this->txt('asqp_create_taxonomy'),
            $gui->render()
        )));
    }

    public function createTaxonomy() : void
    {
        $title = $this->getPostValue(TaxonomyModule::TITLE_KEY);
        $description = $this->getPostValue(TaxonomyModule::DESCRIPTION_KEY);

        $this->taxonomy = new Taxonomy();
        $id = $this->taxonomy->createNew($title, $description);

        $data = new TaxonomyData($id);
        $this->access->getStorage()->setConfiguration(self::TAXONOMY_KEY, $data);

        $this->raiseEvent(new ForwardToCommandEvent($this, QuestionListGUI::CMD_SHOW_QUESTIONS));
    }

    public function showTaxonomyEdit() : void
    {
        $gui = new TaxonomyEditGUI($this->taxonomy->getNodeMapping());

        $this->raiseEvent(new SetUIEvent($this, new UIData(
            $this->txt('asqp_edit_taxonomy'),
            $gui->render()
        )));
    }

    public function addNode() : void
    {
        $id = intval($this->getLinkParameter(self::NODE_KEY));
        $title = $this->getPostValue(TaxonomyModule::TITLE_KEY . $id);

        $this->taxonomy->createNewNode($id, $title);

        $this->raiseEvent(new ForwardToCommandEvent($this, self::COMMAND_SHOW_EDIT_TAXONOMY_GUI));
    }

    public function editNode() : void
    {
        $id = intval($this->getLinkParameter(self::NODE_KEY));
        $title = $this->getPostValue(TaxonomyModule::TITLE_KEY . $id);

        $this->taxonomy->updateNode($id, $title);

        $this->raiseEvent(new ForwardToCommandEvent($this, self::COMMAND_SHOW_EDIT_TAXONOMY_GUI));
    }

    public function deleteNode() : void
    {
        $id = intval($this->getLinkParameter(self::NODE_KEY));

        $this->taxonomy->deleteNode($id);

        $this->raiseEvent(new ForwardToCommandEvent($this, self::COMMAND_SHOW_EDIT_TAXONOMY_GUI));
    }

    public function renderTaxonomySelection(Uuid $id) : string
    {
        if (!$this->hasTaxonomy()) {
            return '';
        }

        $mapping = $this->taxonomy->getNodeMapping();

        $node_selects = implode('', array_map(function($node) use($id) {
            return sprintf(
                '<option value="%s" %s>%s</option>',
                $node['obj_id'],
                $this->data->getQuestionMapping()[$id->toString()] === $node['obj_id'] ? 'selected="selected"' : '',
                $node['title']);
        }, $mapping));

        return sprintf(
            '<select name="%s"><option value="">---</option>%s</select>',
            $this->getTaxonomyPostName($id),
            $node_selects
        );
    }

    public function saveMapping() : void
    {
        $mapping = [];

        foreach ($this->access->getStorage()->getQuestionsOfPool() as $quesiton_id) {
            $value = $this->getPostValue($this->getTaxonomyPostName($quesiton_id));
            if (strlen($value) > 0) {
                $mapping[$quesiton_id->toString()] = $value;
            }
        }

        $data = new TaxonomyData($this->data->getTaxonomyId(), $mapping);

        $this->access->getStorage()->setConfiguration(self::TAXONOMY_KEY, $data);

        $this->raiseEvent(new ForwardToCommandEvent($this, QuestionListGUI::CMD_SHOW_QUESTIONS));
    }

    private function getTaxonomyPostName(Uuid $id) : string
    {
        return self::TAX_POST_KEY . $id->toString();
    }

    public function getModuleDefinition(): IModuleDefinition
    {
        return new ModuleDefinition(
            ModuleDefinition::NO_CONFIG,
            [
                new CommandDefinition(
                    self::COMMAND_SHOW_CREATION_GUI,
                    AccessConfiguration::ACCESS_STAFF,
                    self::TAB_TAXONOMY
                ),
                new CommandDefinition(
                    self::COMMAND_CREATE_TAXONOMY,
                    AccessConfiguration::ACCESS_ADMIN,
                    self::TAB_TAXONOMY
                ),
                new CommandDefinition(
                    self::COMMAND_SHOW_EDIT_TAXONOMY_GUI,
                    AccessConfiguration::ACCESS_ADMIN,
                    self::TAB_TAXONOMY
                ),
                new CommandDefinition(
                    self::COMMAND_ADD_TAXONOMY_NODE,
                    AccessConfiguration::ACCESS_ADMIN,
                    self::TAB_TAXONOMY
                ),
                new CommandDefinition(
                    self::COMMAND_EDIT_TAXONOMY_NODE,
                    AccessConfiguration::ACCESS_ADMIN,
                    self::TAB_TAXONOMY
                ),
                new CommandDefinition(
                    self::COMMAND_DELETE_TAXONOMY_NODE,
                    AccessConfiguration::ACCESS_ADMIN,
                    self::TAB_TAXONOMY
                ),
                new CommandDefinition(
                    self::COMMAND_SAVE_TAXONOMY_MAPPINGS,
                    AccessConfiguration::ACCESS_ADMIN,
                    self::TAB_TAXONOMY
                )
            ],
            [],
            [
                new TabDefinition(
                    self::TAB_TAXONOMY,
                    'asqp_taxonomy',
                    self::COMMAND_SHOW_EDIT_TAXONOMY_GUI,
                    TabDefinition::PRIORITY_LOW
                )
            ]
        );
    }
}