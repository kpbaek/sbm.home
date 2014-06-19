<?php
function get_message_count() 
{
	$CI = & get_instance();
	$CI->load->library('session');
	
	if($CI->session->userdata('user_id') != '')
	{          
		$config['hostname'] = "cikorea.net";
		$config['username'] = "codeigniter";
		$config['password'] = "chldyddns";
		$config['database'] = "codeigniter";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";

        $CI->load->database($config);
        
		$CI->db->select('count(no) as cnt');
		$query = $CI->db->get_where('message', array('resv_id'=>$CI->session->userdata('userid'), 'read_date'=>'0000-00-00 00:00:00'));
		$count = $query->row_array();
		
		if ($count['cnt'] > 0)
		{
			$cnt = '<font color="red">('.$count['cnt'].')</font>';
		}
		else
		{
			$cnt = '';
		}
		define('MESSAGE_COUNT', $cnt);
	}
	else
	{
		define('MESSAGE_COUNT', '');
	}
}
?>