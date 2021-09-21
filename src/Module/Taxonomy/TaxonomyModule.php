<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

use AsqQuestionAuthoringGUI;
use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use Fluxlabs\Assessment\Tools\DIC\KitchenSinkTrait;
use Fluxlabs\Assessment\Tools\Domain\Event\ObjectConfigurationSetEvent;
use Fluxlabs\Assessment\Tools\Domain\ILIASReference;
use Fluxlabs\Assessment\Tools\Domain\IObjectAccess;
use Fluxlabs\Assessment\Tools\Domain\Modules\AbstractAsqModule;
use Fluxlabs\Assessment\Tools\Event\IEventQueue;
use Fluxlabs\Assessment\Tools\Event\Standard\SetUIEvent;
use Fluxlabs\Assessment\Tools\UI\System\UIData;
use ILIAS\UI\Component\Button\Button;
use ilObjTaxonomy;
use ilObjTaxonomyGUI;
use srag\asq\Application\Service\AsqServices;
use srag\asq\Application\Service\AuthoringContextContainer;
use srag\asq\Application\Service\IAuthoringCaller;
use srag\asq\Domain\QuestionDto;
use srag\asq\QuestionPool\Module\Storage\Event\QuestionAddedEvent;
use srag\asq\QuestionPool\Module\UI\QuestionListGUI;

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

    const TAXONOMY_KEY = 'taxonomy_data';
    const COMMAND_SHOW_CREATION_GUI = 'showCreateTaxonomy';
    const COMMAND_CREATE_TAXONOMY = 'createTaxonomy';
    const COMMAND_SHOW_EDIT_TAXONOMY_GUI = 'showEdit';
    const COMMAND_EDIT_TAXONOMY = 'editTaxonomy';

    private ?TaxonomyData $data;

    private ILIASReference $reference;

    public function __construct(IEventQueue $event_queue, IObjectAccess $access, ILIASReference $reference)
    {
        parent::__construct($event_queue, $access);

        $this->reference = $reference;
        $this->data = $this->access->getStorage()->getConfiguration(self::TAXONOMY_KEY);
    }

    public function getTaxonomyButton() : Button
    {
        if ($this->data === null)
        {
            $button = $this->getKSFactory()->button()->standard('TODO Add Taxonomy', $this->getCommandLink(self::COMMAND_SHOW_CREATION_GUI));
        }
        else
        {
            $button = $this->getKSFactory()->button()->standard('TODO Edit Taxonomy', $this->getCommandLink(self::COMMAND_SHOW_EDIT_TAXONOMY_GUI));
        }

        return $button;
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
        $title = $_POST[TaxonomyCreateGUI::PARAM_TITLE];
        $description = $_POST[TaxonomyCreateGUI::PARAM_DESCRIPTION];

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

    public function editTaxonomy() : void
    {
        $this->raiseEvent(new SetUIEvent($this, new UIData(
            'TODO Create Taxonomy',
            'editing'
        )));
    }

    public function getCommands(): array
    {
        return [
            self::COMMAND_SHOW_CREATION_GUI,
            self::COMMAND_CREATE_TAXONOMY,
            self::COMMAND_SHOW_EDIT_TAXONOMY_GUI,
            self::COMMAND_EDIT_TAXONOMY
        ];
    }
}