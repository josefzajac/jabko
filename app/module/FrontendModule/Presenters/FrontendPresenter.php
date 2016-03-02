<?php

namespace App\FrontendModule\Presenters;

class FrontendPresenter extends LoginPresenter
{

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->gulpVersion = $this->cache->load('gulp-time');
    }
}
