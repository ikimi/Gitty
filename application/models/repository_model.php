<?php if( ! defined('BASEPATH')) exit('No direct script access allowed!');

class Repository_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}	

	public function select_creates($username)
	{
		$this->db->select('username,repo_name');
		$query = $this->db->get_where('creates',array('username' => $username));
		$temp = $query->result_array();
		$result = array();

		foreach($temp as $item)
		{
			$this->db->select('repo_name,creator,description,create_date,update_date');
			$query = $this->db->get_where('repositories',array('repo_name' => $item['repo_name'],'creator' => $item['username'],'owner' => $item['username']));
			$result[] = $query->row_array();
		}
		return $result;
	}

	public function select_forks($username)
	{
		$this->db->select('repo_name,username');
		$query = $this->db->get_where('forks',array('username' => $username));
		$temp = $query->result_array();
		$result = array();

		foreach($temp as $item)
		{
			$this->db->select('repo_name,creator,owner,description,create_date,update_date');
			$query = $this->db->get_where('repositories',array('repo_name' => $item['repo_name'],'owner' => $item['username']));
			$result[] = $query->row_array();
		}
		return $result;
	}

	public function select_participates($username)
	{
		$this->db->select('repo_name,username');
		$query = $this->db->get_where('participates',array('username' => $username));
		$temp = $query->result_array();
		$result = array();

		foreach($temp as $item)
		{
			$this->db->select('repo_name,creator,description,create_date,update_date');	
			$query = $this->db->get_where('repositories',array('repo_name' => $item['repo_name'],'owner' => $item['username']));
			$result[] = $query->row_array();
		}
		return $result;
	}

	public function search($keyword)
	{
		// 模糊查询创建的项目
		$this->db->like(array('repo_name' => $keyword));
		$this->db->select('repo_name,username');
		$query = $this->db->get('creates');
		$result['creates'] = $query->result_array();

		// 模糊查询克隆的项目
		$this->db->like(array('repo_name' => $keyword));
		$this->db->select('repo_name,username,creator');
		$query = $this->db->get('forks');
		$result['forks'] = $query->result_array();

		// 模糊查询参与项目
		$this->db->like(array('repo_name' => $keyword));
		$this->db->select('repo_name,username,creator');
		return $result;
	}

	// 判断 username 用户的 reponame 项目是否进行了推送
	public function db_is_empty($username,$reponame)
	{
		// 查询 creates 表
		$this->db->select('HEAD');
		$query = $this->db->get_where('creates',array('username' => $username,'repo_name' => $reponame));
		$result = $query->row_array();
		if(!empty($result['HEAD']))
		{
			$result['empty'] = FALSE;
			$result['table'] = 'creates';
			return $result;
		}

		// 查询 forks 表
		$this->db->select('HEAD');
		$query = $this->db->get_where('forks',array('username' => $username,'repo_name' => $reponame));
		$result = $query->row_array();
		if(!empty($result['HEAD']))
	   	{
			$result['empty'] = FALSE;
			$result['table'] = 'forks';
			return $result;
		}

		// 查询 participates 表
		$this->db->select('HEAD');
		$query = $this->db->get_where('participates',array('username' => $username,'repo_name' => $reponame));
		$result = $query->row_array();
		if(!empty($result['HEAD']))
		{
			$result['empty'] = FALSE;
			$reuslt['table'] = 'participates';
			return $result;
		}
		$result['empty'] = TRUE;
		return $result;
	}

	// 检查 git 版本库是否为空
	public function git_is_empty($username,$reponame)
	{
		$result = array();
		exec("./scripts/rev-parse.sh $username $reponame HEAD",$result);
		if($result[0] == 'HEAD')
			return TRUE;
		return false;
	}

	public function commits_init($username,$reponame)
	{
	
	}

	// 检查 commits 是否更新
	public function commits_update($username,$reponame)
	{
	}

	public function create_repo($data)
	{
		// 插入数据到数据库
		$data['create_date'] = date("Y-m-d");
		$data['update_date'] = $data['create_date'];
		$query1 = $this->db->insert_string('repositories',$data);
		$this->db->query($query1);
		$query2 = $this->db->insert_string('creates',array('username' => $data['creator'],'repo_name' => $data['repo_name']));
		$this->db->query($query2);

		// 在 server 上创建相应的裸仓库 
		// 在 server 上进行相应的配置
		// 修改 gitosis.conf 文件
		$this->load->helper('file');
		$tmp = "\n[group ".strtotime("now")."]\nmembers = ".$data['creator']."\nwritable = ".$data['creator'].'@'.$data['repo_name']."\n";
		write_file('./gitosis-conf/gitosis-admin/gitosis.conf',$tmp,'a+');
	}
}

/* End of file respository_model.php */
/* Location: ./application/models/respository_model.php */
