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
        $this->db->where("banker_openid", $openid);
        $this->db->order_by("id","DESC");
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
            $this->db->trans_start();
            $this->db->set('updated_at',date('Y-m-d H:i:s'));
            $this->db->set('status', $data['status']);
            $this->db->set('winner', $data['winner']);
            $this->db->where('banker_openid',$data['banker_openid']);
            $this->db->where('id',$data['game_id']);
            $this->db->update('tbl_game');
            
            //更新选手的胜局
            $this->db->set('updated_at',date('Y-m-d H:i:s'));
            $this->db->set('victory', 'victory+1',FALSE);
            $this->db->where('id',$data['winner']);
            $this->db->update('tbl_players');
            
            $this->db->trans_complete();
            
            return $data['winner'];
        }
        
        
        $this->db->trans_start();
        
        $game =  array(
            'banker_openid' => $data['banker_openid'],
            'players' => json_encode($data['ids']),
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>'',
            'status'=>$data['status'],
        );
        
        $this->db->insert('tbl_game', $game);
        $game_id = $this->db->insert_id();
        
        $gamers = array(
            array(
                'banker_openid' => $data['banker_openid'],
                'players' => json_encode($data['ids']),
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>'',
                'status'=>$data['status'],
                'gamer' => json_decode($data['ids'],true)[0],
                'game_id'=>$game_id,
                'type'=>'a'
            ),
            array(
                'banker_openid' => $data['banker_openid'],
                'players' => json_encode($data['ids']),
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>'',
                'status'=>$data['status'],
                'gamer' => json_decode($data['ids'],true)[1],
                'game_id'=>$game_id,
                'type'=>'b'
            )
        );
        
        $this->db->insert_batch('tbl_gamers', $gamers);
        
        //更新每个选手的比赛局数
        $this->db->set('updated_at',date('Y-m-d H:i:s'));
        $this->db->set('total', 'total+1',FALSE);        
        $this->db->where('id',json_decode($data['ids'],true)[0]);
        $this->db->or_where('id',json_decode($data['ids'],true)[1]);
        $this->db->update('tbl_players');
        
//         $this->db->set('updated_at',date('Y-m-d H:i:s'));
//         $this->db->set('total', 'total+1',FALSE);
//         $this->db->where('id',json_decode($data['ids'],true)[0]);
//         $this->db->update('tbl_players');
        
//         $players_update_data = array(
//             array(
//                 'updated_at' => date('Y-m-d H:i:s') ,
//                 'total' => 'total+1' ,
//                 'id' => json_decode($data['ids'],true)[0],
//             ),
//             array(
//                 'updated_at' => date('Y-m-d H:i:s') ,
//                 'total' => 'total+1' ,
//                 'id' => json_decode($data['ids'],true)[1]
//             )
//         );
        
//         $this->db->update_batch('tbl_players', $players_update_data, 'id');
        
        
//         $this->db->insert('tbl_game', $data);
//         $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $game_id;
    }
    
    /**
     * 获取某局的选手
     */
    function get_gamers($game_id)
    {
        $this->db->select("gamer as id,brokerage,odds");
        $this->db->from("tbl_gamers");
        $this->db->join('tbl_players','tbl_players.id = tbl_gamers.gamer');
        $this->db->where("game_id", $game_id);
        $this->db->order_by('type','DESC');
        $query = $this->db->get();
        
        return $query->result_array();
    }
    
    /**
     * 获取某局的胜利选手
     */
    function get_winner($game_id)
    {
        $this->db->select("winner");
        $this->db->from("tbl_game");
        $this->db->where("id", $game_id);
        $this->db->where("status", 'off');
        $query = $this->db->get();
        
        return $query->first_row();
    }
    
    /**
     * 获取选手信息
     */
    function get_player_info($id)
    {
        $this->db->select("id,brokerage,odds");
        $this->db->from("tbl_players");
        $this->db->where("id", $id);
        $query = $this->db->get();
        
        return $query->first_row();
    }
}