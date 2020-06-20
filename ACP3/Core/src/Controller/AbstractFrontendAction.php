<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Controller;

use ACP3\Core;
use ACP3\Core\Controller\Event\CustomTemplateVariableEvent;

abstract class AbstractFrontendAction extends Core\Controller\AbstractWidgetAction
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var string
     */
    private $layout = 'layout.tpl';

    public function __construct(Context\FrontendContext $context)
    {
        parent::__construct($context);

        $this->eventDispatcher = $context->getEventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    protected function addCustomTemplateVarsBeforeOutput()
    {
        $this->view->assign('LAYOUT', $this->fetchLayoutViaInheritance());

        $this->eventDispatcher->dispatch(
            new CustomTemplateVariableEvent($this->view),
            CustomTemplateVariableEvent::NAME
        );
    }

    protected function fetchLayoutViaInheritance(): string
    {
        if ($this->request->isXmlHttpRequest()) {
            $paths = $this->fetchLayoutPaths('layout.ajax', 'System/layout.ajax.tpl');
        } else {
            $paths = $this->fetchLayoutPaths('layout', 'layout.tpl');
        }

        $this->iterateOverLayoutPaths($paths);

        return $this->getLayout();
    }

    private function fetchLayoutPaths(string $layoutFileName, string $defaultLayoutName): array
    {
        return [
            $this->request->getModule() . '/' . $this->request->getArea() . '/' . $layoutFileName . '.' . $this->request->getController() . '.' . $this->request->getAction() . '.tpl',
            $this->request->getModule() . '/' . $this->request->getArea() . '/' . $layoutFileName . '.' . $this->request->getController() . '.tpl',
            $this->request->getModule() . '/' . $this->request->getArea() . '/' . $layoutFileName . '.tpl',
            $this->request->getModule() . '/' . $layoutFileName . '.tpl',
            $defaultLayoutName,
        ];
    }

    /**
     * @param string[] $paths
     */
    private function iterateOverLayoutPaths(array $paths): void
    {
        if ($this->getLayout() !== 'layout.tpl') {
            return;
        }

        foreach ($paths as $path) {
            if ($this->view->templateExists($path)) {
                $this->setLayout($path);

                break;
            }
        }
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return $this
     */
    public function setLayout(string $layout)
    {
        $this->layout = $layout;

        return $this;
    }
}
