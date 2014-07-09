<?php

namespace ACP3\Modules\Minify\Controller;

use ACP3\Core;
use ACP3\Modules\Minify;

/**
 * Minify Index Controller
 *
 * @author Tino Goratsch
 */
class Index extends Core\Modules\Controller
{

    public function actionIndex()
    {
        $this->setNoOutput(true);

        if (!empty($this->uri->group)) {
            $libraries = !empty($this->uri->libraries) ? explode(',', $this->uri->libraries) : array();
            $layout = isset($this->uri->layout) && !preg_match('=/=', $this->uri->layout) ? $this->uri->layout : 'layout';

            $helper = $this->get('minify.helpers');

            $options = array();
            switch ($this->uri->group) {
                case 'css':
                    $files = $helper->includeCssFiles($libraries, $layout);
                    break;
                case 'js':
                    $files = $helper->includeJsFiles($libraries, $layout);
                    break;
                default:
                    $files = array();
            }
            $options['files'] = $files;
            $options['maxAge'] = CONFIG_CACHE_MINIFY;
            $options['minifiers']['text/css'] = array('Minify_CSSmin', 'minify');

            \Minify::setCache(new \Minify_Cache_File(UPLOADS_DIR . 'cache/minify/', true));
            \Minify::serve('Files', $options);
        }
    }

}
