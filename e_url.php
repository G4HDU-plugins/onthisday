<?php

/*
* e107 Bootstrap CMS
*
* Copyright (C) 2008-2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
* 
* IMPORTANT: Make sure the redirect script uses the following code to load class2.php: 
* 
* 	if (!defined('e107_INIT'))
* 	{
* 		require_once("../../class2.php");
* 	}
* 
*/

if (!defined('e107_INIT'))
{
    exit;
}

// v2.x Standard  - Simple mod-rewrite module.

class onthisday_url // plugin-folder + '_url'
{
    function config()
    {
        $config = array();

        $config['index'] = array(
            'alias' => 'onthisday', // default alias '_blank'. {alias} is substituted with this value below. Allows for customization within the admin area.
            'regex' => '^{alias}/?$', // matched against url, and if true, redirected to 'redirect' below.
            'sef' => '{alias}', // used by e107::url(); to create a url from the db table.
            'redirect' => '{e_PLUGIN}onthisday/index.php?$1', // file-path of what to load when the regex returns true.

            );


        $config['view'] = array(
            'alias' => 'onthisday',
            'regex' => '^onthisday/?$', // matched against url, and if true, redirected to 'redirect' below.
            'sef' => 'onthisday', // used by e107::url(); to create a url from the db table.
            'redirect' => '{e_PLUGIN}onthisday/index.php?action=viiew&id=$1', // file-path of what to load when the regex returns true.

            );
        $config['search'] = array(
            'alias' => 'onthisday',
            'regex' => '^{alias}/\otd_id=(.*)$', // matched against url, and if true, redirected to 'redirect' below.
            'sef' => '{alias}/view-$1', // used by e107::url(); to create a url from the db table.
            'redirect' => '{e_PLUGIN}onthisday/index.php?action=view&id=$1', // file-path of what to load when the regex returns true.

            );
        return $config;
    }


}
