<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use ilObjTaxonomy;
use ilTaxonomyNode;
use ilTaxonomyTree;
use ilTemplate;
use srag\asq\Infrastructure\Helpers\PathHelper;

/**
 * Class TaxonomyEditGUI
 *
 * @package srag\asq\QuestionPool
 *
 * @author fluxlabs ag - Adrian Lüthi <adi@fluxlabs.ch>
 */
class TaxonomyEditGUI
{
    use PathHelper;
    use CtrlTrait;

    private ilObjTaxonomy $taxonomy;

    private ilTaxonomyTree $tree;

    public function __construct(ilObjTaxonomy $taxonomy)
    {
        $this->taxonomy = $taxonomy;
        $this->tree = $taxonomy->getTree();
    }

    public function render() : string
    {
        $tpl = new ilTemplate($this->getBasePath(__DIR__) . 'src/Module/Taxonomy/taxonomyEdit.html', true, true);

        $root = $this->tree->getNodeData($this->tree->readRootId());
        $this->renderNode($root['obj_id'], $root['title'], intval($root['depth']), $tpl);

        $this->processNode($this->tree->readRootId(), $tpl);

        return $tpl->get();
    }

    private function processNode(string $node_id, ilTemplate $tpl) : void
    {
        foreach ($this->tree->getChilds($node_id) as $node) {
            $this->renderNode($node['obj_id'], $node['title'], intval($node['depth']), $tpl);
            $this->processNode($node['obj_id'], $tpl);
        }
    }

    private function renderNode(string $id, string $title, int $depth, ilTemplate $tpl) : void
    {
        $this->setLinkParameter(TaxonomyModule::NODE_KEY, $id);

        if ($depth > 1) {
            $tpl->setCurrentBlock('delete');
            $tpl->setVariable('DELETE_ACTION', $this->getCommandLink(TaxonomyModule::COMMAND_DELETE_TAXONOMY_NODE));
            $tpl->setVariable('DELETE', 'TODO Delete');
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('content');
        $tpl->setVariable('DEPTH', $depth);
        $tpl->setVariable('TITLE', $title);
        $tpl->setVariable('ADD_CHILD_TITLE', TaxonomyModule::TITLE_KEY . $id);
        $tpl->setVariable('ADD_ACTION', $this->getCommandLink(TaxonomyModule::COMMAND_ADD_TAXONOMY_NODE));
        $tpl->setVariable('ADD', 'TODO Add');
        $tpl->setVariable('EDIT_ACTION', $this->getCommandLink(TaxonomyModule::COMMAND_EDIT_TAXONOMY_NODE));
        $tpl->setVariable('EDIT', 'TODO Edit');
        $tpl->parseCurrentBlock();
    }
}