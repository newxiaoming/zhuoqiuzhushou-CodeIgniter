<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 选手
 */
require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Players (PlayersController)
 * Players Class to control all Players related operations.
 * @author : lsz
 * @version : 0.1
 * @since : 2018年3月20日16:30:15
 */
class Players extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('players_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the user
     */
    public function index()
    {
        $this->global['pageTitle'] = 'CodeInsect : Dashboard';
        
        $this->loadViews("dashboard", $this->global, NULL , NULL);
    }
    
    /**
     * list
     */
    public function list()
    {
        
        $searchText = $this->input->post('searchText');
        $searchText = ''; //暂时不支持搜索
        $data['searchText'] = $searchText;
        
        $this->load->library('pagination');
        
        $count = $this->players_model->listCount();
        
        $returns = $this->paginationCompress ( "players/list", $count, 5 );
        
        $data['playersList'] = $this->players_model->list($searchText, true, $returns["page"], $returns["segment"]);
        
        $this->global['pageTitle'] = '选手';
        
        $this->loadViews("players", $this->global, $data, NULL);
    }
     

    /**
     * This function is used to load the add new form
     */
    function addNew()
    {        
        $this->global['pageTitle'] = '添加选手';
        
        $this->loadViews("addPlayer", $this->global, NULL, NULL);
    }

    /**
     * @desc 还没开放
     */
    function checkPlayerExists()
    {
        $userId = $this->input->post("userId");
        $email = $this->input->post("email");

        if(empty($userId)){
            $result = $this->user_model->checkEmailExists($email);
        } else {
            $result = $this->user_model->checkEmailExists($email, $userId);
        }

        if(empty($result)){ echo("true"); }
        else { echo("false"); }
    }
    
    /**
     * This function is used to add new user to the system
     */
    function addNewPlayer()
    {
        if($this->isAdmin() == TRUE)
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('name','选手名字','trim|required|max_length[128]');
            $this->form_validation->set_rules('brokerage','抽成','trim|required|integer|max_length[128]');
            $this->form_validation->set_rules('odds','赔率','trim|required');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->addNew();
            }
            else
            {
                $name = ucwords(strtolower($this->security->xss_clean($this->input->post('name'))));
                $brokerage = $this->security->xss_clean($this->input->post('brokerage'));
                $odds = $this->input->post('odds');
                
                $userInfo = array('name'=>$name, 'brokerage'=>$brokerage, 'odds'=>$odds, 'created_at'=>$this->vendorId, 'created_at'=>date('Y-m-d H:i:s'));
                
                $this->load->model('players_model');
                $result = $this->players_model->addNewPlayer($userInfo);
                
                if($result > 0)
                {
                    $this->session->set_flashdata('success', '新选手添加成功');
                }
                else
                {
                    $this->session->set_flashdata('error', '添加失败');
                }
                
                redirect('players/list');
            }
        }
    }

    
}

?>