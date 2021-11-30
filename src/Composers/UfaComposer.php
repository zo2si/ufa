<?php

namespace App\Ufa\Composers;

use Illuminate\Contracts\View\View;

//use Illuminate\Users\Repository as UserRepository;

class UfaComposer
{
    public function create(View $view)
    {
        ufa()->setName($view->name());
        $view_data = array_diff_key(ufa()->getData(), $view->getData());
        foreach ($view_data as $key => $value) {
            $view->with($key, $value);
        }
    }

    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        ufa()->setName($view->name());
        $view_data = array_diff_key(ufa()->getData(), $view->getData());
        foreach ($view_data as $key => $value) {
            $view->with($key, $value);
        }
    }
}