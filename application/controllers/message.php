<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Message extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('message_m');
		
		$this->seg_exp = segment_explode($this->uri->uri_string());
		$this->output->enable_profiler(false);
	}
	
	/**
     * 사이트 헤더, 푸터를 자동으로 추가해준다.
     *
     */
    public function _remap($method)
    {
        //헤더 include
        if(!strpos($method, '_ajax'))
        {
        	if ($method == 'lists' or $method == 'views') 
         	{
                $this->load->view('top_v');
       		}
       		else
       		{
       			$this->load->view('top0_v');
       		}
                
        }
        

        if( method_exists($this, $method) )
        {
            $this->{"{$method}"}();
        }

        //푸터 include
        if(!strpos($method, '_ajax'))
        {
        	if ($method == 'lists' or $method == 'views') 
         	{
                $this->load->view('bottom_v');
       		}
       		else
       		{
            	$this->load->view('bottom0_v');
        	}
        }
    }

	function index()
	{
		$this->send();
	}
	
	function send()
	{
		if ($this->session->userdata('userid'))
		{
			$resv_id = $this->uri->segment(3);
			       
			$this->load->library('form_validation');

	        $this->form_validation->set_rules('contents', '보낼 내용', 'required|xss_clean');
	
			if ($this->form_validation->run() == TRUE)
			{
				$post = $this->input->post(NULL, TRUE);
				
				$result = $this->message_m->set_message($post, $resv_id);
				if ($result) 
    			{
                	echo "<script>alert('발송되었습니다.'); self.close();</script>";
    			}    
    			else
    			{
    				echo "<script>alert('발송실패'); document.location='/message/send/".$resv_id."';</script>";
    			}
			}
			else
			{
				$data['user_info'] = $this->message_m->get_user_info($resv_id);
				$this->load->view('message/send_v', $data);
			}
		}
		else
		{
			echo "<script>alert('로그인해야 합니다.'); self.close();</script>";
		}
	}
	
	//게시판 목록
	function lists()
	{
		if ($this->session->userdata('userid'))
		{
			$data['type'] = $type = $this->uri->segment(3);  
			//주소중에서 q(검색어) 세그먼트가 있는지 검사하기 위해 주소를 배열로 변환
			$uri_array = segment_explode($this->uri->uri_string());
			$search_word = '';
			$sfl = '';
	
			if( in_array('q', $uri_array) )
			{
				$data['search_word'] = $search_word = urldecode($this->security->xss_clean(url_explode($uri_array, 'q')));
				//주소에서 q 제거. 뷰에서 사용
				$uri_array =  url_delete($uri_array, 'q');
				$data['search_url'] = "q/".$search_word;
			}
			else
			{
				$data['search_word'] = '';
				$data['search_url'] = '';
			}
	
			if( in_array('sfl', $uri_array) )
			{
				$data['search_sfl'] = $sfl = urldecode($this->security->xss_clean(url_explode($uri_array, 'sfl')));
				//주소에서 sfl 제거
				$uri_array = url_delete($uri_array, 'sfl');
			}
			else
			{
				$data['search_sfl'] = '';
			}
	
	
	
			$data['url'] = '/'.implode('/', $uri_array);
	
			if( $search_word != '' AND $sfl != '' )
			{
				$post = array('method'=>$sfl, 's_word'=>$search_word);
			}
			else
			{
				$post = '';
			}
	
			//페이징변수
			if( in_array('page', $uri_array) )
			{
				$data['page_account'] = $page = urldecode($this->security->xss_clean(url_explode($uri_array, 'page')));
			}
			else
			{
				$data['page_account'] = $page = 1;
			}
	
			if(!is_numeric($page))
			{
				$data['page_account'] = $page = 1;
			}
	
	
			//게시물 전체 갯수
			$tot_cnt = $this->message_m->load_list_total($post, 'message', $type);
			$data['list_total'] = $total = $tot_cnt->cnt;
	
			$rp = 20; //리스트 갯수
			$limit = 9; //보여줄 페이지수
	
			$start = (($page-1) * $rp);
	
			//검색후 페이징처리를 위해 주소에서 page를 삭제
			$this->url_seg = $this->seg_exp;
			$urls = implode('/', url_delete($this->url_seg, 'page'));
	
			//common helper에 페이징 함수 존재
			$data['pagination_links'] = pagination($urls."/page", paging($page,$rp,$total,$limit));
	
			$data['list'] = $this->message_m->load_list($start, $rp, $post, 'message',$type);
	
			$this->load->view('message/lists_v', $data);
   		}
	   	else
		{
			$rpath = str_replace("index.php/", "", $this->input->server('PHP_SELF'));
			$rpath_encode = base64_encode($rpath);
			alert('로그인해야 합니다.', '/auth/login/'.$rpath_encode);
		}
	}

    function views()
	{
		if ($this->session->userdata('userid'))
		{	
	 		$data['type'] = $type = $this->uri->segment(4);
	
			$this->load->library('form_validation');
	        $this->form_validation->set_rules('contents', '쪽지 내용', 'required');
	
			if ($this->form_validation->run() == false)
			{
				$data['views'] = $this->message_m->get_message($this->uri->segment(3), 'view', $type);
				
				$this->load->view('/message/view_v', $data);
			}
			else
			{
				//전송후 리스트로
				$post = $this->input->post(NULL, TRUE);
				
				$result = $this->message_m->set_message($post, $this->uri->segment(4));
				if ($result) 
    			{
                	alert('발송되었습니다.', '/message/lists/s');
    			}    
    			else
    			{
    				echo "<script>alert('발송실패'); history.go(-1);</script>";
    			}
			}
		}
	   	else
		{
			$rpath = str_replace("index.php/", "", $this->input->server('PHP_SELF'));
			$rpath_encode = base64_encode($rpath);
			alert('로그인해야 합니다.', '/auth/login/'.$rpath_encode);
		}
	}

	function delete()
	{
		$no = $this->uri->segment(3);
		$u_no=$this->db->get_where('message', array('no'=>$no));
		$row=$u_no->row();
		$a = 0;
		if($row->send_id == $this->session->userdata('userid'))
		{
			$data = array('is_delete_s' => 'Y');
			$this->db->where('no', $no);
			$this->db->update('message', $data);
			$a++;
		}
		else if($row->resv_id == $this->session->userdata('userid'))
		{
			$data = array('is_delete_r' => 'Y');
			$this->db->where('no', $no);
			$this->db->update('message', $data);
			$a++;
		}
		if ($a > 0)
		{
			alert('삭제되었습니다.', '/message/lists/r');
		}
		else
		{
			alert('삭제할 수 없습니다.', '/message/lists/r');
		}
		
	}

	
}