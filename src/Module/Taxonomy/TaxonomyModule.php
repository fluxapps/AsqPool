<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use Fluxlabs\Assessment\Tools\DIC\KitchenSinkTrait;
use Fluxlabs\Assessment\Tools\Domain\ILIASReference;
use Fluxlabs\Assessment\Tools\Domain\IObjectAccess;
use Fluxlabs\Assessment\Tools\Domain\Modules\AbstractAsqModule;
use Fluxlabs\Assessment\Tools\Event\IEventQueue;
use Fluxlabs\Assessment\Tools\Event\Standard\AddTabEvent;
use Fluxlabs\Assessment\Tools\Event\Standard\ForwardToCommandEvent;
use Fluxlabs\Assessment\Tools\Event\Standard\SetUIEvent;
use Fluxlabs\Assessment\Tools\UI\System\TabDefinition;
use Fluxlabs\Assessment\Tools\UI\System\UIData;
use ilObjTaxonomy;
use ilTaxonomyNode;
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

    const COMMAND_SHOW_CREATION_GUI = 'showCreateTaxonomy';
    const COMMAND_CREATE_TAXONOMY = 'createTaxonomy';
    const COMMAND_SHOW_EDIT_TAXONOMY_GUI = 'showEdit';
    const COMMAND_EDIT_TAXONOMY_NODE = 'editNode';
    const COMMAND_DELETE_TAXONOMY_NODE = 'deleteNode';
    const COMMAND_ADD_TAXONOMY_NODE = 'addNode';


    private ?TaxonomyData $data;

    private ILIASReference $reference;

    private ilObjTaxonomy $taxonomy;

    public function __construct(IEventQueue $event_queue, IObjectAccess $access, ILIASReference $reference)
    {
        parent::__construct($event_queue, $access);

        $this->reference = $reference;
        $this->data = $this->access->getStorage()->getConfiguration(self::TAXONOMY_KEY);

        if ($this->hasTaxonomy()) {
            $this->raiseEvent(new AddTabEvent(
                $this,
                new TabDefinition(self::class, 'Taxonomies', self::COMMAND_SHOW_EDIT_TAXONOMY_GUI)
            ));
        }
    }

    private function loadTaxonomy() : void
    {
        $this->taxonomy = new ilObjTaxonomy($this->data->getTaxonomyId());
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

        $taxonomy = new ilObjTaxonomy();
        $taxonomy->setTitle($title);
        $taxonomy->setDescription($description);
        $id = intval($taxonomy->create());

        $data = new TaxonomyData($id);
        $this->access->getStorage()->setConfiguration(self::TAXONOMY_KEY, $data);

        $this->raiseEvent(new SetUIEvent($this, new UIData(
            'TODO Create Taxonomy',
            'created'
        )));
    }

    public function showEdit() : void
    {
        $this->loadTaxonomy();

        $gui = new TaxonomyEditGUI($this->taxonomy);

        $this->raiseEvent(new SetUIEvent($this, new UIData(
            'TODO Edit Taxonomy',
            $gui->render()
        )));
    }

    public function addNode() : void
    {
        $this->loadTaxonomy();

        $id = intval($this->getLinkParameter(self::NODE_KEY));
        $title = $this->getPostValue(TaxonomyModule::TITLE_KEY . $id);

        $node = new ilTaxonomyNode();
        $node->setTitle($title);
        $node->setTaxonomyId($this->data->getTaxonomyId());
        $node->create();
        $this->taxonomy->getTree()->insertNode($node->getId(), $id);

        $this->raiseEvent(new ForwardToCommandEvent($this, self::COMMAND_SHOW_EDIT_TAXONOMY_GUI));
    }

    public function editNode() : void
    {
        $id = intval($this->getLinkParameter(self::NODE_KEY));
        $title = $this->getPostValue(TaxonomyModule::TITLE_KEY . $id);

        $node = new ilTaxonomyNode($id);
        $node->setTitle($title);
        $node->update();

        $this->raiseEvent(new ForwardToCommandEvent($this, self::COMMAND_SHOW_EDIT_TAXONOMY_GUI));
    }

    public function deleteNode() : void
    {
        $this->loadTaxonomy();

        $id = intval($this->getLinkParameter(self::NODE_KEY));

        $node = new ilTaxonomyNode($id);

        $this->taxonomy->getTree()->deleteNode($this->taxonomy->getTree()->getTreeId(), $node->getId());

        $node->delete();

        $this->raiseEvent(new ForwardToCommandEvent($this, self::COMMAND_SHOW_EDIT_TAXONOMY_GUI));
    }

    public function getCommands(): array
    {
        return [
            self::COMMAND_SHOW_CREATION_GUI,
            self::COMMAND_CREATE_TAXONOMY,
            self::COMMAND_SHOW_EDIT_TAXONOMY_GUI,
            self::COMMAND_ADD_TAXONOMY_NODE,
            self::COMMAND_EDIT_TAXONOMY_NODE,
            self::COMMAND_DELETE_TAXONOMY_NODE
        ];
    }
}