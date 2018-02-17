<?php

if (!defined('e107_INIT'))
{
    exit;
}
if (!defined('USER_WIDTH'))
{
    define(USER_WIDTH, "width:100%;");
} #
//lang files for this template in Xxx_front.php
global $otd_shortcodes;
class onthisday_template
{
    private $showAll;
    function __construct($showAll = 0)
    {
        $this->showAll = $showAll;
    }

    function otdDayHead()
    {
        $retval = '
<table class="fborder" style="' . USER_WIDTH . '">';
        if (defined('OTD_LOGO'))
        {
            $retval .= '
    <tr>
        <td class="forumheader2 otdLogoCell" >
            <img src="' . OTD_LOGO . '" class="otdLogo" alt="logo" title="logo" />
        </td>
	</tr>';
        }
        $retval .= '

	<tr>
   		<td class="forumheader3" >{OTD_PREVIOUSMONTH}{OTD_PREVIOUSDAY}{OTD_TODAY}{OTD_NEXTDAY}{OTD_NEXTMONTH}</td>
	</tr>
   	<tr>
   		<td class="forumheader3" >{OTD_DAY_TODAYSDATE}</td>
	</tr>';
        return $retval;
    }
    function otdDayDetail()
    {
        $retval = '
	<tr>
		<td class="forumheader3">
            <div class="otdYear">{OTD_YEAR}</div> - <span class="otdBold" >{OTD_TITLE}</span>
        </td>
	</tr>
    {OTD_BODY}';
        return $retval;
    }

    function otdDayFoot()
    {

            $retval = '
            <!--
	<tr>
		<td class="forumheader3 otdCalendar" >{OTD_CALENDAR}</td>
	</tr>
    <tr>
		<td class="forumheader3">{OTD_MANAGE}</td>
	</tr>
	<tr>
		<td class="fcaption">&nbsp;</td>
	</tr>-->
</table>';
        return $retval;
    }
    function otdNoRec()
    {
        $retval = '
<table class="fborder" style="' . USER_WIDTH . '">
    <tr>
   		<td class="fcaption">{OTD_DAY_TITLE}</td>
	</tr>
	<tr>
		<td class="forumheader3">' . OTDLAN_DEFAULT . '</td>
	</tr>
	<!--<tr>
		<td class="forumheader3">{OTD_CALENDAR}</td>
	</tr>
    
    <tr>
		<td class="forumheader3">{OTD_MANAGE}</td>
	</tr>
-->	<tr>
		<td class="fcaption">&nbsp;</td>
	</tr>
</table>';
        return $retval;
    }
    function otdNotPermitted()
    {
        $retval = '
<table class="fborder" style="' . USER_WIDTH . '">
   	<tr>
   		<td class="fcaption">{OTD_DAY_TITLE}</td>
	</tr>
	<tr>
		<td class="forumheader3">' . OTD_01 . '</td>
	</tr>
	<tr>
		<td class="fcaption">&nbsp;</td>
	</tr>
</table>';
        return $retval;
    }
    function editEntry()
    {
        $retval = "
<table class='fborder' style='" . USER_WIDTH . "'>
	<tr>
		<td colspan='2' class='forumheader2'>" . OTD_A11 . " {OTD_CURRENTDAY} {OTD_CURRENTMONTH} </td>
	</tr>
	<tr>
		<td class='forumheader3'>" . OTD_A12 . "</td>
		<td class='forumheader3'>
			{OTD_BRIEF}
		</td>
	</tr>
	<tr>
		<td class='forumheader3'>" . OTD_A17 . "</td>
		<td class='forumheader3'>
		 " . OTD_A13 . " {OTD_DAY}&nbsp;&nbsp;&nbsp;
		 " . OTD_A14 . " {OTD_MONTH} &nbsp;&nbsp;&nbsp;
		 " . OTD_A15 . " {OTD_EDYEAR} 
		</td>
	</tr>
	<tr>
		<td class='forumheader3'>" . OTD_A16 . "</td>
		<td class='forumheader3'>{OTD_FULL}</td>
	</tr>
	<tr>
		<td class='forumheader3' colspan='2'>
			{OTD_SUBMIT}
		</td>
	</tr>
   	<tr>
		<td class='fcaption' colspan='2'>&nbsp;</td>
	</tr>
</table>";
        return $retval;
    }
    function showHead()
    {
        $otd_text .= "
<table class='fborder' style='" . USER_WIDTH . "'>
	<tr>
		<td class='forumheader3'  style='width:100%;text-align:center;'>{OTD_CALENDAR}</td>
	</tr>
</table>";

        $otd_selmonth = $otd_currentmonth;
        $otd_text .= "
<table class='fborder' style='" . USER_WIDTH . "'>
	<tr>
		<td class='fcaption' colspan='2'>" . OTD_A24 . " - <strong>$this->calDay " . $otd_currentmonths[$otd_currentmonth] . "</strong></td>
	</tr>";

        return $otd_text;
    }
    function showRow()
    {
        $row = $this->row;
        $otd_text .= "
	<tr>
		<td class='forumheader3' style='width:80%'>{OTD_TITLE}</td>
		<td class='forumheader3' style='width:20%;text-align:center;'>
			{OTD_EDIT}&nbsp;&nbsp;{OTD_DELETE}
        </td>
	</tr>";
        return $otd_text;
    }
    function showNoRow()
    {
        $otd_text .= "
	<tr>
		<td class='forumheader3' colspan='2'>" . OTD_A25 . "</td>
	</tr>";

    }
    function showFoot()
    {
        $retval .= "
	<tr>
		<td class='forumheader3' colspan='2'>{OTD_ADDNEW}</td>
	</tr>
	<tr>
		<td class='fcaption' colspan='2'>&nbsp;</td>
	</tr>
</table>";
return $retval;
    }
}
