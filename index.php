<?php

/**
 *  Plugin for the e107 Website System
 *
 * Copyright (C) 2008-2018 Barry Keal G4HDU (http://www.keal.me.uk)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once ("../../class2.php");

if (!defined('e107_INIT'))
{
    exit;
}
e107::lan('onthisday', false, true); // load English front or language file
if (!is_object($otd_obj))
{
    require_once (e_PLUGIN . 'onthisday/includes/onthisday_class.php');
    $otd_obj = new onthisday;
}

require_once (HEADERF);
if (!$otd_obj->canView())
{
    // $text = $otd_obj->renderMessage();
    $text .= $otd_obj->notPermitted();
} else
{
    // error_reporting(E_ALL);
    //print_a($_SESSION);

    $page .= $otd_obj->runPage();
    $render = $otd_obj->renderMessage();
    $text = $render . $page;
}
$ns->tablerender(e_PAGETITLE, $text); // Render the page
require_once (FOOTERF);
