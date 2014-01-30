<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Общий базовый класс 
 */

class Controller_Page_Php extends Controller_Base {

    public function before() {
        parent::before();
    
    }
    public function action_index() {
        $page = View::factory('page/v_php');
        
        $this->template->page = $page;
    }
    
}

