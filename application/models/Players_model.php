<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Players_model extends CI_Model
{
    /*
     * $pageing 是否分页
     */    
    function playerslist($searchText = '', $pageing = TRUE, $page=1, $segment= 1)
	{
		$this->db->select('id,name,brokerage,odds,total, victory');
		if($pageing)
		{
		    $this->db->limit($page, $segment);		    
		}
		
		$query = $this->db->get('tbl_players');
		$result = $query->result();
		$list = [];
		foreach ($result as $key=>$value)
		{
		    $list[$value->id] = $value;
		}
		return $list;
	}
	
	/**
	 * 列出选手总数
	 * @param string $searchText : 搜索的文字
	 * @return number $count : This is row count
	 */
	function listCount($searchText = '')
	{
	    $this->db->select('id,name,brokerage');
	    
	    $query = $this->db->get('players');
	    
	    
	    return count($query->result());
	}
	
	/**
	 * @desc 添加选手
	 * @return number $insert_id : This is last inserted id
	 */
	function addNewPlayer($userInfo)
	{
	    $this->db->trans_start();
	    $this->db->insert('tbl_players', $userInfo);
	    
	    $insert_id = $this->db->insert_id();
	    
	    $this->db->trans_complete();
	    
	    return $insert_id;
	}
}