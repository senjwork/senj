<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Общий базовый класс 
 */

class Controller_Base extends Controller {

    protected $auth;
    protected $session; 
    protected $user;
    protected $domain_name;
    public $template = '';
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
        
        $this->template = View::factory('v_base');
        
        // верхнее меню
        $list_menu_top = array(
            ''=>'Главная',
            'php'=>'PHP',
            'sql'=>'SQL',
            'jquery'=>'JQuery'
        );
        $menu_top = View::factory('block/v_menu');
        $this->template->menu = $menu_top;
        $this->template->menu->list = $list_menu_top;
        
        
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
               'media/css/block/menu.css',
               'media/css/block/menu_left.css',
               'media/css/block/footer.css',
                'media/css/'.mb_strtolower($this->request->directory()).'/'.mb_strtolower($this->request->controller()).'.css'
           );
         
            // Подключаем скрипты
            $this->template->scripts = array(
                'media/libs/jquery.js',
                'media/libs/jquery.pjax.js',
                'media/js/js.js', 
                 'media/js/'.mb_strtolower($this->request->directory()).'/'.mb_strtolower($this->request->controller()).'.js'
            );
        }
        if(isset($_GET['_pjax'])){
                    echo '<script type="text/javascript">';
                    echo '$("body").removeAttr("class");';
                    
                    foreach ( $this->template->styles as $styles){
                        echo 'if($("head link[href=\"/'.$styles.'\"]").length == 0){
                        $("head").append("<link>");
                            css = $("head").children(":last");
                            css.attr({
                            rel:  "stylesheet",
                            type: "text/css",
                            href: "/'.$styles.'"
                            });
                        }';
                    }
                    foreach ($this->template->scripts as $script){
                        echo 'if($("head script[src=\"/'.$script.'\"]").length == 0){';
                        echo 'var s = document.createElement("script");';
                        echo 's.type = "text/javascript";s.src = "/'.$script.'";';
                        echo 'document.head.appendChild(s);';
                         echo'}';
                    }
                echo '</script>';
                }     
    }
}