<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Game_model extends CI_Model
{
    /**
     * @desc 检查该用户是否是庄家
     * @param {string} $openid : 
     * @return {mixed} $result : This is searched result
     */
    function check_user($openid)
    {
        $this->db->select("id");
        $this->db->from("tbl_game");
        $this->db->where("status", 'on');
        $this->db->order_by("id","DESC");
        $this->db->where("banker_openid", $openid);
        
        $query = $this->db->get();
        
        return $query->first_row();
    }
    
    /**
     * @desc 插入开局数据
     * @param {string} $openid :
     * @return $insert_id
     */
    function submit_game($data)
    {
        if($data['status'] == 'off')
        {
            $this->db->set('updated_at',date('Y-m-d H:i:s'));
            $this->db->set('status', $data['status']);
            $this->db->set('winner', $data['winner']);
            $this->db->where('banker_openid',$data['banker_openid']);
            $this->db->where('id',$data['game_id']);
            $this->db->update('tbl_game');
            
            return $data['winner'];
        }
        $data = array(
            'banker_openid' => $data['banker_openid'],
            'players' => json_encode($data['ids']),
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>'',
            'status'=>$data['status']
        );
        
        $this->db->insert('tbl_game', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
}