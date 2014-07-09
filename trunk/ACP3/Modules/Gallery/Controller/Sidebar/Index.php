<?php

namespace ACP3\Modules\Gallery\Controller\Sidebar;

use ACP3\Core;
use ACP3\Modules\Gallery;

/**
 * Description of GalleryFrontend
 *
 * @author Tino Goratsch
 */
class Index extends Core\Modules\Controller\Sidebar
{

    /**
     *
     * @var Gallery\Model
     */
    protected $model;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->model = new Gallery\Model($this->db);
    }

    public function actionIndex()
    {
        $formatter = $this->get('core.helpers.string.formatter');
        $config = new Core\Config($this->db, 'gallery');
        $settings = $config->getSettings();

        $galleries = $this->model->getAll($this->date->getCurrentDateTime(), $settings['sidebar']);
        $c_galleries = count($galleries);

        if ($c_galleries > 0) {
            for ($i = 0; $i < $c_galleries; ++$i) {
                $galleries[$i]['start'] = $this->date->format($galleries[$i]['start']);
                $galleries[$i]['title_short'] = $formatter->shortenEntry($galleries[$i]['title'], 30, 5, '...');
            }
            $this->view->assign('sidebar_galleries', $galleries);
        }

        $this->setLayout('Gallery/Sidebar/index.index.tpl');
    }

}