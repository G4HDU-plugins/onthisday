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

require_once ("../../class2.php");
if (!defined('e107_INIT'))
{
    exit;
}
e107::lan('onthisday', false, true);
e107::css('onthisday', 'css/onthisday.css'); // load css file

if (!is_object($otd_obj))
{
    require_once (e_PLUGIN . 'onthisday/includes/onthisday_class.php');
    $otd_obj = new onthisday;
}

//include_lan(e_PLUGIN . "onthisday/languages/" . e_LANGUAGE . ".php");
$e_wysiwyg = "otd_full";
if ($pref['wysiwyg'])
{
    $WYSIWYG = true;
}
require_once (HEADERF);
$otd_form = e107::getForm();
if ($otd_obj->canSubmit())
{
    if (!defined('USER_WIDTH'))
    {
        define(USER_WIDTH, "width:100%;");
    }
    require_once (e_HANDLER . "ren_help.php");
    $otd_currentmonths = explode(",", OTD_MONTHS);
    if (e_QUERY)
    {
        $otd_temp = explode(".", e_QUERY);
        $otd_action = $otd_temp[0];
        $otd_currentid = $otd_temp[1];
        $otd_currentmonth = $otd_temp[2];
        $otd_currentday = $otd_temp[3];
    } else
    {
        $otd_action = $_POST['otd_action'];
        $otd_currentid = $_POST['otd_currentid'];
        $otd_currentmonth = $_POST['otd_currentmonth'];
        $otd_currentday = $_POST['otd_currentday'];
    }
    var_dump($otd_action);
    if (!empty($_POST['dodel']))
    {
        $otd_action = 'dodel';
    }
    if (!empty($_POST['cancdel']))
    {
        $otd_action = 'show';
    }
    if (!isset($otd_action))
    {
        $otd_action = "show";
    }
    if (!isset($otd_currentmonth))
    {
        $otd_currentmonth = 1;
    }
    if ($otd_currentday < 1)
    {
        $otd_currentday = 1;
    }
    if ($otd_action == "dodel")
    {
        $otd_text = $otd_obj->dodel($otd_currentid, $otd_action, $otd_currentmonth, $otd_currentday);
        $otd_action = "show";
    }
    if ($otd_action == "delete")
    {
        $otd_text = $otd_obj->deleteit($otd_currentid, $otd_action, $otd_currentmonth, $otd_currentday);
    }
    if ($otd_action == "save" && intval($_POST['otd_id']) == 0)
    {
        $otd_obj->addNew($otd_currentid, $otd_action, $otd_currentmonth, $otd_currentday);
        $otd_action = "show";
    }
    if ($otd_action == "save")
    {
        $otd_obj->save();
        $otd_action = "show";
    }
    if ($otd_action == "add" || $otd_action == "edit")
    {
        $otd_text = $otd_obj->addedit($otd_currentid, $otd_action, $otd_currentmonth, $otd_currentday);
    }

    if ($otd_action == "show")
    {
        $otd_text .= $otd_obj->show();
            

    }
} else
{
    $otd_text = "Not permitted";
}
echo $otd_obj->msg->render();
$ns->tablerender(OTD_04, $otd_text);

require_once (FOOTERF);
