<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\Taxonomy;

use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use Fluxlabs\Assessment\Tools\DIC\LanguageTrait;
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
    use LanguageTrait;

    public function render() : string
    {
        $tpl = new ilTemplate($this->getBasePath(__DIR__) . 'src/Module/Taxonomy/taxonomyCreate.html', true, true);

        $tpl->setVariable('TITLE', $this->txt('asqp_title'));
        $tpl->setVariable('TITLE_KEY',TaxonomyModule::TITLE_KEY);
        $tpl->setVariable('DESCRIPTION', $this->txt('asqp_description'));
        $tpl->setVariable('DESCRIPTION_KEY', TaxonomyModule::DESCRIPTION_KEY);
        $tpl->setVariable('CREATE', $this->txt('asqp_create'));
        $tpl->setVariable('CREATE_ACTION', $this->getCommandLink(TaxonomyModule::COMMAND_CREATE_TAXONOMY));

        return $tpl->get();
    }
}