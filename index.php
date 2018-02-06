<?php

/*
* +---------------------------------------------------------------+
* |        On This Day Menu for e107 v7xx - by Father Barry
* |
* |        This module for the e107 .7+ website system
* |        Copyright Barry Keal 2004-2008
* |
* |        Released under the terms and conditions of the
* |        GNU General Public License (http://gnu.org).
* |
* +---------------------------------------------------------------+
*/

require_once ("../../class2.php");
//error_reporting(E_ALL);
e107::lan('onthisday', false, true); // load English front


if (!defined('e107_INIT'))
{
    exit;
}
if (!is_object($otd_obj))
{

    require_once (e_PLUGIN . 'onthisday/includes/onthisday_class.php');

    $otd_obj = new onthisday;
}

require_once (HEADERF);
$text = $otd_obj->runPage();
$ns->tablerender(e_PAGETITLE, $text); // Render the page
require_once (FOOTERF);
exit;
// include the appropriate language file, if possible


$captiondate = $otd_thisday . ' ' . $otd_currentmonth[$otd_thismonth];
$title = OTDLAN_CAP . " " . $captiondate;
if ($otd_obj->otd_read)
{
    if ($sql->db_Select("onthisday", "*", "where otd_day = {$otd_thisday} and otd_month = {$otd_thismonth} order by otd_year", 'nowhere', false))
    {
        // todo: template show all from prefs
        $text .= $tp->parsetemplate($this->template->otdDayHead(), true, $otd_shortcodes);
        // Events occured on this day
        while ($item = $sql->db_Fetch())
        {
            extract($item);
            $otd_year = ($otd_year > 0 ? OTD_03 . " " . $otd_year : OTD_02);
            $otd_title = $tp->toHTML($otd_brief, true, "emotes_on no_replace");
            $otd_body = $tp->toHTML($otd_full, true, "emotes_on no_replace");

            $text .= $tp->parsetemplate($this->template->otdDayDetail(), true, $otd_shortcodes);
        } // while;
        $text .= $tp->parsetemplate($this->template->otdDayFooter(1), true, $otd_shortcodes);
    } else
    {
        $text .= $tp->parsetemplate($this->template->otdNoRec(), true, $otd_shortcodes);
    }
} else
{
    // Not in correct class
    $text .= $tp->parsetemplate($OTD_DAY_NOTPERMITTED, true, $otd_shortcodes);
}

$ns->tablerender(e_PAGETITLE, $text); // Render the page

require_once (FOOTERF);
