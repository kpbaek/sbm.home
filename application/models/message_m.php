<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created on 2013-05-19
 * @author Jongwon Byun <advisor@cikore.net>
 * @version 1.0
 */
class Message_m extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
	
	function get_user_info($id) 
 	{
    	$query = $this->db->get_where('users', array('userid'=>$id))  ;
    	return $query->row_array();
 	}    
 	
 	function set_message($post, $resv_id) 
  	{
  		$arr = array(
			'send_id' => $this->session->userdata('userid'),
			'resv_id' => $resv_id,
			'reg_date' => date("Y-m-d H:i:s"),
			'contents' => $post['contents'],
			'ip' => $this->input->ip_address()
		);
		$this->db->insert('message', $arr);
		return $this->db->insert_id();
  	}
  	
  	function get_message($no, $mode, $type) //게시물 가져오기
	{
		if ($mode == 'view')
		{
			//받는 사람이 나인 경우만 업데이트
			$sql = "update `message` set read_date='".date("Y-m-d H:i:s")."' where no = '".$no."' AND resv_id = '".$this->session->userdata('userid')."' ";
			$this->db->query($sql);
  		}

		$this->db->select("message.*, users.nickname, us.nickname nick", false);
		$this->db->join('users', 'users.userid=message.send_id')  ;
		$this->db->join('users us', 'us.userid=message.resv_id')  ;

		$query = $this->db->get_where('message', array('no' => $no, 'is_delete_'.$type => 'N'));

        return $query->row_array();
	}
  	
  	function load_list($page, $rp, $post, $table, $type)
	{
		if ($type == 'r') 
  		{
  			$rows = 'resv_id';
  		}
  		else if ($type == 's') 
  		{
  			$rows = 'send_id';
  		}
		$where = "";
		$this->db->select($table.".*, users.nickname, us.nickname nick", false);
		$this->db->join('users', 'users.userid='.$table.'.send_id')  ;
		$this->db->join('users us', 'us.userid='.$table.'.resv_id')  ;
		$this->db->order_by($table.'.no', 'desc');
		$this->db->limit($rp, $page);
		if ($post) 
		{
			$this->db->like($post['method'], $post['s_word']);
  		}
  		$where .= "(".$table.".is_delete_".$type."='N' AND ".$table.".".$rows."='".$this->session->userdata('userid')."')";
  		$this->db->where($where);

		$query = $this->db->get($table);

        return $query->result_array();
	}

	function load_list_total($post, $table, $type)
	{
		if ($type == 'r') 
  		{
  			$rows = 'resv_id';
  		}
  		else if ($type == 's') 
  		{
  			$rows = 'send_id';
  		}
  		
		$this->db->select('count(no) as cnt');
		$where = "";
		if ($post) 
		{
			$this->db->like($post['method'], $post['s_word']);
		}
		$where .= "(".$table.".is_delete_".$type."='N' AND ".$table.".".$rows."='".$this->session->userdata('userid')."')";
  		$this->db->where($where);

		$query = $this->db->get($table);

        return $query->row();
	}
}