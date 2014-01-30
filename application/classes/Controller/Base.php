<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Общий базовый класс 
 */

class Controller_Base extends Controller_Template {

    protected $auth;
    protected $session; 
    protected $user;
    protected $domain_name;
    public $template = 'v_base';
    public $auth_required = false;
    public $auto_render = true;

    public function before() {
        parent::before();
        
        // Доменое имя(глобальная переменая)
        $subdomain_mass = explode('.', $_SERVER['SERVER_NAME']); if (count($subdomain_mass) == 3) {$this->domain_name = $subdomain_mass[1] . '.' . $subdomain_mass[2];} else {$this->domain_name = $subdomain_mass[0] . '.' . $subdomain_mass[1];}
        View::set_global('domain_name', $this->domain_name);
        
        // Авторизация
        $this->auth = Auth::instance();
        $this->user = $this->auth->get_user(); /* получаем данные авторизированого пользователя */
        $this->session = Session::instance();
        
        // верхнее меню
        $menu = View::factory('block/v_menu');
        $this->template->menu = $menu;

       
        // footer
        $footer = View::factory('block/v_footer');
        $this->template->footer = $footer;
        
        if ($this->auto_render) {
          
            // Вывод в шаблон
            $this->template->title = __('Записи программиста'); /* добавляем значение в title */
            $this->template->keywords = __('sql,html,php,css,javascript,ajax,jquery'); /* добавляем значение в keywords */
            $this->template->description = __('Записи программиста'); /* добавляем значение в keywords */

         
            // Подключаем стили
           $this->template->styles = array(
               'media/css/style.css',
               'media/css/menu.css',
           );
         
            // Подключаем скрипты
            $this->template->scripts = array(
                'media/libs/jquery.min.js',
                'media/libs/jquery.pjax.js',
                'media/js/js.js',                
            );
        }     
    }    
}