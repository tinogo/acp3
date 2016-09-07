<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Details;

use ACP3\Core;
use ACP3\Core\Validation\ValidationRules\IntegerValidationRule;
use ACP3\Modules\ACP3\Comments;
use ACP3\Modules\ACP3\System;

/**
 * Class Edit
 * @package ACP3\Modules\ACP3\Comments\Controller\Admin\Details
 */
class Edit extends Core\Controller\AbstractAdminAction
{
    /**
     * @var \ACP3\Modules\ACP3\Comments\Model\Repository\CommentRepository
     */
    protected $commentRepository;
    /**
     * @var \ACP3\Modules\ACP3\Comments\Validation\AdminFormValidation
     */
    protected $adminFormValidation;
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;

    /**
     * Details constructor.
     *
     * @param \ACP3\Core\Controller\Context\AdminContext                 $context
     * @param \ACP3\Modules\ACP3\Comments\Model\Repository\CommentRepository        $commentRepository
     * @param \ACP3\Modules\ACP3\Comments\Validation\AdminFormValidation $adminFormValidation
     * @param \ACP3\Core\Helpers\FormToken                               $formTokenHelper
     */
    public function __construct(
        Core\Controller\Context\AdminContext $context,
        Comments\Model\Repository\CommentRepository $commentRepository,
        Comments\Validation\AdminFormValidation $adminFormValidation,
        Core\Helpers\FormToken $formTokenHelper)
    {
        parent::__construct($context);

        $this->commentRepository = $commentRepository;
        $this->adminFormValidation = $adminFormValidation;
        $this->formTokenHelper = $formTokenHelper;
    }

    /**
     * @param int $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($id)
    {
        $comment = $this->commentRepository->getOneById($id);

        if (empty($comment) === false) {
            $this->breadcrumb
                ->append(
                    $this->translator->t($comment['module'], $comment['module']),
                    'acp/comments/details/index/id_' . $comment['module_id']
                )
                ->append($this->translator->t('comments', 'admin_details_edit'));

            $this->title->setPageTitlePostfix($comment['name']);

            if ($this->request->getPost()->count() !== 0) {
                return $this->executePost(
                    $this->request->getPost()->all(),
                    $comment,
                    $id,
                    $comment['module_id']
                );
            }

            return [
                'form' => array_merge($comment, $this->request->getPost()->all()),
                'module_id' => (int)$comment['module_id'],
                'form_token' => $this->formTokenHelper->renderFormToken(),
                'can_use_emoticons' => true
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
    /**
     * @param array $formData
     * @param array $comment
     * @param int   $commentId
     * @param int   $moduleId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executePost(array $formData, array $comment, $commentId, $moduleId)
    {
        return $this->actionHelper->handleEditPostAction(
            function () use ($formData, $comment, $commentId) {
                $this->adminFormValidation->validate($formData);

                $updateValues = [
                    'message' => $this->get('core.helpers.secure')->strEncode($formData['message'])
                ];
                if ((empty($comment['user_id']) || $this->validator->is(IntegerValidationRule::class, $comment['user_id']) === false) &&
                    !empty($formData['name'])
                ) {
                    $updateValues['name'] = $this->get('core.helpers.secure')->strEncode($formData['name']);
                }

                $bool = $this->commentRepository->update($updateValues, $commentId);

                Core\Cache\Purge::doPurge($this->appPath->getCacheDir() . 'http');

                return $bool;
            },
            'acp/comments/details/index/id_' . $moduleId
        );
    }
}
