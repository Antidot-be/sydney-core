<?php

class Sydney_Tools_Modulesinfo
{

    public function __construct()
    {

    }

    /**
     * Returns an array containing the list of modules as key and controllers in a sub array
     * Example:
     * <code>
     * Array
     * (
     *     [admin] => Array
     *         (
     *             [0] => Index
     *             [1] => Jscripts
     *             [2] => Servicesfolder
     *         )
     *
     * </code>
     * @param string $modulesPath
     * @return array
     */
    public function getModulesAndControllersList($introspect = false, $modulesPath = '/core/application/modules/')
    {
        $rdir = Sydney_Tools_Paths::getCorePath() . '/../' . $modulesPath;
        $dirs = array();
        // get the module directory
        if ($handle = opendir($rdir)) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match('/^\./', $file)) {
                    $dirs[$file] = array();
                    if ($handle2 = opendir($rdir . $file . '/controllers/')) {
                        while (false !== ($cntrl = readdir($handle2))) {
                            if (!preg_match('/^\./', $cntrl)) {
                                $cnl = preg_split('/Controller.php/', $cntrl);
                                if (count($cnl) == 2) {
                                    if ($introspect) {
                                        $dirs[$file][($cnl[0])] = $this->introspect($file, $cnl[0], $rdir . $file . '/controllers/' . $cntrl);
                                    } else {
                                        $dirs[$file][] = $cnl[0];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }

        return $dirs;
    }

    /**
     * Returns the list of actions and their documentation from a controller class
     *
     * @param string $module
     * @param string $controller
     * @return array
     */
    public function introspect($module, $controller, $filepath, $allMethods = false, $showPrivate = false)
    {
        $className = ucfirst($module) . '_' . ucfirst($controller) . 'Controller';
        //return $filepath.' '.$className;
        $toret = array();
        if (file_exists($filepath)) {
            if (!preg_match('/(admincloudfiles|ErrorController|LoginController|default)/', $filepath)) {
                include_once($filepath);
                //$obj = new $className;
                $reflection = new ReflectionClass($className);
                foreach ($reflection->getMethods() as $method) {
                    if ($showPrivate || (!$showPrivate && $method->isPublic())) {
                        if ($allMethods) {
                            $toret[] = $method->name;
                        } elseif (preg_match('/Action$/', $method->name)) {
                            $toret[$method->name]['doc'] = $method->getDocComment();
                        }
                    }
                }

                return array('doc'     => $reflection->getDocComment(),
                             'methods' => $toret
                );
            }
        }

        return array('doc' => '', 'methods' => $toret);
    }

    /**
     *
     */
    public function getWebinstancesList()
    {

    }

}
