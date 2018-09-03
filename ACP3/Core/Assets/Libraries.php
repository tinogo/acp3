<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets;

use ACP3\Core\Assets\Event\AddLibraryEvent;
use ACP3\Core\Http\RequestInterface;
use MJS\TopSort\Implementations\StringSort;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Libraries
{
    /**
     * @var array
     */
    protected $libraries = [
        'ajax-form' => [
            'enabled' => true,
            'enabled_for_ajax' => false,
            'dependencies' => ['bootstrap', 'jquery'],
            'js' => 'ajax-form.js',
        ],
        'bootbox' => [
            'enabled' => false,
            'dependencies' => ['bootstrap'],
            'js' => 'bootbox.js',
        ],
        'bootstrap' => [
            'enabled' => false,
            'dependencies' => ['jquery'],
            'css' => 'bootstrap.min.css',
            'js' => 'bootstrap.min.js',
        ],
        'datatables' => [
            'enabled' => false,
            'dependencies' => ['bootstrap'],
            'css' => 'dataTables.bootstrap.css',
            'js' => 'jquery.dataTables.js',
        ],
        'datetimepicker' => [
            'enabled' => false,
            'dependencies' => ['jquery', 'moment'],
            'css' => 'bootstrap-datetimepicker.css',
            'js' => 'bootstrap-datetimepicker.min.js',
        ],
        'fancybox' => [
            'enabled' => false,
            'dependencies' => ['jquery'],
            'css' => 'jquery.fancybox.css',
            'js' => 'jquery.fancybox.min.js',
        ],
        'font-awesome' => [
            'enabled' => false,
            'css' => [
                'brands.css',
                'regular.css',
                'solid.css',
                'fontawesome.css',
            ],
        ],
        'jquery' => [
            'enabled' => true,
            'enabled_for_ajax' => false,
            'js' => 'jquery.min.js',
        ],
        'js-cookie' => [
            'enabled' => false,
            'enabled_for_ajax' => false,
            'js' => 'js.cookie.js',
        ],
        'moment' => [
            'enabled' => false,
            'js' => 'moment.min.js',
        ],
    ];
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    private $request;

    /**
     * Libraries constructor.
     *
     * @param \ACP3\Core\Http\RequestInterface $request
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        RequestInterface $request,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->request = $request;
    }

    public function dispatchAddLibraryEvent(): void
    {
        $this->eventDispatcher->dispatch('core.assets.add_libraries', new AddLibraryEvent($this));
    }

    /**
     * @return array
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function getLibraries(): array
    {
        $topSort = new StringSort();
        foreach ($this->libraries as $library => $options) {
            $topSort->add($library, $options['dependencies'] ?? []);
        }

        $librariesTopSorted = [];
        foreach ($topSort->sort() as $library) {
            $librariesTopSorted[$library] = $this->libraries[$library];
        }

        return $librariesTopSorted;
    }

    /**
     * @param string $identifier
     * @param array  $options
     *
     * @return $this
     */
    public function addLibrary(string $identifier, array $options): self
    {
        if (!isset($this->libraries[$identifier])) {
            $this->libraries[$identifier] = $options;
        }

        if (isset($options['enabled']) && $options['enabled'] === true) {
            $this->enableLibraries($options['dependencies'] ?? []);
        }

        return $this;
    }

    /**
     * Activates frontend libraries.
     *
     * @param array $libraries
     *
     * @return $this
     */
    public function enableLibraries(array $libraries): self
    {
        foreach ($libraries as $library) {
            if (\array_key_exists($library, $this->libraries) === true) {
                // Resolve javascript library dependencies recursively
                if (!empty($this->libraries[$library]['dependencies'])) {
                    $this->enableLibraries($this->libraries[$library]['dependencies']);
                }

                // Enable the javascript library
                $this->libraries[$library]['enabled'] = true;
            }
        }

        return $this;
    }

    /**
     * @return array
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function getEnabledLibraries(): array
    {
        $enabledLibraries = [];
        foreach ($this->getLibraries() as $library => $options) {
            if ($this->includeInXmlHttpRequest($options)) {
                continue;
            }
            if ($options['enabled'] === false) {
                continue;
            }

            $enabledLibraries[] = $library;
        }

        return $enabledLibraries;
    }

    /**
     * @param array $values
     *
     * @return bool
     */
    private function includeInXmlHttpRequest(array $values): bool
    {
        return $this->request->isXmlHttpRequest()
            && isset($values['enabled_for_ajax'])
            && $values['enabled_for_ajax'] === false;
    }
}
