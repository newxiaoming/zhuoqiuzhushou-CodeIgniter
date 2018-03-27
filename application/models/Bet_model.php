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
            'avatarUrl'=>$data['avatarUrl']
        );
        
        $this->db->insert('tbl_bet', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    
    /**
     * 获取投注列表
     */
    function get_bet_list($game_id, $winner = '')
    {
        
        $this->db->select("id,money,game_id,gamer,beter_openid as bid, avatarUrl");
        $this->db->from("tbl_bet");
        $this->db->where("game_id", $game_id);
        if($winner)
        {
            $this->db->where("gamer", $winner);
        }
        $this->db->order_by("id","DESC");
        
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * 获取投注列表
     */
    function get_bet_count($game_id,$ids)
    {
        
        $count['a'] = $gamer_a_bet_count = $this->db
            ->where('gamer', $ids[0])
            ->where('game_id', $game_id)
            ->count_all_results('tbl_bet');
        
        $count['b'] = $gamer_b_bet_count = $this->db
            ->where('gamer', $ids[1])
            ->where('game_id', $game_id)
            ->count_all_results('tbl_bet');

        return $count;
    }
    
    public function check_bet($openid)
    {
        $this->db->select("id");
        $this->db->from("tbl_bet");
        $this->db->where("beter_openid", $openid);
        
        $query = $this->db->get();
        
        return $query->first_row();
    }
}