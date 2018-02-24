<?php

if (!defined('e107_INIT'))
{
    exit;
}


// v2.x Standard

class onthisday_rss // plugin-folder + '_rss'
{
    /**
     * Admin RSS Configuration
     */
    function config()
    {
        $config = array();

        $config[] = array(
            'name' => 'On This Day',
            'url' => 'onthisday',
            'topic_id' => '',
            'description' => 'this is the rss feed for the onthisday plugin', // that's 'description' not 'text'
            'class' => '0',
            'limit' => '9');

        return $config;
    }

    /**
     * Compile RSS Data
     * @param array $parms
     * @param string $parms['url']
     * @param int $parms['limit']
     * @param int $parms['id']
     * @return array
     */
    function data($parms = array())
    {
        $sql = e107::getDb();

        $rss = array();
        $i = 0;
        $d = (int)date('d');
        $m = (int)date('m');
        if ($items = $sql->select('onthisday', "*", "otd_day=$d and otd_month=$m LIMIT 0," . $parms['limit']))
        {

            while ($row = $sql->fetch())
            {

                //	$rss[$i]['author']			= $row['mib_user_id'];
                //	$rss[$i]['author_email']	= $row['mib_user_email'];
                $rss[$i]['link'] = "onthisday/index.php?";
                $rss[$i]['linkid'] = $row['otd_id'];
                $rss[$i]['title'] = $row['otd_brief'];
                $rss[$i]['description'] = $row['otd_full'];
                //	$rss[$i]['category_name']	= '';
                //	$rss[$i]['category_link']	= '';
                $rss[$i]['datestamp'] = $row['otd_day'] . '-' . $row['otd_month'] . '-' . $row['otd_year'];
                $rss[$i]['enc_url'] = "";
                $rss[$i]['enc_leng'] = "";
                $rss[$i]['enc_type'] = "";
                $i++;
            }

        }

        return $rss;
    }


}
