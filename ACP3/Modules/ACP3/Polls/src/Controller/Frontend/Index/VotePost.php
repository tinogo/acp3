<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Polls;

class VotePost extends Core\Controller\AbstractWidgetAction
{
    /**
     * @var Core\Date
     */
    private $date;
    /**
     * @var Polls\Model\VoteModel
     */
    private $voteModel;
    /**
     * @var Polls\Validation\VoteValidation
     */
    private $voteValidation;
    /**
     * @var \ACP3\Core\Helpers\FormAction
     */
    private $actionHelper;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        FormAction $actionHelper,
        Core\Date $date,
        Polls\Validation\VoteValidation $voteValidation,
        Polls\Model\VoteModel $voteModel
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->voteValidation = $voteValidation;
        $this->voteModel = $voteModel;
        $this->actionHelper = $actionHelper;
    }

    /**
     * @return array|string|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id)
    {
        return $this->actionHelper->handlePostAction(
            function () use ($id) {
                $formData = $this->request->getPost()->all();
                $ipAddress = $this->request->getSymfonyRequest()->getClientIp();
                $time = $this->date->getCurrentDateTime();

                $this->voteValidation
                    ->setPollId($id)
                    ->setIpAddress($ipAddress)
                    ->validate($formData);

                $this->voteModel->vote($formData, $id, $ipAddress, $time);

                return $this->actionHelper->setRedirectMessage(true, $this->translator->t('polls', 'poll_success'), 'polls/index/result/id_' . $id);
            },
            'polls/index/vote/id_' . $id
        );
    }
}
