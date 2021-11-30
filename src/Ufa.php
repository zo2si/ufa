<?php namespace App\Ufa;

use Config;
use Request;

class Ufa
{
    // START: new functions when unifying.
    const SOURCE_EXTERNAL = 'external';
    const SOURCE_INTERNAL = 'internal';

    public $debug = false;
    public $dest_dir = 'dist/';
    public $compatible_ie = false;
    public $data = [];
    public $manifest = [];

    private $default = 'mobile';
    private $name = 'page';
    private $params = array();

    private $external_resources = array(
        'js'  => array(),
        'css' => array(),
    );
    private $internal_resources = array(
        'js'  => array(),
        'css' => array(),
    );

    function __construct()
    {
        $this->debug = Config::get('ufa.debug');
        $this->compatible_ie = Config::get('ufa.compatible_ie', false);
        $host = Config::get('ufa.host', '/');
        $debug_dir = Config::get('ufa.debugdir', 'www/');
        $built_dir = Config::get('ufa.builtdir', 'dist/');
        $this->dest_dir = $host . ($this->debug ? $debug_dir : $built_dir);
        // init manifest
        $this->manifest = $this->getManifest();
    }

    /**
     * Get client type.
     * @return string: browser/mobile/wechat/app
     */
    public function clientType()
    {
        $env_weiliao = 'weiliao';
        $env_app = 'app';
        $env_touch = 'touch';
        $env_wechat = 'wechat';
        $env_browser = 'browser';
        $env_mobile = 'mobile';

        $user_agent = Request::header('User-Agent');
        $client = $env_browser;

        // App
        if (preg_match('/ClientType\/APP/', $user_agent) || $env_weiliao === Request::get('from')) {
            return $env_app;
        }

        // Mobile
        if (preg_match('/Mobile/', $user_agent)) {
            // wechat
            $client = $env_mobile;
            if (preg_match('/MicroMessenger|NetType/', $user_agent)) {
                $client = $env_wechat;
            }
        }

        return $client;
    }


    /**
     * Get normal path
     * @param $from string, 'js/''
     * @param $to string,  '../lib/list.js'
     * @return string 'lib/list.js'
     */
    private function _normalizePath($from, $to)
    {
        $path = $from . $to;
        $path = str_replace('../', '', $path, $count);
        $parts = explode('/', $path);
        $relative_path = implode('/', array_slice($parts, $count));
        return $relative_path;
    }

    public function realPath($path, $resource_type = 'js', $dest_dir = null)
    {
        $path .= '.' . $resource_type;
        $basedir = $resource_type . '/';
        $asset_manifest = $this->_normalizePath($basedir, $path);

        $hashfile = $this->hashmap('/' . $asset_manifest);


        $dest_dir = (null != $dest_dir) ? $dest_dir : $this->dest_dir;
        $hashfile = ltrim($hashfile, '\/');

        return $dest_dir . $hashfile;
    }

    public function asset($path)
    {
        return $this->dest_dir . $this->hashmap($path);
    }

    public function setName($name)
    {
        if (!$this->name) {
            $this->name = $name;
        }
    }

    public function getCompatible()
    {
        return $this->compatible_ie;
    }

    /**
     * Get suffix of resource.
     * @param $type
     * @return string. e.g.: app, mobile
     */
    private function get_suffix($type)
    {
        $type = $type ? $type : $this->default;
        $suffix = ($type === $this->default) ? '' : $type;
        return $suffix;
    }

    /**
     * Add Resources.
     *
     * @param $source : external or internal.
     * @param $resource_type : js or css.
     * @param $data
     * @param $client_type : app or mobile.
     */
    public function addResources($source, $resource_type, $data, $client_type)
    {
        if (!empty($data)) {
            if ($source === self::SOURCE_INTERNAL) {
                $resources = &$this->internal_resources[$resource_type];
            } else {
                $resources = &$this->external_resources[$resource_type];
            }

            $client_type = $client_type ? $client_type : $this->default;
            $suffix = self::get_suffix($client_type);

            if ($suffix) {
                foreach ($data as &$val) {
                    $val .= '.' . $suffix;
                }
            }

            $resources[$client_type] = isset($resources[$client_type]) ? $resources[$client_type] : array();
            $resources[$client_type] = array_unique(array_merge(($resources[$client_type]), $data), SORT_REGULAR);

        }
    }

    /**
     * Get resources.
     *
     * @param        $source
     * @param        $resource_type
     * @param string $client_type
     * @return array
     */
    public function getResources($source, $resource_type, $client_type = '')
    {

        if ($source === self::SOURCE_INTERNAL) {
            $resources = &$this->internal_resources[$resource_type];
        } else {
            $resources = &$this->external_resources[$resource_type];
        }

        $client_type = $client_type ? $client_type : $this->default;

        return isset($resources[$client_type]) ? $resources[$client_type] : array();
    }

    /**
     * Get all loading resources.
     * @param string $client_type
     * @param bool   $is_pure
     * @return array
     */
    public function loadResources($client_type = '', $is_pure = false)
    {

        $all_resources = [
            'js'  => self::loadScripts($client_type, $is_pure),
            'css' => self::loadStyles($client_type, $is_pure),
        ];

        return $all_resources;
    }

    public function loadStyles($client_type = '', $is_pure = false)
    {
        $resources = [
            'internal' => $this->_loadTool(self::SOURCE_INTERNAL, 'css', $client_type, $is_pure),
            'external' => $this->_loadTool(self::SOURCE_EXTERNAL, 'css', $client_type, $is_pure),
        ];
        $this->_suffixTool($resources['internal'], 'css');
        $this->_suffixTool($resources['external'], 'css');
        return $resources;
    }

    public function loadScripts($client_type = '', $is_pure = false)
    {
        $resources = [
            'internal' => $this->_loadTool(self::SOURCE_INTERNAL, 'js', $client_type, $is_pure),
            'external' => $this->_loadTool(self::SOURCE_EXTERNAL, 'js', $client_type, $is_pure),
        ];
        $this->_suffixTool($resources['internal'], 'js');
        $this->_suffixTool($resources['external'], 'js');
        return $resources;
    }

    /**
     * Private: add suffix for each loading file.
     * @param $resources
     * @param $resource_type
     * @return mixed
     */
    private function _suffixTool(&$resources, $resource_type)
    {
        foreach ($resources as &$val) {
            $val = $this->realPath($val, $resource_type);
        }
        return $resources;
    }

    /**
     * Private: get loading resources(without suffix)
     * @param      $source
     * @param      $resource_type
     * @param      $client_type
     * @param bool $is_pure
     * @return array
     */
    private function _loadTool($source, $resource_type, $client_type, $is_pure = false)
    {
        if ($is_pure) {
            return $this->getResources($source, $resource_type, $client_type);
        }
        $default = $client_type ? $client_type : $this->default;
        if ($default != $this->default) {
            $resources = array_unique(array_merge(
                $this->getResources($source, $resource_type, ''),
                $this->getResources($source, $resource_type, $client_type)
            ), SORT_REGULAR);
        } else {
            $resources = $this->getResources($source, $resource_type, $default);
        }

        return $resources;
    }

    /**
     * @param        $resource_type
     * @param array  $data
     * @param string $client_type
     */
    public function addExternals($resource_type, $data = array(), $client_type = '')
    {
        self::addResources(self::SOURCE_EXTERNAL, $resource_type, $data, $client_type);
    }

    /**
     * @param $resource_type string js or css.
     * @param $client_type string mobile or wechat .etc.
     * @return array
     */
    public function getExternalResources($resource_type, $client_type = '')
    {
        return self::getResources(self::SOURCE_EXTERNAL, $resource_type, $client_type);
    }

    /**
     * @param        $resource_type
     * @param array  $data
     * @param string $client_type
     */
    public function addInternalResources($resource_type, $data = array(), $client_type = '')
    {
        self::addResources(self::SOURCE_INTERNAL, $resource_type, $data, $client_type);
    }

    /**
     * @param $resource_type string js or css.
     * @param $client_type string mobile or wechat .etc.
     * @return array
     */
    public function getInternalResources($resource_type, $client_type = '')
    {
        return self::getResources(self::SOURCE_INTERNAL, $resource_type, $client_type);
    }

    /**
     * @param array  $data
     * @param string $type client type.
     */
    public function addExternalJs($data = array(), $type = '')
    {
        self::addExternals('js', $data, $type);
    }

    /**
     * Alias for function addExternalJs
     * @param array  $data
     * @param string $type client type.
     */
    public function extJs($data = array(), $type = '')
    {
        self::addExternals('js', $data, $type);
    }


    /**
     * @param array  $data
     * @param string $type client type.
     */
    public function addExternalCss($data = array(), $type = '')
    {
        self::addExternals('css', $data, $type);
    }

    /**
     * Alias for function addExternalCss
     * @param array  $data
     * @param string $type client type.
     */
    public function extCss($data = array(), $type = '')
    {
        self::addExternals('css', $data, $type);
    }

    /**
     * @param array  $data
     * @param string $type client type.
     */
    public function addInternalCss($data = array(), $type = '')
    {
        self::addInternalResources('css', $data, $type);
    }

    /**
     * @param array  $data
     * @param string $type client type.
     */
    public function addInternalJs($data = array(), $type = '')
    {
        self::addInternalResources('js', $data, $type);
    }

    /**
     * Add parameters.
     * @param        $value
     * @param string $key
     */
    public function addParam($value, $key = '')
    {
        if ($key) {
            $param = isset($this->params[$key]) ? $this->params[$key] : array();
            $this->params[$key] = array_merge($param, $value);
        } else {
            $params = array_merge($this->params, $value);
            $this->params = $params;
        }
    }

    /**
     * Get specified parameter.
     * @param $key
     * @return array
     */
    public function getParam($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : array();
    }

    /**
     * Get all parameters.
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getManifest()
    {
        $file = @file_get_contents(public_path($this->dest_dir . 'mix-manifest.json'));
        return json_decode($file, true);
    }

    public function hashmap($path)
    {
        return isset($this->manifest[$path]) ? $this->manifest[$path] : $path;
    }


    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     *
     * @param $name
     * @param $args
     */
    public function __call($name, $args)
    {

    }

    /**
     * >= PHP 5.3.0
     * @param $name
     * @param $args
     */
    public static function __callStatic($name, $args)
    {
    }

    // END: new functions when unifying.

    // functions below in CRM, Agent, TW, PC, Bureau
}