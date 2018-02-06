<?php


if (!defined('e107_INIT')) { exit; }

// v2 e_search addon. 
// Removes the need for search_parser.php, search_advanced.php and in most cases search language files. 

class onthisday_search extends e_search // include plugin-folder in the name.
{
		
	function config()
	{	
		$search = array(
			'name'			=> LAN_PLUGIN_ONTHISDAY_NAME,
			'table'			=> 'onthisday as ot ',

		//	'advanced' 		=> array(
		//						'date'	=> array('type'	=> 'date', 		'text' => LAN_DATE_POSTED),
		//						'author'=> array('type'	=> 'author',	'text' => LAN_SEARCH_61)
		//					),
							
			'return_fields'	=> array('ot.otd_brief','ot.otd_full','ot.otd_id','ot.otd_year','ot.otd_month','ot.otd_day'), 
			'search_fields'	=> array('ot.otd_brief'=>1.5, 'ot.otd_full'=>1.0), // fields and weights. 
			'order'			=> array('ot.otd_year' => DESC,'ot.otd_month'=>ASC,'ot.otd_day'=>ASC),
			'refpage'		=> 'index.php'
		);


		return $search;
	}



	/* Compile Database data for output */
	function compile($row)
	{
		$res = array();

	    $res['link'] 		= $url = e107::url('onthisday','category', $row); // e_PLUGIN . "faq/faq.php?cat." . $cat_id . "." . $link_id . "";
	    $res['pre_title'] 	= $row['otd_brief'] ? $row['otd_brief'] .' | ' : "";
	    $res['title'] 		= $row['otd_brief'];
	    $res['summary'] 	= substr($row['otd_full'], 0, 100)."....  ";
	    $res['detail'] 		= e107::getParser()->toDate($row['faq_datestamp'],'long');

		return $res;
		
	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $_GET (ie. advanced fields included. in this case 'date' and 'author' )
	 */
     /*
	function where($parm='')
	{
		$tp = e107::getParser();

		$qry = " find_in_set(x.faq_info_class,'".USERCLASS_LIST."') AND ";
		
		if (vartrue($parm['time']) && is_numeric($parm['time'])) 
		{
			$qry .= " cb_datestamp ".($parm['on'] == 'new' ? '>=' : '<=')." '".(time() - $parm['time'])."' AND";
		}

		if (vartrue($parm['author'])) 
		{
			$qry .= " cb_nick LIKE '%".$tp -> toDB($parm['author'])."%' AND";
		}
		
		return $qry;
	}
	*/

}
// $search_info[]=array( 'sfile' => e_PLUGIN.'faq/search.php', 'qtype' => 'FAQ', 'refpage' => 'faq.php');
/*
include_lan(e_PLUGIN . "onthisday_menu/languages/" . e_LANGUAGE . ".php");

$otd_title = OTD_A62;
$search_info[] = array(
    'sfile' => e_PLUGIN . 'onthisday_menu/search/search.php',
    'qtype' => $otd_title,
    'refpage' => 'index.php');
*/