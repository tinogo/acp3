<?php

namespace ACP3\Core\WYSIWYG;

use ACP3\Application;
use ACP3\Core\Registry;

/**
 * Implementation of the AbstractWYSIWYG class for a simple textarea
 * @package ACP3\Core\WYSIWYG
 */
class Textarea extends AbstractWYSIWYG
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    public function __construct($id, $name, $value = '', $toolbar = '', $advanced = false, $height = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->advanced = (bool)$advanced;
        $this->config['toolbar'] = $toolbar === 'simple' ? 'Basic' : 'Full';
        $this->config['height'] = $height . 'px';

        $this->container = Registry::get('services');
    }

    protected function configure()
    {
        return;
    }

    /**
     * @return string
     */
    public function display()
    {
        $out = '';
        if ($this->container->get('core.modules')->isActive('emoticons') === true) {
            $out .= $this->container->get('emoticons.helpers')->emoticonsList($this->id);
        }
        $out .= '<textarea name="' . $this->name . '" id="' . $this->id . '" cols="50" rows="6" class="span6">' . $this->value . '</textarea>';
        return $out;
    }

}