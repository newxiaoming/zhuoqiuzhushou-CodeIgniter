<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Bet_model extends CI_Model
{
    /**
     * @desc 插入投注信息
     * @param {string} $openid :
     * @return $insert_id
     */
    function submit_game($data)
    {
        
        $data = array(
            'beter_openid' => $data['user'],
            'game_id' => $data['game_id'],
            'created_at'=>date('Y-m-d H:i:s'),
            'gamer' => $data['gamer'],
            'money' => $data['money'],
        );
        
        $this->db->insert('tbl_bet', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    
    /**
     * 获取投注列表
     */
    function get_bet_list($game_id)
    {
        
        $this->db->select("id,money,game_id,gamer");
        $this->db->from("tbl_bet");
        $this->db->where("game_id", $game_id);
        $this->db->order_by("id","DESC");
        
        $query = $this->db->get();
        
        return $query->result();
    }
}