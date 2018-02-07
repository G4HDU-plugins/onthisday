<?php
/*
+---------------------------------------------------------------+
|        On This Day Menu for e107 v7xx - by Father Barry
|
|        This module for the e107 .7+ website system
|        Copyright Barry Keal 2004-2009
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
+---------------------------------------------------------------+
*/

require_once("../../class2.php");
if (!defined('e107_INIT'))
{
    exit;
}
e107::lan('onthisday',false, true);
        e107::css('onthisday', 'css/onthisday.css'); // load css file

if (!is_object($otd_obj))
{
    require_once(e_PLUGIN . 'onthisday/includes/onthisday_class.php');
    $otd_obj = new onthisday;
}

include_lan(e_PLUGIN . "onthisday/languages/" . e_LANGUAGE . ".php");
$e_wysiwyg = "otd_full";
if ($pref['wysiwyg'])
{
    $WYSIWYG = true;
}
require_once(HEADERF);
$otd_form = e107::getForm(); 
if ($otd_obj->canSubmit())
{
    if (!defined('USER_WIDTH'))
    {
        define(USER_WIDTH, "width:100%;");
    }
    require_once(e_HANDLER . "ren_help.php");
    $otd_currentmonths = explode(",", OTD_MONTHS);
    if (e_QUERY)
    {
        $otd_temp = explode(".", e_QUERY);
        $otd_action = $otd_temp[0];
        $otd_currentid = $otd_temp[1];
        $otd_currentmonth = $otd_temp[2];
        $otd_currentday = $otd_temp[3];
    }
    else
    {
        $otd_action = $_POST['otd_action'];
        $otd_currentid = $_POST['otd_currentid'];
        $otd_currentmonth = $_POST['otd_currentmonth'];
        $otd_currentday = $_POST['otd_currentday'];
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
        $sql->db_Delete("onthisday", "otd_id='$otd_currentid'", false);
        $otd_action = "show";
        $e107cache->clear("nq_otdmenu");
        $e107cache->clear("otd_display");
    }
    if ($otd_action == "delete")
    {
        if ($sql->db_Select("onthisday", "*", "otd_id=$otd_currentid"))
        {
            $otd_row = $sql->db_Fetch();
            extract($otd_row);
            $otd_monthsel = $otd_month -1 ;
            $otd_text = "
<table class='fborder' style='" . USER_WIDTH . "' >
	<tr>
        <td class='fcaption'>" . OTD_A30 . "</td>
    </tr>
	<tr>
        <td class='fcaption'>" . OTD_A26 . "<br /><br /><strong>" . $tp->toHTML($otd_brief, false) . "</strong><br />
	" . OTD_A31 . " $otd_day - " . OTD_A32 . " " . $otd_currentmonths[$otd_monthsel] . " - " . OTD_A33 . " $otd_year
	<br /><br />" . OTD_A27 . "<br />
	<a href='" . e_SELF . "?dodel.$otd_id.$otd_currentmonth.$otd_currentday' >" . OTD_A28 . "</a>&nbsp;&nbsp;&nbsp
	<a href='" . e_SELF . "?show.$otd_id.$otd_currentmonth.$otd_currentday' >" . OTD_A29 . "</a>
	   </td>
    </tr>
</table>";
        }
    }
    if ($otd_action == "save" && intval($_POST['otd_id']) == 0)
    {
        if (!empty($_POST['otd_brief']) && !empty($_POST['otd_day']) && !empty($_POST['otd_month']))
        {
            if (!$sql->db_Select('onthisday', '*', 'where otd_brief="' . $tp->toDB($_POST['otd_brief']) . '"', 'nowhere', false))
            {
                // Create new record
                $otd_arg = "0,
	   '" . $tp->toDB($_POST['otd_brief']) . "',
	   '" . intval($_POST['otd_day']) . "',
	   '" . intval($_POST['otd_month']) . "',
	   '" . intval($_POST['otd_year']) . "',
	   '" . $tp->toDB($_POST['otd_full']) . "',
	   '" . USERID . "'";

                if ($sql->db_Insert("onthisday", $otd_arg, false))
                {
                    $e107cache->clear("nq_otdmenu");
                    $e107cache->clear("otd_display");
                    $otd_msg = OTD_A59;
                    $edata_sn = array("user" => USERNAME, "otd_brief" => $tp->toDB($_POST['otd_brief']), "date" => intval($_POST['otd_day']) . ' - ' . intval($_POST['otd_month']) . ' - ' . intval($_POST['otd_year']));
                    $e_event->trigger("onthisdaypost", $edata_sn);
                    if (isset($gold_obj) && $gold_obj->plugin_active('onthisday') && $OTD_PREF['otd_goldamount'] > 0)
                    {
                        $gold_param['gold_user_id'] = USERID;
                        $gold_param['gold_who_id'] = 0;
                        $gold_param['gold_amount'] = $OTD_PREF['otd_goldamount'];
                        $gold_param['gold_type'] = OTD_G01;
                        $gold_param['gold_action'] = 'credit';
                        $gold_param['gold_plugin'] = 'onthisday';
                        $gold_param['gold_log'] = OTD_G05 . ' : ' . $_POST['otd_brief'];
                        $gold_param['gold_forum'] = 0;
                        $fred = $gold_obj->gold_modify($gold_param, false);
                    }
                }
                else
                {
                    $otd_msg = OTD_A63;
                }
            }
            else
            {
                $otd_msg = OTD_A61;
            }
        }
        else
        {
            $otd_msg = OTD_A60;
        }
        $otd_action = "show";
    }
    if ($otd_action == "save")
    {
        if ($_POST['otd_id'] > 0)
        {
            // saving existing record
            $otd_arg = "
			otd_brief='" . $tp->toDB($_POST['otd_brief']) . "',
			otd_day='" . intval($_POST['otd_day']) . "',
			otd_month='" . intval($_POST['otd_month']) . "',
			otd_year='" . intval($_POST['otd_year']) . "',
			otd_full='" . $tp->toDB($_POST['otd_full']) . "'
			where otd_id='" . intval($_POST['otd_id']) . "'";
            if ($sql->db_Update("onthisday", $otd_arg))
            {
                $e107cache->clear("nq_otdmenu");
                $e107cache->clear("otd_display");
                $otd_msg = OTD_A59;
                $edata_sn = array("user" => USERNAME, "otd_brief" => $tp->toDB($_POST['otd_brief']), "date" => intval($_POST['otd_day']) . ' - ' . intval($_POST['otd_month']) . ' - ' . intval($_POST['otd_year']));
                $e_event->trigger("onthisdayupdate", $edata_sn);
            }
            else
            {
                $otd_msg = OTD_A64;
            }
        }

        $otd_action = "show";
    }

    if ($otd_action == "add" || $otd_action == "edit")
    {
        if ($otd_action == "edit")
        {
            if ($sql->db_Select("onthisday", "*", "otd_id='$otd_currentid' "))
            {
                $otd_row = $sql->db_Fetch();
                extract($otd_row);
            }
        }
        $OTD_BRIEF=$otd_form->text('otd_brief',$tp->toFORM($otd_brief),100,array('size'=>'mini','class'=>'otdBrief'));
        $OTD_DAY=$otd_form->number('otd_day',$tp->toFORM($otd_day),10,array('size'=>'mini','min'=>1,'max'=>31,'class'=>'otdManage'));
        $OTD_MONTH=$otd_form->number('otd_month',$tp->toFORM($otd_month),10,array('size'=>'mini','min'=>1,'max'=>12,'class'=>'otdManage'));
        $OTD_YEAR=$otd_form->text('otd_year',$tp->toFORM($otd_year),10,array('size'=>'mini','class'=>'otdManage'));
        $OTD_FULL=$otd_form->textarea('otd_full',$tp->toFORM($otd_full),10,array('size'=>'mini','class'=>'otdManage'));
        $otd_text = "
<form id='dataform' action='" . e_SELF . "' method='post'>
	<div id='otdvar'>
		<input type='hidden' name='otd_action' value='save' />
		<input type='hidden' name='otd_currentmonth' value='$otd_currentmonth' />
		<input type='hidden' name='otd_currentday' value='$otd_currentday' />
	</div>
	<table class='fborder' style='" . USER_WIDTH . "'>
		<tr>
			<td colspan='2' class='forumheader2'>" . OTD_A11 . " " . $otd_currentday . " " . $otd_currentmonths[$otd_currentmonth] . "</td>
		</tr>
		<tr>
			<td class='forumheader3'>" . OTD_A12 . "</td>
			<td class='forumheader3'>
				$OTD_BRIEF
				<input type='hidden' name='otd_id' value='$otd_id' />
			</td>
		</tr>
		<tr>
			<td class='forumheader3'>" . OTD_A17 . "</td>
			<td class='forumheader3'>
			 " . OTD_A13 . " $OTD_DAY&nbsp;&nbsp;&nbsp;
			 " . OTD_A14 . " $OTD_MONTH &nbsp;&nbsp;&nbsp;
			 " . OTD_A15 . " $OTD_YEAR 
			</td>
		</tr>
		<tr>
			<td class='forumheader3'>" . OTD_A16 . "</td>
			<td class='forumheader3'>$OTD_FULL
			</td>
		</tr>
		<tr>
			<td class='forumheader3' colspan='2'>
				<input type='submit' class='button' name='submitit' value='" . OTD_A09 . "' />
			</td>
		</tr>
    	<tr>
			<td class='fcaption' colspan='2'>&nbsp;</td>
		</tr>
	</table>
</form>";
    }

    if ($otd_action == "show")
    {
        $otd_text .= "
<table class='fborder' style='" . USER_WIDTH . "'>
	<tr>
		<td class='fcaption' >" . OTD_04 . "</td>
	</tr>
	<tr>
		<td class='forumheader2' >" . $otd_msg . "&nbsp;</td>
	</tr>
	<tr>
		<td class='forumheader3'  style='width:100%;text-align:center;'>
		" . $otd_obj->otd_calendar($otd_currentmonth, $otd_currentday) . "</td>
	</tr>
</table>";

        $otd_selmonth = $otd_currentmonth ;
        $otd_text .= "
<table class='fborder' style='" . USER_WIDTH . "'>
	<tr>
		<td class='fcaption' colspan='2'>" . OTD_A24 . " - <strong>$otd_currentday " . $otd_currentmonths[$otd_currentmonth] . "</strong></td>
	</tr>";
        if ($otd_obj->isAdmin())
        {
            // admin so get all
            $otd_where = "where otd_month='$otd_selmonth' and otd_day='$otd_currentday'";
        }
        else
        {
            // not admin so just get mine
            $otd_where = "where otd_month='$otd_selmonth' and otd_day='$otd_currentday' and otd_poster='" . USERID . "'";
        }

        if ($sql->db_Select("onthisday", "*", "$otd_where", "nowhere", false))
        {
            while ($otd_row = $sql->db_Fetch())
            {
                extract($otd_row);

                $otd_text .= "
	<tr>
		<td class='forumheader3' style='width:70%'>" . $tp->toHTML($otd_brief, false) . "</td>
		<td class='forumheader3' style='width:30%;text-align:center;'>
			<a href='" . e_SELF . "?edit.$otd_id.$otd_currentmonth.$otd_currentday' alt='".OTD_013."' title='".OTD_013."'><i class='fa fa-edit  fa-2x'></i></a>&nbsp;&nbsp;
			<a href='" . e_SELF . "?delete.$otd_id.$otd_currentmonth.$otd_currentday' alt='".OTD_014."' title='".OTD_014."' ><i class='fa fa-trash fa-2x'></i></a></td>
	</tr>";
            }
        }
        else
        {
            $otd_text .= "
	<tr>
		<td class='forumheader3' colspan='2'>" . OTD_A25 . "</td>
	</tr>";
        }
        $otd_text .= "
	<tr>
		<td class='forumheader3' colspan='2'>
			<a href='" . e_SELF . "?add.0.$otd_currentmonth.$otd_currentday'>" . OTD_A21 . "</a>
		</td>
	</tr>
	<tr>
		<td class='fcaption' colspan='2'>&nbsp;</td>
	</tr>
</table>";
    }
}
else
{
    $otd_text = "Not permitted";
}

$ns->tablerender(OTD_04, $otd_text);

require_once(FOOTERF);
