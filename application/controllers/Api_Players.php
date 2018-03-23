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
        
        $data['playersList'] = $this->players_model->list('',FALSE);
        
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
        $jscode = $this->input->get('code');
        
        $login_auth_url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . WXAPPID . '&secret=' . WXSECRET . '&js_code='.$jscode.'&grant_type=authorization_code';
        
        $this->load->helper('common');
        
        $result = curl_get($login_auth_url);
        $result = json_decode($result,true);
        
        $openid = isset($result['openid']) ? $result['openid'] : 0;
        
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
        
        $data = ['user'=>$user,'gamer'=>$gamer, 'game_id'=>$game_id, 'money'=>$money];
        
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
        
        $this->load->model('bet_model');
        $result = $this->bet_model->get_bet_list($game_id);
        
        if($result)
        {
            $this->output
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['status'=>200, 'data'=>$result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
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
}