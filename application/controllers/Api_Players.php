<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Api_Players (Api_PlayersController)
 * Api_Players Class to control all Api_Players related operations.
 * @author : lsz
 * @version : 0.1
 * @since : 2018年3月21日12:00:40
 */
class Api_Players extends CI_Controller
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('players_model');
    }
    
    /**
     * api list
     */
    public function getList()
    {
        $count = $this->players_model->listCount();
        
//         $returns = $this->paginationCompress ( "players/list", $count, 5 );
        
        $data['playersList'] = $this->players_model->playerslist('',FALSE);
        
        $this->global['title'] = '选手';
        
        $response = [
            'data'=>$data['playersList'],
            'count'=>$count
        ];
        
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
       exit;//手工调用上述输出方法而不结束脚本的执行，会导致重复输出结果。
    }
    
    /**
     * @desc 检查登录用户是庄家还是投注用户
     */
    public function check_user()
    {
        $post_data = $this->input->raw_input_stream;
        $post_data = json_decode($post_data);
        $jscode = $post_data->code;
        $iv = $post_data->iv;
        $raw_data = $post_data->rawData;
        $signature = $post_data->signature;
        $encrypt_data = $post_data->encryptData;
        $minutes = 5;//session_key 有效期
        
        $login_auth_url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . WXAPPID . '&secret=' . WXSECRET . '&js_code='.$jscode.'&grant_type=authorization_code';
        
        $this->load->helper('common');
        
        $result = curl_get($login_auth_url);
        
        $result = json_decode($result,true);
        
        $openid = isset($result['openid']) ? $result['openid'] : 0;
        
        //提取用户信息
        $user_data = json_decode($raw_data);
        
        $this->load->model('game_model');
        $banker_result = $this->game_model->check_user($openid);
        
        $respone = [
            'isbanker'=>empty($banker_result)?0:1, 
            'id'=>$openid ,
            'game_id'=>empty($banker_result)?0:$banker_result->id
        ];
        
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($respone, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
        exit;//手工调用上述输出方法而不结束脚本的执行，会导致重复输出结果。
    }
    
    /**
     * @desc 提交游戏数据
     */
    public function submit_game()
    {
        $banker_openid = $this->input->get('bk');
        $ids = $this->input->get('ids');
        $is_end = $this->input->get('_t');
        $winner = $this->input->get('_w');
        $game_id = $this->input->get('_g');
        
        $data = ['ids'=>$ids,'banker_openid'=>$banker_openid, 'winner'=>$winner,'game_id'=>$game_id,'status'=>$is_end==1?'off':'on'];
        
        $this->load->model('game_model');
        $result = $this->game_model->submit_game($data);
        if($result)
        {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(['status'=>200, 'game_id'=>$result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                ->_display();
            exit;//手工调用上述输出方法而不结束脚本的执行，会导致重复输出结果。
        }else {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(['status'=>404], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                ->_display();
            exit;//手工调用上述输出方法而不结束脚本的执行，会导致重复输出结果。
        }
    }
    
    /**
     * 投注
     */
    public function submit_bet()
    {
        $user = $this->input->get('user');
        $gamer = $this->input->get('gamer');
        $game_id = $this->input->get('_g');
        $money = $this->input->get('_m');
        $avatarUrl = $this->input->get('avatarUrl');
        
        $data = ['user'=>$user,'gamer'=>$gamer, 'game_id'=>$game_id, 'money'=>$money,'avatarUrl'=>$avatarUrl];
        
        $this->load->model('bet_model');
        $result = $this->bet_model->submit_game($data);
        if($result)
        {
            $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['status'=>200, 'game_id'=>$game_id], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
            exit;
        }else {
            $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['status'=>404], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
            exit;
        }
    }
    
    /**
     * 获取投注
     */
    public function get_bet()
    {
        $game_id = $this->input->get('_g'); 
        $ids = json_decode($this->input->get('ids'), TRUE);
        
        
        $this->load->model('bet_model');
        $result = empty($this->bet_model->get_bet_list($game_id)) ? '' : $this->bet_model->get_bet_list($game_id);
        
        $this->load->model('game_model');
        if(!$ids)
        {
            $rs = $this->game_model->get_gamers($game_id);
            foreach ($rs as $key=>$value){
                $ids[] = $value['id'];
            }
        }
        
        $open_id = $this->input->get('_id'); 
        
        $count = $this->bet_model->get_bet_count($game_id,$ids);
        
        $isbet = $this->bet_model->check_bet($open_id) ? 1: 0;
        
        //获取是否结束比赛了
        $isFinish = $this->game_model->get_winner($game_id) ? 1: 0;
        
        $this->output
        ->set_status_header(200)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode(['status'=>200, 'data'=>$result,'count'=>$count,'isbet'=>$isbet, 'isfinish'=>$isFinish], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        ->_display();
        exit;
    }
    
    public function get_bet_count()
    {
        $game_id = $this->input->get('_g');
        $ids = $this->input->get('ids');
        
        $this->load->model('bet_model');
        $result = $this->bet_model->get_bet_count($game_id);
        
        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['status'=>404, 'data'=>$result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
        exit;
    }
    
    public function get_bet_result()
    {
        $game_id = $this->input->get('_g');
//         $ids = json_decode($this->input->get('ids'), TRUE);
        
        $data['data'] = [];
        
        $this->load->model('game_model');
        $winner = $this->game_model->get_winner($game_id); 
        $winner_info = $this->game_model->get_player_info($winner->winner);
        
        $this->load->model('bet_model');
        $result = empty($this->bet_model->get_bet_list($game_id,$winner->winner)) ?  '': $this->bet_model->get_bet_list($game_id,$winner->winner);
        if($result)
        {
            $data = $this->sum($result, $winner_info);
        }
        
        
        
        $rs = $this->game_model->get_gamers($game_id);
        foreach ($rs as $key=>$value){
            $ids[] = $value['gamer'];
        }
        
        $open_id = $this->input->get('_id');
        
        $count = $this->bet_model->get_bet_count($game_id,$ids);
        
        $isbet = $this->bet_model->check_bet($open_id) ? 1: 0;
        
        
        
        $this->output
        ->set_status_header(200)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode(['status'=>200, 'data'=>$data['data'],'count'=>$count,'isbet'=>$isbet, 'winner'=>$winner->winner,'income'=>$data['winner_income']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        ->_display();
        exit;
    }
    
    public function sum($data, $winner_info)
    {
        
        $odds = explode(':', $winner_info->odds);
        $o = $odds[1]/$odds[0];//赔率
        $winner_income = 0;
        //抽成
        $brokerage = $winner_info->brokerage;
        
        foreach ($data as $key=>$value)
        {
            $winner_income += $data[$key]->revenue = $data[$key]->money * $o;//应收
            $data[$key]->income = $data[$key]->money * $o * (100- $brokerage)*0.01;//实收
        }
        return ['data'=>$data, 'winner_income'=>$winner_income * $brokerage * 0.01];
    }
    
    function getUserInofo()
    {
        $post_data = $this->input->raw_input_stream;
        $post_data = json_decode($post_data);
        $jscode = $post_data->code;
        $iv = $post_data->iv;
        $raw_data = $post_data->rawData;
        $signature = $post_data->signature;
        $encrypt_data = $post_data->encryptData;
        $minutes = 5;//session_key 有效期
        
        $login_auth_url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . WXAPPID . '&secret=' . WXSECRET . '&js_code='.$jscode.'&grant_type=authorization_code';
        
        $this->load->helper('common');
        
        $result = curl_get($login_auth_url);
        $result = json_decode($result,true);
        
        $openid = isset($result['openid']) ? $result['openid'] : 0;
        
        //提取用户信息
        $user_data = json_decode($raw_data);
        
        $this->load->model('game_model');
        $banker_result = $this->game_model->check_user($openid);
        $respone = [
            'isbanker'=>empty($banker_result)?0:1,
            'id'=>$openid ,
            'game_id'=>empty($banker_result)?0:$banker_result->id
        ];
        
        
    }
    
    public function get_gamers()
    {
        $game_id = $this->input->get('_g');
        $this->load->model('game_model');
        $data = $this->game_model->get_gamers($game_id);
        
        $this->output
        ->set_status_header(200)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode(['status'=>200, 'data'=>$data], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        ->_display();
        exit;
    }
}