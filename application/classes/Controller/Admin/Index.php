<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Общий базовый класс 
 */

class Controller_Admin_Index extends Controller_Admin {

    public function before() {
        parent::before();
    
    }
    public function action_index() {
        $page = View::factory('admin/page/v_index');
        
        $this->template->page = $page;
    }
    
}

