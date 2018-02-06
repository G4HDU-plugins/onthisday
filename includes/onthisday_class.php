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
    private $otd_admin = false; // admin?
    private $otd_read = false; // read?
    private $otd_submit = false; // submit?
    function __construct()
    {
        e107::js('footer', e_PLUGIN . 'onthisday/js/onthisday.js', 'jquery'); // Load Plugin javascript and include jQuery framework
        e107::css('onthisday', 'css/onthisday.css'); // load css file

        $this->prefs = e107::pref('onthisday');
        //  print_a($this->prefs);
        //   global $OTD_PREF;
        //   $this->load_prefs();
        $this->otd_admin = check_class($this->prefs['otd_adminclass']);
        $this->otd_submit = $this->otd_admin || check_class($this->prefs['otd_submitclass']);
        $this->otd_read = $this->otd_submit || check_class($this->prefs['otd_readclass']);
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
        $menuCache = "nq_otdmenu";
        $otdCache = "otd_display";
        $day = date('d');
        if ($this->prefs['lastDay'] !== $day)
        {
            e107::getCache()->clear($menuCache);
            e107::getCache()->clear($otdCache);
            $cache = e107::getConfig('onthisday');
            $cache->setPref('lastDay', $day);
            $cache->save(false, true, false);
        }
    }
    function runPage()
    {
        $this->parseQuery();
        return $this->doAction();

    }
    private function parseQuery()
    {
        if (isset($_GET['action']))
        {
            $this->action = $_GET['action'];
            $this->from = (int)$_GET['from'];
            $this->month = (int)$_GET['month'];
            $this->day = (int)$_GET['day'];
            $this->id = (int)$_GET['id'];


        } elseif (e_QUERY)
        {
            $otd_tmp = explode('.', e_QUERY);
            $this->from = (int)$otd_tmp[0];
            $this->action = $otd_tmp[1];
            $this->month = intval($otd_tmp[2]);
            $this->day = intval($otd_tmp[3]);
            $this->id = intval($otd_tmp[4]);

        }
        if ($this->month > 12 || $this->month == 0 || $this->day == 0 || $this->day > $this->daysInMonth[$this->month])
        {
            $this->day = (int)date("d");
            $this->month = (int)date("m");
            $this->action = 'day';
            $this->id = 0;
            $this->from = 0;
        }
        $this->sc->day = $this->day;
        $this->sc->month = $this->month;
        $this->sc->today = (int)date("d");
        $this->sc->tomonth = (int)date("m");
        $this->sc->daysInMonth = $this->daysInMonth;
        $this->sc->monthList = $this->monthList;
    }
    private function doAction()
    {
        switch ($this->action)
        {
            case 'show':
                $text = $this->showRec();
                break;
            case 'day':
            default:
                $text = $this->doList();
        }
        return $text;
    }
    private function doList()
    {
        $menuCache = "nq_otdmenu";
        $otdCache = "otd_display";

        // retrieve the cached menu
        $cached = e107::getCache()->retrieve($otdCache); // get the cache contents
        if (false === $cached)
        {
            // there is no cache
            // so create the cache entry
            $qry = "SELECT * FROM #onthisday 
where otd_month='{$this->month}' AND otd_day='{$this->day}' ORDER BY otd_year,otd_brief";
            $cached = $this->tp->parseTemplate($this->template->otdDayHead(), true, $this->sc);
            $result = $this->db->gen($qry, false);
            if ($result)
            {
                while ($row = $this->db->fetch())
                {
                    $this->sc->row = $row;
                    $cached .= $this->tp->parseTemplate($this->template->otdDayDetail(), true, $this->sc);
                }
            } else
            {
                $cached .= $this->tp->parseTemplate($this->template->otdNoRec(), true, $this->sc);
            }
            $cached .= $this->tp->parseTemplate($this->template->otdDayFoot(), true, $this->sc);

            e107::getCache()->set($otdCache, $cached); // and save it in the cache

        }
        return $cached;
    }
    function otd_calendar($month = 1, $day = 1)
    {
        global $sql;
        $otd_onthisday = (int)date("d");
        $otd_onthismonth = (int)date("m");
        $selmonth = (int)$month;
        if ($this->otd_admin)
        {
            // admin so get all
            $otd_where = "where otd_month='{$month}'";
        } else
        {
            // not admin so just get mine
            $otd_where = "where otd_month='{$month}' and otd_poster='" . USERID . "'";
        }
        $sql->db_Select("onthisday", "otd_day", "$otd_where", "nowhere", false);
        $otd_activedays = array();
        while ($otd_row = $sql->db_Fetch())
        {
            $otd_activedays[] = $otd_row['otd_day'];
        } // while
        $otd_prev = $month - 1;
        $otd_next = $month + 1;
        $otd_months = explode(",", OTD_MONTHS);
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
            31);
        $text .= "
<table style='width:100%;text-align:center;margin-left:auto;margin-right:auto;border-width:1px;border-style:solid;'>";
        if ($month > 1)
        {
            $text .= "
	<tr>
		<td class='forumheader2'><a href='" . e_SELF . "?show.0." . $otd_prev . ".$day'>&lt;</a></td>";
        } else
        {
            $text .= "
	<tr>
		<td class='forumheader2'>&nbsp;</td>";
        }
        $text .= "
		<td class='forumheader2' style='text-align:center;' colspan='5'>&nbsp;&nbsp;" . $otd_months[$month] . "&nbsp;&nbsp;</td>";
        if ($month < 12)
        {
            $text .= "
		<td class='forumheader2'><a href='" . e_SELF . "?show.0." . $otd_next . ".$day'>&gt;</a></td>";
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
            if ($i == $month)
            {
                $otd_m = '<b>' . substr($otd_months[$i], 0, 3) . '</b>';
            } else
            {
                $otd_m = substr($otd_months[$i], 0, 3);
            }
            $text .= "&nbsp;<a href='" . e_SELF . "?show.0.$i.$day'>" . $otd_m . "</a>&nbsp;";
        }
        $text .= "
		</td>
	</tr>
	<tr>";

        for ($i = 1; $i <= $otd_days[$month]; $i++)
        {
            if ($column > 6)
            {
                $text .= "
	</tr>
	<tr>";
                $column = 0;
            }
            if ($i == $day)
            {
                $highlight = "background-color:#CC9999; ";
            } else
            {
                $highlight = "";
            }
            if ($otd_onthisday == $i && $otd_onthismonth == $month)
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
			<td class='forumheader3' style='text-align:right;{$highlight}{$today_highlight}'>$otd_active<a href='" . e_SELF . "?show.0.$month.$i'>" . $i . "</a></td>";
            } else
            {
                $text .= "
			<td class='forumheader3' style='text-align:right;{$highlight}{$today_highlight}'>$otd_active<a href='" . e_SELF . "?show.0.$month.$i'>" . $i . "</a></td>";
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
        return $this->otd_admin;
    }
    function canSubmit()
    {
        return $this->otd_submit;
    }
    function canView()
    {
        return $this->otd_read;
    }
    function showMenu()
    {
        // include_lan(e_PLUGIN . "onthisday/languages/" . e_LANGUAGE . ".php");
        $otd_thisday = date("d");
        $otd_thismonth = date("m");
        $otd_text = "<div id='otdMenuContainer' style='min-height:50px;max-height:".$this->prefs['otd_maxheight']."px;'><ul>";
        if ($this->db->select("onthisday", "*", "where otd_day='$otd_thisday' and otd_month='$otd_thismonth' order by otd_year", "nowhere", false))
        {
            while ($row = $this->db->db_Fetch())
            {
                $otd_text .= "<li>" . $this->tp->html_truncate($this->tp->toHTML($row['otd_brief'], false, "no_make_clickable emotes_off"), $this->prefs['otd_maxlength'], OTD_MORE) . "</li>";
            }
              $otd_text .= "</ul></diV>";
            $otd_text .= "<div style='text-align:center'><a href='" . e_PLUGIN . "onthisday/index.php'>" . OTD_015 . "</a></div>";
       
        } else
        {

            $otd_text .= OTDLAN_DEFAULT;
        }
        if ($this->canSubmit())
        {
            // allowed to submit so display link
            $otd_text .= "<div style='text-align:center;'><a href='" . e_PLUGIN . "onthisday/manage_entries.php'>" . OTD_001 . "</a></div>";
        }
       
        $cache_data = $this->ns->tablerender(OTDLAN_CAP, $otd_text, 'otdmenu', true); // Render the menu
        return $cache_data;
    }

}
