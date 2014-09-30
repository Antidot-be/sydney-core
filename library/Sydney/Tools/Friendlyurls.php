<?php

/**
 *
 * @author Arnaud Selvais
 * @since Tue 24 Apr 2012
 */
class Sydney_Tools_Friendlyurls extends Sydney_Tools
{
    /**
     * Returns a URL friendly URL
     *
     * @param int $id
     * @param string $label
     * @param string $type
     * @param Zend_View_Helper_Url $helper
     * @return string
     */
    public static function getFriendlyUrl($id, $label, $type = 'page', Zend_View_Helper_Url $helper)
    {
        $label = self::getUrlLabel($label);
        $routeName = 'pageRoute';

        return $helper->url(array('slug' => $label), $routeName, false, false);
    }

    /**
     * Returns an URL friendly string from a page label
     *
     * @param string $label
     * @return string
     */
    public static function getUrlLabel($label)
    {
        $str = trim(strtolower(preg_replace('/[^a-z 0-9-\/]/i', '',
            self::getNoaccentStr($label)
        )));
        $str = preg_replace('/ /', '-', $str); // On supprime les espaces

        return preg_replace('/-(?:-+)/', '-', $str); // On supprime les "-" consécutifs
    }

    /**
     * Returns a string without accents chars
     *
     * @param string $str
     * @param string $charset
     * @return string
     */
    public static function getNoaccentStr($str, $charset = 'utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str);

        return $str;
    }

    /**
     * Sets all the default routes for processing the friendly URLs
     *
     * @param Zend_Controller_Router_Interface $router
     */
    public static function setDefaultRoutes(Zend_Controller_Router_Interface $router)
    {
        $internalUrl = 'publicms|admin|default\/';
        // routeName => array( route, defaults, map, reverse )
        $routesRegex = array(
            'searchRoute'        => array(
                'search\.html',
                array(
                    'module'     => 'publicms',
                    'controller' => 'search',
                    'action'     => 'index'
                ),
                null,
                'search.html'
            ),
            'fileRoute'          => array(
                '(.*)FILE-([0-9]*)',
                array(
                    'module'     => 'publicms',
                    'controller' => 'file',
                    'action'     => 'getrfile'
                ),
                array(1 => 'slug', 2 => 'id'),
                '%sFILE-%s%s'
            ),
            'filedisplayedRoute' => array(
                'S([0-9]{1,4})I([0-9]{1,10})\.(png|jpg|gif)',
                array('module'     => 'publicms',
                      'controller' => 'file',
                      'action'     => 'showimg'
                ),
                array(1 => 'dw', 2 => 'id', 3 => 'ext'),
                'sfls%s-%s.%s'
            ),
            'pageRoute'          => array(
                '^((?!'.$internalUrl.').*)$',
                array(
                    'module'     => 'publicms',
                    'controller' => 'index',
                    'action'     => 'view'
                ),
                array(1 => 'slug'),
                '%s'
            ),
        );
        foreach ($routesRegex as $k => $v) {
            $router->addRoute($k, new Zend_Controller_Router_Route_Regex($v[0], $v[1], $v[2], $v[3]));
        }
        $router->addRoute('sydneydefault', new Zend_Controller_Router_Route('', array('module'     => 'publicms',
                                                                                      'controller' => 'index',
                                                                                      'action'     => 'view'
        )));
    }

}
