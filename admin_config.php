<?php

/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* onthisdayd under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* e107 onthisday Plugin
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/onthisday/admin_config.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

require_once ("../../class2.php");
if (!getperms("P"))
{
    e107::redirect('admin');
    exit;
}
e107::lan('onthisday', true, true);

class plugin_onthisday_admin extends e_admin_dispatcher
{
    /**
     * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'index' => 'list', 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
     * Note - default mode/action is autodetected in this order:
     * - $defaultMode/$defaultAction (owned by dispatcher - see below)
     * - $adminMenu (first key if admin menu array is not empty)
     * - $modes (first key == mode, corresponding 'index' key == action)
     * @var array
     */
    protected $modes = array('main' => array(
            'controller' => 'plugin_onthisday_admin_ui',
            'path' => null,
            'ui' => 'plugin_onthisday_admin_form_ui',
            'uipath' => null));

    /* Both are optional
    * protected $defaultMode = null;
    * protected $defaultAction = null;
    */

    /**
     * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}onthisday/admin_config.php', 'perm' => '0']);
     * Additionally, any valid e107::getNav()->admin() key-value pair could be added to the above array
     * @var array
     */
    protected $adminMenu = array(
        'main/list' => array('caption' => 'Manage', 'perm' => '0'),
        'main/create' => array('caption' => LAN_CREATE, 'perm' => '0'),
        'main/prefs' => array('caption' => 'Settings', 'perm' => '0'),
        'main/import' => array('caption' => 'Import', 'perm' => '0'),
        'main/export' => array('caption' => 'Export', 'perm' => '0'));

    /**
     * Optional, mode/action aliases, related with 'selected' menu CSS class
     * Format: 'MODE/ACTION' => 'MODE ALIAS/ACTION ALIAS';
     * This will mark active main/list menu item, when current page is main/edit
     * @var array
     */
    protected $adminMenuAliases = array('main/edit' => 'main/list');

    /**
     * Navigation menu title
     * @var string
     */
    protected $menuTitle = 'On This Day';
}


class plugin_onthisday_admin_ui extends e_admin_ui
{
    // required
    protected $pluginTitle = "e107 onthisday";

    /**
     * plugin name or 'core'
     * IMPORTANT: should be 'core' for non-plugin areas because this
     * value defines what CONFIG will be used. However, I think this should be changed
     * very soon (awaiting discussion with Cam)
     * Maybe we need something like $prefs['core'], $prefs['onthisday'] ... multiple getConfig support?
     *
     * @var string
     */
    protected $pluginName = 'onthisday';

    /**
     * DB Table, table alias is supported
     * Example: 'r.onthisday'
     * @var string
     */
    protected $table = "onthisday";

    /**
     * This is only needed if you need to JOIN tables AND don't wanna use $tableJoin
     * Write your list query without any Order or Limit.
     *
     * @var string [optional]
     */
    protected $listQry = "";
    //

    // optional - required only in case of e.g. tables JOIN. This also could be done with custom model (set it in init())
    //protected $editQry = "SELECT * FROM #onthisday WHERE onthisday_id = {ID}";

    // required - if no custom model is set in init() (primary id)
    protected $pid = "otd_id";

    // optional
    protected $perPage = 20;

    protected $batchDelete = true;

    //	protected \$sortField		= 'somefield_order';


    //	protected \$sortParent      = 'somefield_parent';


    //	protected \$treePrefix      = 'somefield_title';


    //TODO change the onthisday_url type back to URL before onthisday.
    // required
    /**
     * (use this as starting point for wiki documentation)
     * $fields format  (string) $field_name => (array) $attributes
     *
     * $field_name format:
     * 	'table_alias_or_name.field_name.field_alias' (if JOIN support is needed) OR just 'field_name'
     * NOTE: Keep in mind the count of exploded data can be 1 or 3!!! This means if you wanna give alias
     * on main table field you can't omit the table (first key), alternative is just '.' e.g. '.field_name.field_alias'
     *
     * $attributes format:
     * 	- title (string) Human readable field title, constant name will be accpeted as well (multi-language support
     *
     *  - type (string) null (means system), number, text, dropdown, url, image, icon, datestamp, userclass, userclasses, user[_name|_loginname|_login|_customtitle|_email],
     *    boolean, method, ip
     *  	full/most recent reference list - e_form::renderTableRow(), e_form::renderElement(), e_admin_form_ui::renderBatchFilter()
     *  	for list of possible read/writeParms per type see below
     *
     *  - data (string) Data type, one of the following: int, integer, string, str, float, bool, boolean, model, null
     *    Default is 'str'
     *    Used only if $dataFields is not set
     *  	full/most recent reference list - e_admin_model::sanitize(), db::_getFieldValue()
     *  - dataPath (string) - xpath like path to the model/posted value. Example: 'dataPath' => 'prefix/mykey' will result in $_POST['prefix']['mykey']
     *  - primary (boolean) primary field (obsolete, $pid is now used)
     *
     *  - help (string) edit/create table - inline help, constant name will be accpeted as well, optional
     *  - note (string) edit/create table - text shown below the field title (left column), constant name will be accpeted as well, optional
     *
     *  - validate (boolean|string) any of accepted validation types (see e_validator::$_required_rules), true == 'required'
     *  - rule (string) condition for chosen above validation type (see e_validator::$_required_rules), not required for all types
     *  - error (string) Human readable error message (validation failure), constant name will be accepted as well, optional
     *
     *  - batch (boolean) list table - add current field to batch actions, in use only for boolean, dropdown, datestamp, userclass, method field types
     *    NOTE: batch may accept string values in the future...
     *  	full/most recent reference type list - e_admin_form_ui::renderBatchFilter()
     *
     *  - filter (boolean) list table - add current field to filter actions, rest is same as batch
     *
     *  - forced (boolean) list table - forced fields are always shown in list table
     *  - nolist (boolean) list table - don't show in column choice list
     *  - noedit (boolean) edit table - don't show in edit mode
     *
     *  - width (string) list table - width e.g '10%', 'auto'
     *  - thclass (string) list table header - th element class
     *  - class (string) list table body - td element additional class
     *
     *  - readParms (mixed) parameters used by core routine for showing values of current field. Structure on this attribute
     *    depends on the current field type (see below). readParams are used mainly by list page
     *
     *  - writeParms (mixed) parameters used by core routine for showing control element(s) of current field.
     *    Structure on this attribute depends on the current field type (see below).
     *    writeParams are used mainly by edit page, filter (list page), batch (list page)
     *
     * $attributes['type']->$attributes['read/writeParams'] pairs:
     *
     * - null -> read: n/a
     * 		  -> write: n/a
     *
     * - dropdown -> read: 'pre', 'post', array in format posted_html_name => value
     * 			  -> write: 'pre', 'post', array in format as required by e_form::selectbox()
     *
     * - user -> read: [optional] 'link' => true - create link to user profile, 'idField' => 'author_id' - tells to renderValue() where to search for user id (used when 'link' is true and current field is NOT ID field)
     * 				   'nameField' => 'comment_author_name' - tells to renderValue() where to search for user name (used when 'link' is true and current field is ID field)
     * 		  -> write: [optional] 'nameField' => 'comment_author_name' the name of a 'user_name' field; 'currentInit' - use currrent user if no data provided; 'current' - use always current user(editor); '__options' e_form::userpickup() options
     *
     * - number -> read: (array) [optional] 'point' => '.', [optional] 'sep' => ' ', [optional] 'decimals' => 2, [optional] 'pre' => '&euro; ', [optional] 'post' => 'LAN_CURRENCY'
     * 			-> write: (array) [optional] 'pre' => '&euro; ', [optional] 'post' => 'LAN_CURRENCY', [optional] 'maxlength' => 50, [optional] '__options' => array(...) see e_form class description for __options format
     *
     * - ip		-> read: n/a
     * 			-> write: [optional] element options array (see e_form class description for __options format)
     *
     * - text -> read: (array) [optional] 'htmltruncate' => 100, [optional] 'truncate' => 100, [optional] 'pre' => '', [optional] 'post' => ' px'
     * 		  -> write: (array) [optional] 'pre' => '', [optional] 'post' => ' px', [optional] 'maxlength' => 50 (default - 255), [optional] '__options' => array(...) see e_form class description for __options format
     *
     * - textarea 	-> read: (array) 'noparse' => '1' default 0 (disable toHTML text parsing), [optional] 'bb' => '1' (parse bbcode) default 0,
     * 								[optional] 'parse' => '' modifiers passed to e_parse::toHTML() e.g. 'BODY', [optional] 'htmltruncate' => 100,
     * 								[optional] 'truncate' => 100, [optional] 'expand' => '[more]' title for expand link, empty - no expand
     * 		  		-> write: (array) [optional] 'rows' => '' default 15, [optional] 'cols' => '' default 40, [optional] '__options' => array(...) see e_form class description for __options format
     * 								[optional] 'counter' => 0 number of max characters - has only visual effect, doesn't truncate the value (default - false)
     *
     * - bbarea -> read: same as textarea type
     * 		  	-> write: (array) [optional] 'pre' => '', [optional] 'post' => ' px', [optional] 'maxlength' => 50 (default - 0),
     * 				[optional] 'size' => [optional] - medium, small, large - default is medium,
     * 				[optional] 'counter' => 0 number of max characters - has only visual effect, doesn't truncate the value (default - false)
     *
     * - image -> read: [optional] 'title' => 'SOME_LAN' (default - LAN_PREVIEW), [optional] 'pre' => '{e_PLUGIN}myplug/images/',
     * 				'thumb' => 1 (true) or number width in pixels, 'thumb_urlraw' => 1|0 if true, it's a 'raw' url (no sc path constants),
     * 				'thumb_aw' => if 'thumb' is 1|true, this is used for Adaptive thumb width
     * 		   -> write: (array) [optional] 'label' => '', [optional] '__options' => array(...) see e_form::imagepicker() for allowed options
     *
     * - icon  -> read: [optional] 'class' => 'S16', [optional] 'pre' => '{e_PLUGIN}myplug/images/'
     * 		   -> write: (array) [optional] 'label' => '', [optional] 'ajax' => true/false , [optional] '__options' => array(...) see e_form::iconpicker() for allowed options
     *
     * - datestamp  -> read: [optional] 'mask' => 'long'|'short'|strftime() string, default is 'short'
     * 		   		-> write: (array) [optional] 'label' => '', [optional] 'ajax' => true/false , [optional] '__options' => array(...) see e_form::iconpicker() for allowed options
     *
     * - url	-> read: [optional] 'pre' => '{ePLUGIN}myplug/'|'http://somedomain.com/', 'truncate' => 50 default - no truncate, NOTE:
     * 			-> write:
     *
     * - method -> read: optional, passed to given method (the field name)
     * 			-> write: optional, passed to given method (the field name)
     *
     * - hidden -> read: 'show' => 1|0 - show hidden value, 'empty' => 'something' - what to be shown if value is empty (only id 'show' is 1)
     * 			-> write: same as readParms
     *
     * - upload -> read: n/a
     * 			-> write: Under construction
     *
     * Special attribute types:
     * - method (string) field name should be method from the current e_admin_form_ui class (or its extension).
     * 		Example call: field_name($value, $render_action, $parms) where $value is current value,
     * 		$render_action is on of the following: read|write|batch|filter, parms are currently used paramateres ( value of read/writeParms attribute).
     * 		Return type expected (by render action):
     * 			- read: list table - formatted value only
     * 			- write: edit table - form element (control)
     * 			- batch: either array('title1' => 'value1', 'title2' => 'value2', ..) or array('singleOption' => '<option value="somethig">Title</option>') or rendered option group (string '<optgroup><option>...</option></optgroup>'
     * 			- filter: same as batch
     * @var array
     */
    protected $fields = array(
        'checkboxes' => array(
            'title' => '',
            'type' => null,
            'data' => null,
            'width' => '5%',
            'thclass' => 'center',
            'forced' => true,
            'class' => 'center',
            'toggle' => 'e-multiselect'),
        'otd_id' => array(
            'title' => LAN_ID,
            'type' => 'number',
            'data' => 'int',
            'width' => '5%',
            'thclass' => '',
            'class' => 'center',
            'forced' => true,
            'primary' => true /*, 'noedit'=>TRUE*/ ), //Primary ID is not editable
        'otd_brief' => array(
            'title' => OTD_A12,
            'type' => 'text',
            'data' => 'str',
            'width' => '30%',
            'thclass' => '',
            'forced' => true,
            ), //Primary ID is not editable
        'otd_day' => array(
            'title' => OTD_A13,
            'type' => 'method',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => '',
            'batch' => true,
            'filter' => true,
            'forced' => true,
            ),
        'otd_month' => array(
            'title' => OTD_A14,
            'type' => 'method',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => '',
            'batch' => true,
            'filter' => true,
            'forced' => true),
        'otd_year' => array(
            'title' => OTD_A15,
            'type' => 'number',
            'data' => 'int',
            'width' => 'auto',
            'thclass' => '',
            'batch' => true,
            'filter' => true,
            'forced' => true),
        'otd_full' => array(
            'title' => OTD_A16,
            'type' => 'textarea',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => ''),
        'otd_poster' => array(
            'title' => LAN_AUTHOR,
            'type' => 'user',
            'data' => 'str',
            'width' => 'auto',
            'thclass' => 'left'),
        'options' => array(
            'title' => LAN_OPTIONS,
            'type' => null,
            'data' => null,
            'width' => '10%',
            'thclass' => 'center last',
            'class' => 'center last',
            'forced' => true));

    //required - default column user prefs
    protected $fieldpref = array(
        'checkboxes',
        'onthisday_id',
        'onthisday_type',
        'onthisday_url',
        'onthisday_compatibility',
        'options');

    // FORMAT field_name=>type - optional if fields 'data' attribute is set or if custom model is set in init()
    /*protected $dataFields = array();*/

    // optional, could be also set directly from $fields array with attributes 'validate' => true|'rule_name', 'rule' => 'condition_name', 'error' => 'Validation Error message'
    /*protected  $validationRules = array(
    * 'onthisday_url' => array('required', '', 'onthisday URL', 'Help text', 'not valid error message')
    * );*/

    // optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
    protected $prefs = array(
        'otd_showempty' => array(
            'title' => OTD_A12,
            'type' => 'text',
            'data' => 'string',
            'validate' => true),
        'otd_maxlength' => array(
            'title' => OTD_A18,
            'type' => 'number',
            'data' => 'integer'),
        'otd_readclass' => array(
            'title' => OTD_A10,
            'help' => OTD_H03,
            'type' => 'userclass',
            'data' => 'integer'),
        'otd_submitclass' => array(
            'title' => OTD_A56,
            'type' => 'userclass',
            'data' => 'integer'),
        'otd_adminclass' => array(
            'title' => OTD_A57,
            'type' => 'userclass',
            'data' => 'integer'),
        /*    
        'otd_showall' => array(
            'title' => OTD_A58,
            'type' => 'boolean',
            'data' => 'integer')
            */
            );

    // optional
    public function init()
    {
        $tp = e107::getParser();
        global $sql;
        if (isset($_POST['exportit']))
        {
            if (!empty($_POST['otdfname']))
            {
                $otd_filename = "./csv/" . $_POST['otdfname'];
                $otd_fh = @fopen($otd_filename, "wt");
                if ($otd_fh)
                {
                    $sql->db_Select("onthisday", "*","where otd_id>1",'',true);
                    while ($otd_row = $sql->fetch())
                    {
                       // print "WW";
                      //  var_dump($otd_row);
                        extract($otd_row);
                        $otd_date = $otd_year . "-" . $otd_month . "-" . $otd_day;
                        $otd_output = "\"" . $tp->toText($otd_brief) . "\",\"" . $otd_date . "\",\"" . $tp->toText($otd_full) . "\"\n";
                        // print $otd_output;
                        fwrite($otd_fh, $otd_output);
                    } // while
                    fclose($otd_fh);
                } else
                {
                    $otd_msg = OTD_A53;
                }
            } else
            {
                $otd_msg = OTD_A52;
            }
        }
        if (isset($_POST['importit']))
        {
            // Import the csv in to the database
            // n
            if (!empty($_POST['otdcsv']))
            {
                // die("WW");
                if (strpos(strtolower($_POST['otdcsv']), ".ics") > 0)
                {
                    $otd_filename = "./csv/" . $_POST['otdcsv'];

                    $otd_fh = fopen($otd_filename, "rbt");
                    while (!feof($otd_fh))
                    {
                        $otd_row = fgets($otd_fh, 5000);
                        // print $otd_row . "<br />";
                        if (strpos($otd_row, "BEGIN:VEVENT") === 0)
                        {
                            // print "here";
                            // start parsing an entry
                            $otd_ended = false;
                            while (!$otd_ended && !feof($otd_fh))
                            {
                                // print "there<br>";
                                $otd_event = fgets($otd_fh, 5000);
                                if (strpos($otd_event, "END:VEVENT") === 0)
                                {
                                    $otd_ended = true;
                                }
                                // print $otd_event . "<br>";
                                if (substr($otd_event, 0, 8) == "SUMMARY:")
                                {
                                    $otd_brief = substr($otd_event, 8);
                                }
                                if (substr($otd_event, 0, 12) == "DESCRIPTION:")
                                {
                                    $otd_tmp = substr($otd_event, 12);
                                    $otd_full = str_replace("\\n", "<br />", $otd_tmp);
                                    // $otd_full = "W".nl2br($otd_tmp);
                                }
                                if (substr($otd_event, 0, 19) == "DTSTART;VALUE=DATE:")
                                {
                                    $otd_date = substr($otd_event, 19);
                                    $otd_year = substr($otd_date, 0, 4);
                                    $otd_month = substr($otd_date, 4, 2);
                                    $otd_day = substr($otd_date, 6, 2);
                                }
                                // DTSTART;VALUE=DATE:20061101
                            } // while
                            $otd_arg = "0,
				'" . $tp->toDB($otd_brief) . "',
				'" . $tp->toDB($otd_day) . "',
				'" . $tp->toDB($otd_month) . "',
				'" . $tp->toDB($otd_year) . "',
				'" . $tp->toDB($otd_full) . "'," . USERID;
                            $sql->db_Insert("onthisday", $otd_arg);
                            // print $otd_brief . "<br>" . $otd_full . "<br>" . $otd_year."<br>".$otd_month."<br>".$otd_day."<br>" . "<br><hr>";
                            e107::getMessage()->addSuccess($message);
                        }
                    } // while
                    fclose($otd_fh);
                } else
                {
                    $otd_filename = "./csv/" . $_POST['otdcsv'];
                    $otd_fh = @fopen($otd_filename, "rbt");

                    if ($otd_fh)
                    {
                        while (!feof($otd_fh))
                        {
                            $otd_inarray = fgetcsv($otd_fh, 5000, ",", "\"");
                            $otd_brief = $tp->html_truncate(trim($otd_inarray[0]), 200);
                            if (!empty($otd_brief))
                            {
                                $otd_fulldate = trim($otd_inarray[1]);
                                $otd_full = trim($otd_inarray[2]);
                                $otd_temp = explode("-", $otd_fulldate);
                                $otd_year = $otd_temp[0];
                                // print $otd_row[0]." ".$otd_brief."  ".$otd_year."<br />";
                                $otd_arg = "0,
	'" . $tp->toDB($otd_brief) . "',
	'" . $tp->toDB($otd_temp[2]) . "',
	'" . $tp->toDB($otd_temp[1]) . "',
		'" . $tp->toDB($otd_temp[0]) . "',
		'" . $tp->toDB($otd_full) . "'," . USERID;
                                $sql->db_Insert("onthisday", $otd_arg, false);
                            }
                        } // while
                        $otd_msg = OTD_A49;
                    } else
                    {
                        $otd_msg = OTD_A51;
                    }
                }
            } else
            {
                $otd_msg = OTD_A50;
            }
            // $e107cache->clear("nq_otdmenu");
            //  $e107cache->clear("otd_display");
            e107::getMessage()->addSuccess($otd_msg);
            // e107::redirect(e_PLUGIN . 'onthisday/admin_config.php?mode=main&action=import');
        }

    }

    public function exportPage()
    {
        $otd_text .= "
<form method='post' action='" . e_SELF . "' id='otdform'>
    <table class='fborder' style='" . ADMIN_WIDTH . "'>
        <tr>
            <td class='fcaption' colspan='2' >" . OTD_A46 . "</td>
        </tr>
        <tr>    
            <td class='forumheader3'>" . OTD_A47 . "</td>
            <td class='forumheader3'>
                <input class='tbox' name='otdfname' style='width:40%;' type='text' />
            </td>
        </tr>
        <tr>
            <td class='fcaption' colspan='2' >
                <input type='submit' name='exportit' class='tbox' value='" . OTD_A43 . "' />
            </td>
        </tr>
    </table>
</form>";
        $ns = e107::getRender();
        $ns->tablerender("Export", $otd_text);

    }
    public function importPage()
    {
        // First get list of files
        $otd_text .= "
<form method='post' action='" . e_SELF . "' id='otdform'>
    <table class='fborder' style='" . ADMIN_WIDTH . "'>
        <tr>
            <td class='fcaption' colspan='2' >" . OTD_A40 . "</td>
        </tr>
        <tr>
            <td class='forumheader3' colspan='2' >" . OTD_A44 . "</td>
        </tr>
        <tr>
            <td class='fcaption' colspan='2' >" . OTD_A45 . "</td>
        </tr>
        <tr>
            <td class='forumheader3' style='width:30%'>" . OTD_A41 . "</td>
            <td class='fcaption' style='width:70%'>
                <select name='otdcsv' class='tbox'>
                    <option value=''>" . OTD_A48 . "</option>";
        $dir = "./csv";
        if ($otd_dirh = opendir($dir))
        {
            while (($file = readdir($otd_dirh)) !== false)
            {
                if ($file <> "." && $file <> ".." && $file <> "index.htm")
                {
                    $otd_text .= "<option value='" . $file . "'>" . $file . "</option>";
                }
            }
            closedir($otd_dirh);
        }
        $otd_text .= "
                </select>
            </td>
        </tr>
        <tr>
            <td class='fcaption' colspan='2' >
                <input type='submit' name='importit' class='tbox' value='" . OTD_A42 . "' />
            </td>
        </tr>
        <tr>
            <td class='fcaption' colspan='2' >" . OTD_A46 . "</td>
        </tr>

    </table>
</form>";
        $ns = e107::getRender();
        $ns->tablerender("Import", $otd_text);

    }
}

class plugin_onthisday_admin_form_ui extends e_admin_form_ui
{

    function otd_day($curVal, $mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function.
    {
        $frm = e107::getForm();

        for ($i = 1; $i < 32; $i++)
        {
            $types[$i] = $i;
        }
        if ($mode == 'read')
        {
            return vartrue($types[$curVal]);
        }

        if ($mode == 'batch') // Custom Batch List for otd_day
        {
            return $types;
        }

        if ($mode == 'filter') // Custom Filter List for otd_day
        {
            return $types;
        }

        return $frm->select('otd_day', $types, $curVal);
    }
    function otd_month($curVal, $mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function.
    {
        $frm = e107::getForm();

        for ($i = 1; $i < 13; $i++)
        {
            $types[$i] = $i;
        }
        if ($mode == 'read')
        {
            return vartrue($types[$curVal]);
        }

        if ($mode == 'batch') // Custom Batch List for otd_day
        {
            return $types;
        }

        if ($mode == 'filter') // Custom Filter List for otd_day
        {
            return $types;
        }

        return $frm->select('otd_month', $types, $curVal);
    }

}


/*
* After initialization we'll be able to call dispatcher via e107::getAdminUI()
* so this is the first we should do on admin page.
* Global instance variable is not needed.
* NOTE: class is auto-loaded - see class2.php __autoload()
*/
/* $dispatcher = */

new plugin_onthisday_admin();

/*
* Uncomment the below only if you disable the auto observing above
* Example: $dispatcher = new plugin_onthisday_admin(null, null, false);
*/
//$dispatcher->runObservers(true);

require_once (e_ADMIN . "auth.php");

/*
* Send page content
*/
e107::getAdminUI()->runPage();

require_once (e_ADMIN . "footer.php");

/* OBSOLETE - see admin_shortcodes::sc_admin_menu()
* function admin_config_adminmenu() 
* {
* //global $rp;
* //$rp->show_options();
* e107::getRegistry('admin/onthisday_dispatcher')->renderMenu();
* }
*/

/* OBSOLETE - done within header.php
* function headerjs() // needed for the checkboxes - how can we remove the need to duplicate this code?
* {
* return e107::getAdminUI()->getHeader();
* }
*/

?>