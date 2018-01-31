<?php

/*
* +---------------------------------------------------------------+
* |        On This Day Menu for e107 v7xx - by Father Barry
* |
* |        This module for the e107 .7+ website system
* |        Copyright Barry Keal 2004-2009
* |
* |        Released under the terms and conditions of the
* |        GNU General Public License (http://gnu.org).
* |
* +---------------------------------------------------------------+
*/
if (!defined('e107_INIT'))
{
    exit;
}
e107::lan('onthisday', 'menu', true);
if (!is_object($otd_obj))
{
    require_once (e_PLUGIN . 'onthisday/includes/onthisday_class.php');
    $otd_obj = new onthisday;
}

if ($otd_obj->canView())
{
    $menuCache = "nq_otdmenu";
    $otdCache = "otd_display";

    // retrieve the cached menu
    $cached = e107::getCache()->retrieve($menuCache); // get the cache contents
    if (false === $cached)
    {
        // there is no cache
        // so create the cache entry
        //and the menu entry
        $cached = $otd_obj->showMenu();
        e107::getCache()->set($menuCache, $cached); // and save it in the cache
    } 
    echo $cached;
}
