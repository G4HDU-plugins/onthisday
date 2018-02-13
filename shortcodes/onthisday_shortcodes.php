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
    function sc_OTD_CALENDAR()
    {
        return $this->otd_calendar;

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
        $retval = '
<span class="otdLink" >
    <a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $this->tomonth . '&day=' . $this->today . '" title="Today">
        <i class="fa fa-chevron-down fa-2x" aria-hidden="true"></i>
    </a>
</span>';
        return $retval;
    }
    function sc_OTD_DAY_TODAYSDATE()
    {
        //print_a($this->monthList);
        //print_a($this->month);
        return "" . $this->day . ' ' . $this->monthList[(int)$this->month] . '';
    }
    function sc_OTD_PREVIOUSMONTH()
    {
        $prevMonth = ($this->month == 1 ? 12 : $this->month - 1);

        $retval = '
<span class="otdLink" >
    <a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $prevMonth . '&day=' . $this->day . '" title="Last Month" >
        <i class="fa fa-angle-double-left fa-2x" aria-hidden="true"></i>
    </a>
</span>';
        return $retval;
    }
    function sc_OTD_PREVIOUSDAY()
    {
        $inMonth = $this->daysInMonth[$this->month];
        $prevDay = ($this->day == 1 ? $inMonth : $this->day - 1);
        $retval = '
<span class="otdLink" >
    <a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $this->month . '&day=' . $prevDay .'" title="Previous Day" >
        <i class="fa fa-angle-left fa-2x" aria-hidden="true"></i>
    </a>
</span>';
        return $retval;
    }
    function sc_OTD_NEXTMONTH()
    {
        $nextMonth = ($this->month == 12 ? 1 : $this->month + 1);
        $retval = '
<span class="otdLink" >
    <a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $nextMonth . '&day=' . $this->day .'" title="Next Month" >
        <i class="fa fa-angle-double-right fa-2x" aria-hidden="true"></i>
    </a>
</span>';
        return $retval;
    }
    function sc_OTD_NEXTDAY()
    {
        $inMonth = $this->daysInMonth[$this->month];
        $nextDay = ($this->day == $inMonth ? 1 : $this->day + 1);
        $retval = '
<span class="otdLink" >
    <a href="' . e_PLUGIN . 'onthisday/index.php?action=day&month=' . $this->month . '&day=' . $nextDay .'" title="Next Day">
        <i class="fa fa-angle-right fa-2x" aria-hidden="true"></i>
    </a>
</span>';
        return $retval;
    }
    function sc_OTD_CURRENTDAY()
    {
        $retval = $this->currentday;
        return $retval;
    }
    function sc_OTD_CURRENTMONTH()
    {
        $retval = $this->currentmonth;
        return $retval;
    }
    function sc_OTD_BRIEF()
    {
        $retval = $this->otd_brief;
        return $retval;
    }
    function sc_OTD_DAY()
    {
        $retval = $this->otd_day;
        return $retval;
    }
    function sc_OTD_MONTH()
    {
        $retval = $this->otd_month;
        return $retval;
    }
    function sc_OTD_EDYEAR()
    {
        $retval = $this->otd_year;
        return $retval;
    }
    function sc_OTD_FULL()
    {
        $retval = $this->otd_full;
        return $retval;
    }
    function sc_OTD_SUBMIT()
    {
        $retval = $this->otd_submit;
        return $retval;
    }
    function sc_OTD_EDIT()
    {
        $retval =   "<a href='" . e_SELF . "?action=edit&id={$this->row['otd_id']}&calMonth={$this->calMonth}&calDay={$this->calDay}'  title='" . OTD_013 . "'><i class='fa fa-edit   fa-2x'></i></a>";

        return $retval;
    }
    function sc_OTD_DELETE()
    {
        $retval = "<a href='" . e_SELF . "?action=delete&id={$this->row['otd_id']}&calMonth={$this->calMonth}&calDay={$this->calDay}'  title='" . OTD_014 . "' ><i class='fa fa-trash fa-2x'></i></a>";
        return $retval;
    }
    function sc_OTD_ADDNEW(){
        $retval="<a href='" . e_SELF . "?action=add&id=0&from=0&calMonth={$this->calMonth}&calDay={$this->calDay}'>" . OTD_A21 . "</a>";
        return $retval;
        }
}
