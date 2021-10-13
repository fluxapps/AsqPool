<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use Fluxlabs\Assessment\Tools\DIC\KitchenSinkTrait;
use Fluxlabs\Assessment\Tools\Domain\IObjectAccess;
use Fluxlabs\Assessment\Tools\Domain\Modules\AbstractAsqModule;
use Fluxlabs\Assessment\Tools\Event\IEventQueue;
use Fluxlabs\Assessment\Tools\Event\Standard\AddTabEvent;
use Fluxlabs\Assessment\Tools\Event\Standard\ForwardToCommandEvent;
use Fluxlabs\Assessment\Tools\Event\Standard\SetUIEvent;
use Fluxlabs\Assessment\Tools\Service\Taxonomy\Taxonomy;
use Fluxlabs\Assessment\Tools\UI\System\TabDefinition;
use Fluxlabs\Assessment\Tools\UI\System\UIData;
use ILIAS\Data\UUID\Uuid;
use ilObjTaxonomy;
use ilTaxonomyNode;
use ilTaxonomyTree;
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

    const TAXONOMY_KEY = 'taxonomy_data';
    const NODE_KEY = 'currentNode';
    const TITLE_KEY = 'taxTitle';
    const DESCRIPTION_KEY = 'taxDescription';

    const TAX_POST_KEY = 'taxonomy_';

    const COMMAND_SHOW_CREATION_GUI = 'showCreateTaxonomy';
    const COMMAND_CREATE_TAXONOMY = 'createTaxonomy';
    const COMMAND_SHOW_EDIT_TAXONOMY_GUI = 'showEdit';
    const COMMAND_EDIT_TAXONOMY_NODE = 'editNode';
    const COMMAND_DELETE_TAXONOMY_NODE = 'deleteNode';
    const COMMAND_ADD_TAXONOMY_NODE = 'addNode';
    const COMMAND_SAVE_TAXONOMY_MAPPINGS = 'saveMapping';


    private ?TaxonomyData $data;

    private Taxonomy $taxonomy;

    public function __construct(IEventQueue $event_queue, IObjectAccess $access)
    {
        parent::__construct($event_queue, $access);

        $this->data = $this->access->getStorage()->getConfiguration(self::TAXONOMY_KEY);

        if ($this->hasTaxonomy()) {
            $this->raiseEvent(new AddTabEvent(
                $this,
                new TabDefinition(self::class, 'Taxonomies', self::COMMAND_SHOW_EDIT_TAXONOMY_GUI)
            ));

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
            'TODO Create Taxonomy',
            $gui->render()
        )));
    }

    public function createTaxonomy() : void
    {
        $title = $this->getPostValue(TaxonomyModule::TITLE_KEY);
        $description = $this->getPostValue(TaxonomyModule::DESCRIPTION_KEY);

        $id = $this->taxonomy->createNew($title, $description);

        $data = new TaxonomyData($id);
        $this->access->getStorage()->setConfiguration(self::TAXONOMY_KEY, $data);

        $this->raiseEvent(new SetUIEvent($this, new UIData(
            'TODO Create Taxonomy',
            'created'
        )));
    }

    public function showEdit() : void
    {
        $gui = new TaxonomyEditGUI($this->taxonomy->getNodeMapping());

        $this->raiseEvent(new SetUIEvent($this, new UIData(
            'TODO Edit Taxonomy',
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
        return self::TAX_POST_KEY . $id;
    }

    public function getCommands(): array
    {
        return [
            self::COMMAND_SHOW_CREATION_GUI,
            self::COMMAND_CREATE_TAXONOMY,
            self::COMMAND_SHOW_EDIT_TAXONOMY_GUI,
            self::COMMAND_ADD_TAXONOMY_NODE,
            self::COMMAND_EDIT_TAXONOMY_NODE,
            self::COMMAND_DELETE_TAXONOMY_NODE,
            self::COMMAND_SAVE_TAXONOMY_MAPPINGS
        ];
    }
}