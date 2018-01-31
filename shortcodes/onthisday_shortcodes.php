<?php

/*
* +---------------------------------------------------------------+
* |        Onthisday Menu for e107 v7xx - by Father Barry
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
include_once (e_HANDLER . 'shortcode_handler.php');
class otdShortcode extends e_shortcode
{
    function __construct()
    {

    }
    function sc_OTD_YEAR()
    {
        if ($this->row['otd_year'] > 0)
        {
            return $this->row['otd_year'];
        } else
        {
            return "&nbsp;";
        }

    }
    function sc_OTD_TITLE()
    {
        return $this->row['otd_brief'];
    }
    function sc_OTD_BODY()
    {

        if (!empty($this->row['otd_full']))
        {
            return '
	<tr>
		<td class="forumheader3">
            <div class="otdYear">&nbsp;&nbsp;&nbsp;&nbsp;</div> - <span class="otdItalic" >' . $this->row['otd_full'] . '</span>
        </td>
	</tr>';
        } else
        {
            return '';
        }
    }
    function sc_OTD_TODAY()
    {
        $retval = '<span class="otdLink" ><a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $this->tomonth . '&day=' . $this->today .
            '" title="Today"><i class="fa fa-chevron-down" aria-hidden="true"></i></a></span>';
        return $retval;
    }
    function sc_OTD_DAY_TODAYSDATE()
    {

        return $this->day . ' ' . $this->monthList[$this->month];
    }
    function sc_OTD_PREVIOUSMONTH()
    {
        $prevMonth = ($this->month == 1 ? 12 : $this->month - 1);

        $retval = '<span class="otdLink" ><a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $prevMonth . '&day=' . $this->day .
            '" title="Last Month" ><i class="fa fa-angle-double-left" aria-hidden="true"></i></a></span>';
        return $retval;
    }
    function sc_OTD_PREVIOUSDAY()
    {
        $inMonth = $this->daysInMonth[$this->month];
        $prevDay = ($this->day == 1 ? $inMonth : $this->day - 1);
        $retval = '<span class="otdLink" ><a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $this->month . '&day=' . $prevDay .
            '" title="Previous Day" ><i class="fa fa-angle-left" aria-hidden="true"></i></a></span>';
        return $retval;
    }
    function sc_OTD_NEXTMONTH()
    {
        $nextMonth = ($this->month == 12 ? 1 : $this->month + 1);
        $retval = '<span class="otdLink" ><a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $nextMonth . '&day=' . $this->day .
            '" title="Next Month" ><i class="fa fa-angle-double-right" aria-hidden="true"></i></a></span>';
        return $retval;
    }
    function sc_OTD_NEXTDAY()
    {
        $inMonth = $this->daysInMonth[$this->month];
        $nextDay = ($this->day == $inMonth ? 1 : $this->day + 1);
        $retval = '<span class="otdLink" ><a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $this->month . '&day=' . $nextDay .
            '" title="Next Day"><i class="fa fa-angle-right" aria-hidden="true"></i></a></span>';
        return $retval;
    }
}
//$otd_shortcodes = $tp->e_sc->parse_scbatch(__FILE__);
// * start shortcodes
/*

* SC_BEGIN OTD_DAY_TITLE
* global $title;
* return $title;
* SC_END

* SC_BEGIN OTD_YEAR
* global $otd_year;
* return $otd_year;
* SC_END

* SC_BEGIN OTD_TITLE
* global $otd_title;
* return $otd_title;
* SC_END

* SC_BEGIN OTD_BODY
* global $otd_body;
* return $otd_body;
* SC_END

* SC_BEGIN OTD_MANAGE
* global $otd_obj;
* if($otd_obj->otd_submit)
* {
* return '<a href="'.e_PLUGIN.'onthisday/manage_entries.php">'.OTD_001.'</a>';
* }
* else
* {
* return '';
* }
* SC_END

* SC_BEGIN OTD_CALENDAR
* global $otd_obj,$otd_thisday,$otd_thismonth;

* return $otd_obj->otd_calendar($otd_thismonth,$otd_thisday);
* SC_END
*/
