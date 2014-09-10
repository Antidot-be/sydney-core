<?php

/**
 * Helper permettant la gestion unifi� des urls
 * @author JTO
 * @since 04/02/2014
 */
class Sydney_View_Helper_GetUrl extends Zend_View_Helper_Url
{

    /**
     * Retourne l'url généré d'un noeud en fonction des parametres fournis dans le fichier de config
     * @author JTO
     * @since 04/02/2014
     * @param array $node
     * @return string
     */
    public function getUrl($node)
    {
        $helper = new Sydney_View_Helper_SydneyUrl();

        return $helper->SydneyUrl($node['id'], $node['url']);
    }
}
