<?php

/**
 *  On This Day Plugin for the e107 Website System
 *
 * Copyright (C) 2008-2017 Barry Keal G4HDU (http://www.keal.me.uk)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

e107::lan('onthisday', false, true); //load front

require_once (e_HANDLER . 'userclass_class.php');
class onthisday
{
    private $admin = false; // admin?
    private $read = false; // read?
    private $submit = false; // submit?
    private $prefs;
    private $template;
    private $sc;
    private $frm;
    private $msg;
    private $db;
    private $tp;
    private $ns;
    private $monthList;
    private $daysInMonth;
    private $menuCacheh;
    private $otdCache;
    private $day; // day and month we are working on
    private $month;
    private $thisDay; // today day and month
    private $thisMonth;

    function __construct()
    {
        e107::js('footer', e_PLUGIN . 'onthisday/js/onthisday.js', 'jquery'); // Load Plugin javascript and include jQuery framework
        e107::css('onthisday', 'css/onthisday.css'); // load css file

        $this->prefs = e107::pref('onthisday');

        $this->admin = check_class($this->prefs['otd_adminclass']);
        $this->submit = $this->admin || check_class($this->prefs['otd_submitclass']);
        $this->read = $this->submit || check_class($this->prefs['otd_readclass']);
        define("e_PAGETITLE", OTD_04);

        require_once (e_PLUGIN . 'onthisday/shortcodes/onthisday_shortcodes.php');
        if (file_exists(THEME . 'images/otd_logo.png'))
        {
            define('OTD_LOGO', THEME . 'images/otd_logo.png');
        } else
        {
            define('OTD_LOGO', e_PLUGIN . 'onthisday/images/otd_logo.png');
        }

        if (file_exists(THEME . 'onthisday_template.php'))
        {
            define('OTD_TEMPLATE', THEME . 'onthisday_template.php');
        } else
        {
            define('OTD_TEMPLATE', e_PLUGIN . 'onthisday/templates/onthisday_template.php');
        }
        require_once (OTD_TEMPLATE);
        $this->template = new onthisday_template();
        require_once (e_PLUGIN . 'onthisday/shortcodes/onthisday_shortcodes.php');
        $this->sc = new otdShortcode();
        $this->msg = e107::getMessage();
        $this->db = e107::getDB(); // mysql class object
        $this->tp = e107::getParser(); // parser for converting to HTML and parsing templates etc.
        $this->frm = e107::getForm(); // Form element class.
        $this->ns = e107::getRender(); // render in theme box.
        $this->monthList = explode(',', OTD_MONTHLIST);

        $this->daysInMonth = array(
            0,
            31,
            29,
            31,
            30,
            31,
            30,
            31,
            31,
            30,
            31,
            30,
            31);
        // print_a($this->daysInMonth);
        $this->menuCache = "nq_otdmenu"; // the name of the menu cache
        $this->otdCache = "otd_display"; // the name of the front page cache
        $this->day = date('d'); // today's day and month
        $this->month = date('m');

        if ($this->prefs['lastDay'] != date('z'))
        {
            // check if the day has changed using z - day of year 0 - 365
            // if it has clear the caches
            // and save current day in prefs
            e107::getCache()->clear($this->menuCache);
            e107::getCache()->clear($this->otdCache);
            $tmp = e107::getConfig('onthisday');
            $tmp->setPref('lastDay', date('z'));
            $tmp->save(false, true, false);
            // print_a($this->prefs);
        }

    }
    function runPage()
    {
        $this->parseQuery(); // get any post or get values passed in from url or form
        return $this->doAction();

    }
    private function parseQuery()
    {
        if (isset($_GET['action'])) // passed in by get
        {
            $this->action = $_GET['action'];
            $this->from = (int)$_GET['from'];
            $this->month = (int)$_GET['month']; // month and day we are editing
            $this->day = (int)$_GET['day']; // month and day we are editing
            $this->id = (int)$_GET['id'];
            $this->calMonth = (int)$_GET['calMonth']; // the month/day used in the calendar
            $this->calDay = (int)$_GET['calDay']; // the month/day used in the calendar
        } elseif (isset($_POST['id'])) // passed in by post
        {
            $this->from = (int)$_POST['from'];
            $this->action = $_POST['action'];
            $this->id = (int)$_POST['id'];
            $this->month = (int)$_POST['month']; // month and day we are editing
            $this->day = (int)$_POST['day']; // month and day we are editing
            $this->calMonth = (int)$_POST['calMonth']; // the month/day used in the calendar
            $this->calDay = (int)$_POST['calDay']; // the month/day used in the calendar
        } elseif (e_QUERY) // old style equery
        {
            $otd_tmp = explode('.', e_QUERY);
            $this->from = (int)$otd_tmp[0];
            $this->action = $otd_tmp[1];
            $this->calMonth = intval($otd_tmp[2]);
            $this->calDay = intval($otd_tmp[3]);
            $this->id = intval($otd_tmp[4]);
        }
        if ($this->month > 12 || $this->month == 0 || $this->day == 0 || $this->day > $this->daysInMonth[$this->month])
        {
            //  $this->day = (int)date("d");
            //   $this->month = (int)date("m");
            //$this->calMonth=1;
            //$this->calDay=1;
            //   $this->action = 'day';
            // $this->id = 0;
            // $this->from = 0;
        }

        $this->sc->day = $this->day;
        $this->sc->month = $this->month;
        $this->sc->today = (int)date("d");
        $this->sc->tomonth = (int)date("m");
        $this->sc->daysInMonth = $this->daysInMonth;
        $this->sc->monthList = $this->monthList;
        $this->sc->calMonth = $this->calMonth;
        $this->sc->calDay = $this->calDay;
        if (isset($_POST['dodel']))
        {
            //   print_a($this->action);
            //    print_a($this->calMonth);
            //    print_a($this->calDay);
            $otd_text = $this->dodel();
            $this->action = "manage";
        }
        if (isset($_POST['cancdel']))
        {
            $this->action = 'manage';
        }
        if ($this->action == 'save')
        {
            $this->save();
            $this->action = "manage";
        }
    }
    private function doAction()
    {
        //print_a($this->action);
        switch ($this->action)
        {

            case 'add':
            case 'edit':
                $text = $this->addedit($this->id, $this->action);
                break;
            case 'delete':
                $text = $this->deleteit($this->id);
                break;
            case 'manage':
                $text = $this->showRec();
                break;
            case 'view':
                $text = $this->view();
                break;
            default:
                $this->action = 'day';
        }
        if ($this->action == 'day')
        {
            $text = $this->doList();
        }
        return $text;
    }
    private function doList()
    {
        // retrieve the cached menu
        $text = e107::getCache()->retrieve($this->otdCache); // get the cache contents
        //  print_a($this->month);
        //  print_a($this->day);
        if (false === $text)
        {
            // there is no cache
            // so create the cache entry
            $qry = "SELECT * FROM #onthisday 
                where otd_month='{$this->month}' AND otd_day='{$this->day}' ORDER BY otd_year,otd_brief";
            $result = $this->db->gen($qry, false);
            if ($result)
            {
                $text = $this->tp->parseTemplate($this->template->otdDayHead(), true, $this->sc);

                while ($row = $this->db->fetch())
                {
                    $this->sc->row = $row;
                    $text .= $this->tp->parseTemplate($this->template->otdDayDetail(), true, $this->sc);
                }
                $text .= $this->tp->parseTemplate($this->template->otdDayFoot(), true, $this->sc);

            } else
            {
                $text .= $this->tp->parseTemplate($this->template->otdNoRec(), true, $this->sc);
            }

            $res = e107::getCache()->set($this->otdCache, $text); // and save it in the cache
        }
        return $text;
    }
    function otd_calendar($month = 1, $day = 1)
    {
        // global $sql;
        $otd_onthisday = (int)date("d");
        $otd_onthismonth = (int)date("m");
        $selmonth = (int)$month;

        $otd_where = "where otd_month='{$this->calMonth}'";

        $this->db->select("onthisday", "otd_day", "$otd_where", "nowhere", false);
        $otd_activedays = array();
        while ($otd_row = $this->db->fetch())
        {
            $otd_activedays[] = $otd_row['otd_day'];
        } // while
        $otd_prev = $this->calMonth - 1;
        $otd_next = $this->calMonth + 1;
        $otd_months = explode(",", OTD_MONTHS); // names of months
        $otd_days = array(
            0,
            31,
            29,
            31,
            30,
            31,
            30,
            31,
            31,
            30,
            31,
            30,
            31); // number of days in month
        $text .= "
<table style='width:100%;text-align:center;margin-left:auto;margin-right:auto;border-width:1px;border-style:solid;'>";
        if ($this->calMonth > 1)
        {
            $text .= "
	<tr>
		<td class='forumheader2'>
            <a href='" . e_SELF . "?from=0&action=manage&calMonth={$otd_prev}&calDay=1'>
                <i class='fa fa-angle-left fa-2x' aria-hidden='true'></i>
            </a>
        </td>";
        } else
        {
            $text .= "
	<tr>
		<td class='forumheader2'>&nbsp;</td>";
        }
        //     print_a($this->calMonth);
        //      print_a($this->calDay);
        $text .= "
		<td class='forumheader2' style='text-align:center;' colspan='5'>&nbsp;&nbsp;" . $this->monthList[$this->calMonth] . "&nbsp;&nbsp;</td>";
        if ($this->calMonth < 12)
        {
            $text .= "
        <td class='forumheader2' style = 'text-align:right;'>
            <a href='" . e_SELF . "?from=0&action=manage&calMonth={$otd_next}&calDay=1'>
                <i class='fa fa-angle-right fa-2x' aria-hidden='true'></i>
            </a>
        </td>";
        } else
        {
            $text .= "
		<td class='forumheader2'>&nbsp;</td>";
        }
        $column = 0;
        $text .= "
	</tr>
	<tr>
		<td class='forumheader2' colspan='7' style='text-align:center;'>";
        for ($i = 1; $i < 13; $i++)
        {
            if ($i == $this->calMonth)
            {
                $otd_m = '<b>' . substr($otd_months[$i], 0, 3) . '</b>';
            } else
            {
                $otd_m = substr($otd_months[$i], 0, 3);
            }
            $text .= "&nbsp;<a href='" . e_SELF . "?from=0&action=manage&calMonth={$i}&calDay=$this->calDay'>" . $otd_m . "</a>&nbsp;";
        }
        $text .= "
		</td>
	</tr>
	<tr>";

        for ($i = 1; $i <= $otd_days[$this->calMonth]; $i++)
        {
            if ($column > 6)
            {
                $text .= "
	</tr>
	<tr>";
                $column = 0;
            }
            if ($i == $this->calDay)
            {
                $highlight = "background-color:#CC9999; ";
            } else
            {
                $highlight = "";
            }
            if ($this->toDay == $i && $this->toMonth == $this->month)
            {

                $today_highlight = 'border: double #0000FF;';
            } else
            {
                $today_highlight = '';
            }

            if (in_array($i, $otd_activedays))
            {
                $otd_active = "*&nbsp;";
            } else
            {
                $otd_active = "&nbsp;&nbsp;";
            }
            if ($column == 0 || $column == 6)
            {
                $text .= "
			<td class='forumheader3' style='text-align:right;{$highlight}{$today_highlight}'>$otd_active<a href='" . e_SELF . "?action=manage&from=0&calMonth={$month}&calDay={$i}'>" . $i . "</a></td>";
            } else
            {
                $text .= "
			<td class='forumheader3' style='text-align:right;{$highlight}{$today_highlight}'>$otd_active<a href='" . e_SELF . "?action=manage&from=0&calMonth={$month}&calDay={$i}'>" . $i . "</a></td>";
            }
            $column++;
        }
        if ($column < 7)
        {
            for ($i = $column; $i <= 6; $i++)
            {
                if ($column == 0 || $column == 6)
                {
                    $text .= "
			<td class='forumheader3'>&nbsp;</td>";
                } else
                {
                    $text .= "
			<td class='forumheader3'>&nbsp;</td>";
                }
                $column++;
            }
        }
        $text .= "
	</tr>
</table>";
        return $text;
    }
    function isAdmin()
    {
        return $this->admin;
    }
    function canSubmit()
    {
        return $this->submit;
    }
    function canView()
    {
        return $this->read;
    }
    function showMenu()
    {
        $otd_thisday = date("d");
        $otd_thismonth = date("m");

        if ($this->db->select("onthisday", "*", "where otd_day='$otd_thisday' and otd_month='$otd_thismonth' order by otd_year", "nowhere", false))
        {
            $otd_text = "
<div id='otdMenuContainer' style='min-height:50px;max-height:" . $this->prefs['otd_maxheight'] . "px;'>
    <ul>";
            while ($row = $this->db->db_Fetch())
            {
                $otd_text .= "
        <li>" . $this->tp->html_truncate($this->tp->toHTML($row['otd_brief'], false, "no_make_clickable emotes_off"), $this->prefs['otd_maxlength'], OTD_MORE) . "</li>";
            }
            $otd_text .= "
    </ul>
</div>";
            $otd_text .= "
<div style='text-align:center'>
    <a href='" . e_PLUGIN . "onthisday/index.php'>" . OTD_015 . "</a>
</div>";
        } else
        {
            $otd_text .= OTDLAN_DEFAULT;
        }
        if ($this->canSubmit())
        {
            // allowed to submit so display link
            $otd_text .= "
<div style='text-align:center;'>
    <a href='" . e_PLUGIN . "onthisday/index.php?action=manage&calMonth={$this->month}&calDay={$this->day}'>" . OTD_001 . "</a>
</div>";
        }

        $cache_data = $this->ns->tablerender(OTDLAN_CAP, $otd_text, 'otdmenu', true); // Render the menu
        return $cache_data;
    }
    function addedit($otd_currentid, $param)
    {
        if ($param == "edit")
        {
            if ($this->db->select("onthisday", "*", "otd_id='$otd_currentid' "))
            {
                $otd_row = $this->db->fetch();
                extract($otd_row);
            }
        } else
        {
            $otd_day = $this->calDay;
            $otd_month = $this->calMonth;
        }
        $this->sc->otd_brief = $this->frm->text('otd_brief', $this->tp->toFORM($otd_brief), 200, array('size' => 'mini', 'class' => 'otdBrief'));
        $this->sc->otd_day = $this->frm->number('otd_day', $this->tp->toFORM($otd_day), 10, array(
            'size' => 'mini',
            'min' => 1,
            'max' => 31,
            'class' => 'otdManage'));
        $this->sc->otd_month = $this->frm->number('otd_month', $this->tp->toFORM($otd_month), 10, array(
            'size' => 'mini',
            'min' => 1,
            'max' => 12,
            'class' => 'otdManage'));
        $this->sc->otd_year = $this->frm->text('otd_year', $this->tp->toFORM($otd_year), 10, array('size' => 'mini', 'class' => 'otdManage'));
        $this->sc->otd_full = $this->frm->textarea('otd_full', $this->tp->toFORM($otd_full), 10, array('size' => 'mini', 'class' => 'otdManage'));
        $this->sc->otd_submit = $this->frm->submit('submitit', OTD_A09);
        $otd_text = "
<form id='otddataform' action='" . e_SELF . "' method='post'>
	<div id='otdvar'>
		<input type='hidden' name='action' value='save' />
		<input type='hidden' name='calMonth' value='$this->calMonth' />
		<input type='hidden' name='calDay' value='{$this->calDay}' />
		<input type='hidden' name='id' value='{$this->id}' />
	</div>";
        $this->sc->currentday = $otd_currentday;
        $this->sc->currentmonth = $this->monthList[$otd_currentmonth];

        $otd_text .= $this->tp->parsetemplate($this->template->editEntry(), false, $this->sc);
        $otd_text .= "
</form>";
        return $otd_text;
    }
    function deleteit()
    {
        if ($this->db->select("onthisday", "*", "WHERE otd_id='{$this->id}'", "", false))
        {
            $otd_row = $this->db->fetch();
            extract($otd_row);
            $otd_monthsel = $otd_month - 1;
            $OTD_DELETE = $this->frm->submit('dodel', OTD_A28);
            $OTD_CANCEL = $this->frm->submit('cancdel', OTD_A29);
            $otd_text = "
<form id='dataform' action='" . e_SELF . "' method='post'>
	<div id='otdvar'>
		<!--<input type='hidden' name='otd_action' value='blank' />-->
		<input type='hidden' name='id' value='{$this->id}' />
		<input type='hidden' name='calMonth' value='{$this->calMonth}' />
		<input type='hidden' name='calDay' value='{$this->calDay}' />
	</div>
    <table class='fborder' style='" . USER_WIDTH . "' >
    	<tr>
            <td class='fcaption'>" . OTD_A30 . "</td>
        </tr>
    	<tr>
            <td class='forumheader3'>" . OTD_A26 . "<br /><br /><strong>" . $this->tp->toHTML($otd_brief, false) . "</strong><br />
    	" . OTD_A31 . " $otd_day - " . OTD_A32 . " " . $this->monthList[$otd_month] . " - " . OTD_A33 . " $otd_year
    	<br />" . OTD_A27 . "<br />
    	{$OTD_DELETE}&nbsp;&nbsp;&nbsp;{$OTD_CANCEL}
    	   </td>
        </tr>
    </table>
</form>";
        }
        return $otd_text;
    }
    // function dodel($otd_currentid, $param, $otd_currentmonth, $otd_currentday)
    function dodel()
    {
        global $e_event;
        //         print_a($this->calMonth);
        if ($this->db->delete("onthisday", "otd_id='{$this->id}'", false))
        {

            $this->msg->addSuccess(OTD_A67);
            e107::getCache()->clear($this->menuCache);
            e107::getCache()->clear($this->otdCache);
            $edata_sn = array(
                "user" => USERNAME,
                "otd_brief" => $this->tp->toDB($_POST['otd_brief']),
                "date" => intval($_POST['otd_day']) . ' - ' . intval($_POST['otd_month']) . ' - ' . intval($_POST['otd_year']));
            $e_event->trigger("onthisdaydelete", $edata_sn);

        } else
        {
            $this->msg->addError(OTD_A68);
        }
        $this->action = "manage";
    }
    function addNew($otd_currentid, $param, $otd_currentmonth, $otd_currentday)
    {
        global $e_event;

    }
    function save()
    {
        global $e_event;
        //   print_a($_POST['id']);
        if ($_POST['id'] > 0)
        {
            // check if record is identical
            if (!$this->db->select('onthisday', 'otd_id', 'where 
            otd_day="' . (int)$_POST['otd_day'] . '" AND 
            otd_month="' . (int)$_POST['otd_month'] . '" AND 
            otd_year="' . (int)$_POST['otd_year'] . '" AND 
            otd_brief="' . $this->tp->toDB($_POST['otd_brief']) . '" AND
            otd_full="' . $this->tp->toDB($_POST['otd_full']) . '"', 'nowhere', false))
            {
                // saving existing record if changes made
                $otd_arg = "
			otd_brief='" . $this->tp->toDB($_POST['otd_brief']) . "',
			otd_day='" . intval($_POST['otd_day']) . "',
			otd_month='" . intval($_POST['otd_month']) . "',
			otd_year='" . intval($_POST['otd_year']) . "',
			otd_full='" . $this->tp->toDB($_POST['otd_full']) . "'
			where otd_id='" . intval($this->id) . "'";
                if ($this->db->update("onthisday", $otd_arg))
                {
                    e107::getCache()->clear($this->menuCache);
                    e107::getCache()->clear($this->otdCache);
                    $this->msg->addSuccess(OTD_A59);
                    // e107::getMessage()->addSuccess(OTD_A59);
                    $edata_sn = array(
                        "user" => USERNAME,
                        "otd_brief" => $this->tp->toDB($_POST['otd_brief']),
                        "date" => intval($_POST['otd_day']) . ' - ' . intval($_POST['otd_month']) . ' - ' . intval($_POST['otd_year']));
                    $e_event->trigger("onthisdayupdate", $edata_sn);
                } else
                {
                    // unable to update
                    $this->msg->addError(OTD_A64);
                }
            } else
            {
                // no changes made
                $this->msg->addInfo(OTD_A66);
            }
            //      print_a($this->msg);
            // print_a($_SESSION);

        } else
        {
            // add a new one
            if (!empty($_POST['otd_brief']) && !empty($_POST['otd_day']) && !empty($_POST['otd_month']))
            {
                // if the fields are completed
                // check if a duplicate
                if (!$this->db->select('onthisday', 'otd_id', 'where 
            otd_day="' . (int)$_POST['otd_day'] . '" AND 
            otd_month="' . (int)$_POST['otd_month'] . '" AND 
            otd_year="' . (int)$_POST['otd_year'] . '" AND 
            otd_brief="' . $this->tp->toDB($_POST['otd_brief']) . '" AND
            otd_full="' . $this->tp->toDB($_POST['otd_full']) . '"', 'nowhere', false))
                {
                    // Create new record ensuring not a duplicate
                    $otd_arg = "0,
	   '" . $this->tp->toDB($_POST['otd_brief']) . "',
	   '" . intval($_POST['otd_day']) . "',
	   '" . intval($_POST['otd_month']) . "',
	   '" . intval($_POST['otd_year']) . "',
	   '" . $this->tp->toDB($_POST['otd_full']) . "',
	   '" . USERID . "'";

                    if ($this->db->insert("onthisday", $otd_arg, false))
                    {
                        // all OK so attempt to save the new record
                        $this->msg->addSuccess(OTD_A59);
                        e107::getCache()->clear($this->menuCache);
                        e107::getCache()->clear($this->otdCache);

                        $edata_sn = array(
                            "user" => USERNAME,
                            "otd_brief" => $this->tp->toDB($_POST['otd_brief']),
                            "date" => intval($_POST['otd_day']) . ' - ' . intval($_POST['otd_month']) . ' - ' . intval($_POST['otd_year']));
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
                    } else
                    {
                        //failed to save the record
                        $this->msg->addError(OTD_A63);
                    }
                } else
                {
                    // duplicate record
                    $this->msg->addWarning(OTD_A61);
                }
            } else
            {
                // fields not completed
                $this->msg->addWarning(OTD_A60);
            }

        }
        //echo $this->msg->render();

    }
    function showRec()
    {
        $this->sc->otd_calendar = $this->otd_calendar($this->calMonth, $this->calDay);
        // show the calendar at the top of the list
        $otd_text .= $this->tp->parsetemplate($this->template->showHead(), false, $this->sc);
        $otd_where = "where otd_month='$this->calMonth' AND otd_day='$this->calDay'";
        $this->sc->calMonth = $this->calMonth;
        $this->sc->calDay = $this->calDay;
        if ($this->db->select("onthisday", "*", "$otd_where", "nowhere", false))
        {
            while ($row = $this->db->fetch())
            {
                $this->sc->row = $row;
                $otd_text .= $this->tp->parsetemplate($this->template->showRow(), false, $this->sc);
            }
        } else
        {
            $otd_text .= $this->tp->parsetemplate($this->template->showNoRow(), false, $this->sc);
        }
        $otd_text .= $this->tp->parsetemplate($this->template->showFoot(), false, $this->sc);

        return $otd_text;
    }
    function renderMessage()
    {

     //   print_a($_SESSION);
        $retval = $this->msg->render();
        return $retval;
    }
    function notPermitted()
    {
        return $this->tp->parsetemplate($this->template->otdNotPermitted(), false, $this->sc);
    }
    //zztp00472062
    function view()
    {
        if ($this->db->select("onthisday", "*", "WHERE otd_id='{$this->id}'", "", false))
        {
            $otd_row = $this->db->fetch();
            extract($otd_row);
            $otd_monthsel = $otd_month - 1;

            $OTD_HOME = $this->frm->submit('cancok', OTD_A28);
            $otd_text = "
<form id='dataform' action='" . e_SELF . "' method='post'>
	<div id='otdvar'>
		<!--<input type='hidden' name='action' value='day' />-->
		<input type='hidden' name='id' value='{$this->id}' />
		<input type='hidden' name='calMonth' value='{$this->calMonth}' />
		<input type='hidden' name='calDay' value='{$this->calDay}' />
	</div>
    <table class='fborder' style='" . USER_WIDTH . "' >
    	<tr>
            <td class='fcaption'>" . OTD_016 . "</td>
        </tr>
    	<tr>
            <td class='forumheader3'><strong>" . $this->tp->toHTML($otd_brief, false) . "</strong><br />
    	" . OTD_A31 . " $otd_day - " . OTD_A32 . " " . $this->monthList[$otd_month] . " - " . OTD_A33 . " $otd_year
    	<br /><br />{$OTD_HOME}
    	   </td>
        </tr>
    </table>
</form>";
        }
        return $otd_text;
    }
}
