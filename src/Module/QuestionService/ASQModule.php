<?php
declare(strict_types = 1);

namespace srag\asq\QuestionPool\Module\QuestionService;

use AsqQuestionAuthoringGUI;
use Fluxlabs\Assessment\Tools\DIC\CtrlTrait;
use Fluxlabs\Assessment\Tools\DIC\KitchenSinkTrait;
use Fluxlabs\Assessment\Tools\Domain\IObjectAccess;
use Fluxlabs\Assessment\Tools\Domain\Modules\AbstractAsqModule;
use Fluxlabs\Assessment\Tools\Event\IEventQueue;
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
class ASQModule extends AbstractAsqModule implements IAuthoringCaller
{
    use CtrlTrait;
    use KitchenSinkTrait;

    private AsqServices $asq_services;

    public function __construct(IEventQueue $event_queue, IObjectAccess $access)
    {
        parent::__construct($event_queue, $access);

        global $ASQDIC;

        $this->asq_services = $ASQDIC->asq();
    }

    public function executeTransfer(string $transfer) : void
    {
        $backLink = $this->getKSFactory()->link()->standard(
            'TODO back',
            $this->getCommandLink(QuestionListGUI::CMD_SHOW_QUESTIONS)
        );


        $authoring_context_container = new AuthoringContextContainer(
            $backLink,
            $this
        );

        global $DIC;

        $asq = new AsqQuestionAuthoringGUI(
            $authoring_context_container,
            $DIC->language(),
            $DIC->ui(),
            $DIC->ctrl(),
            $DIC->tabs(),
            $DIC->access(),
            $DIC->http(),
            $this->asq_services
        );

        $DIC->ctrl()->forwardCommand($asq);
    }

    public function getExternals(): array
    {
        return [
            strtolower(AsqQuestionAuthoringGUI::class)
        ];
    }

    public function afterQuestionCreated(QuestionDto $question): void
    {
        $this->raiseEvent(new QuestionAddedEvent($this, $question->getId()));
    }
}