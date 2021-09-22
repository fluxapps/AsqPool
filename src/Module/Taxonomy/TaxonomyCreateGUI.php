<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use ilTemplate;
use srag\asq\Infrastructure\Helpers\PathHelper;

/**
 * Class TaxonomyCreateGUI
 *
 * @package srag\asq\QuestionPool
 *
 * @author fluxlabs ag - Adrian Lüthi <adi@fluxlabs.ch>
 */
class TaxonomyCreateGUI
{
    use PathHelper;
    use CtrlTrait;

    public function render() : string
    {
        $tpl = new ilTemplate($this->getBasePath(__DIR__) . 'src/Module/Taxonomy/taxonomyCreate.html', true, true);

        $tpl->setVariable('TITLE','TODO_Titel');
        $tpl->setVariable('TITLE_KEY',TaxonomyModule::TITLE_KEY);
        $tpl->setVariable('DESCRIPTION', 'TODO_Description');
        $tpl->setVariable('DESCRIPTION_KEY', TaxonomyModule::DESCRIPTION_KEY);
        $tpl->setVariable('CREATE', 'TODO_Create');
        $tpl->setVariable('CREATE_ACTION', $this->getCommandLink(TaxonomyModule::COMMAND_CREATE_TAXONOMY));

        return $tpl->get();
    }
}