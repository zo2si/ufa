<?php

if (!function_exists('ufa')) {

    /**
     * Get ufa Instance
     * @param null $interface
     * @return \App\Ufa\Ufa
     */
    function ufa()
    {
        return app('UfaService');
    }
}
