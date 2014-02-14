<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Общий базовый класс 
 */

class Controller_Page_Php extends Controller_Base {

    public function before() {
        parent::before();
    
        $this->template->page = View::factory('page/v_php');
        
         // левое меню
        $list_menu_top = array(
            'php'=>__('Библиотеки'),
            'php/scripts'=>__('Скрипты')
        );
        
        $menu_left = View::factory('block/v_menu_left');
        $this->template->page->left_menu = $menu_left;
        $this->template->page->left_menu->list = $list_menu_top;
        
        
    }
    public function action_index() {
        
        $this->template->title = __('PHP библиотеки');
        if (isset($_GET['_pjax'])) {
                    echo '<title>'.$this->template->title.'</title>' ;                           
                    echo $this->template->page ;           
        } else {
            $this->response->body($this->template->render());
        }
    }
    public function action_scripts() {
        
        $this->template->title = __('PHP скрипты');
        if (isset($_GET['_pjax'])) {
                    echo '<title>'.$this->template->title.'</title>' ;                           
                    echo $this->template->page ;           
        } else {
            $this->response->body($this->template->render());
        }
    }
    
}

