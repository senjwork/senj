<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Общий базовый класс 
 */

class Controller_Page_Auth extends Controller_Base {

    public function before() {
        parent::before();
    
    }
    public function action_logout() {
        $this->session->set('reff_isset', false);   
        $this->auth->logout();
        if(isset($_SERVER['HTTP_REFERER'])){
            HTTP::redirect($_SERVER['HTTP_REFERER']);
        } else{
            HTTP::redirect('http://'.$this->domain_name.'/login');
        }
    }
    public function action_login() {
        $l = $this->session->get('reff_isset', false);
        if(!$l){
            if(isset($_SERVER['HTTP_REFERER']))
                $this->session->set('reff', $_SERVER['HTTP_REFERER']);            
        }
         $this->session->set('reff_isset', true);    
        
        if(isset($_POST['email'])){
                $user = ORM::factory('User');

                $users = $user->values($_POST);
                $post = func::HardCheckQuery($_POST);

                $remember = ($post['remember'] == 'on');
                if ($users->check()) {
                    if ($this->auth->login($post['email'], $post['password'], $remember)) {
                        $this->user = $this->auth->get_user();
                        $this->user->last_login = time();
                        $this->user->save();
                        // отравляем туда откуда пришел
                        HTTP::redirect($this->session->get('reff', '/'));
                    } else {
//                       $errors = $users->error('email', 'no_user', array(':param1' => 'password'));
//                        $errors = $users->errors('validation');
                    }
                } else {
                     $errors = $users->errors('validation');
                }
            }
    }
    
}

