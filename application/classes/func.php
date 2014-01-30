<?php defined('SYSPATH') or die('No direct script access.');

class func {
     public static function HardCheckQuery($query){
        if(is_array($query)){
            foreach ($query as $value) {
                if(is_string($value)){
                    $value = trim($value);
                    $value = stripslashes($value);
                    $value = strip_tags($value);
                    $value = htmlspecialchars($value);
                }elseif(is_array($value)){
                     foreach ($value as $val) {
                         if(is_string($val)){
                            $val = trim($val);
                            $val = stripslashes($val);
                            $val = strip_tags($val);
                            $val = htmlspecialchars($val);
                         }
                     }
                }
            }
        }else{
            $query = trim($query);
            $query = stripslashes($query);
            $query = strip_tags($query);
            $query = htmlspecialchars($query);
        }
        return $query;
    }
     public static function HardSQL($query) {
         if(is_array($query)){
            foreach ($query as $value) {
                if(is_string($value)){
                    $value = strip_tags($value);
                    $value = mysql_real_escape_string(trim($value));
                }elseif(is_array($value)){
                     foreach ($value as $val) {
                         if(is_string($val)){
                            $val = strip_tags($val);
                            $val = mysql_real_escape_string(trim($val));
                         }
                     }
                }
            }
        }else{
            $query = strip_tags($query);
            $query = mysql_real_escape_string(trim($query));
        }
         return $query;
     }
    public static function generateCode($length = NULL) {
        if($length == NULL)$length = 8;
        $symbols = '0123456789qwertHPTOEyuiopSDGEasdfghjklzxcvbnm';

        $string = '';
        for ($i = 0; $i < $length; $i++) {
                $key = rand(0, strlen($symbols)-1);
                $string .= $symbols[$key];
        }
        return $string;
    }
    public static function generatelogin() {
        
        $symbols = '0123456789';

        $string = '';
        for ($i = 0; $i < 17; $i++) {
                $key = rand(0, strlen($symbols)-1);
                $string .= $symbols[$key];
        }
        if(!ORM::factory('User')->where('username', '=', $string)->find()->loaded())
            return $string;
        else
            func::generatelogin();
    }
    

    public static function GetIp(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
          $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /*
     * $array = array(__("пользователь"), __("пользователя"), __("пользователей"));
     * functions::getWord($chell_cout, $array)
     */
    public static function getWord($number, $suffix) {
        $keys = array(2, 0, 1, 1, 1, 2);
        $mod = $number % 100;
        $suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
        return $suffix[$suffix_key];
      }
      
    /*
     * functions::startMail($id)
     */
    public static function startMail($banner_id, $user_id) {
        if (is_numeric($banner_id)&& is_numeric($user_id)) {

            $mail = ORM::factory('baner')
                ->where('id', '=', (int) $banner_id)
                ->where('users_id', '=', (int)$user_id)
                ->where('del', '=', 0)
                ->where('type', '=', 2)
                ->where('balans', '>=', "'price'")
                ->find();
            if ($mail->loaded()) {
                $users_id = ORM::factory('advertdistrib')
                    ->where('from', '=', $mail->users_id)
                    ->where('banner_id', '=', $mail->id)
                    ->where('replay', '=', $mail->replay)
                    ->find_all();
                $user_id = '';
                $id_arr = '';
                $i = 1;
                if (count($users_id) > 0) {
                    foreach ($users_id as $us_id) {
                        if(is_numeric($us_id->user_id)){
                            if ($i == 1) {
                                $id_arr .= "'" . $us_id->user_id . "'";
                                $i++;
                            }
                            else
                                $id_arr .= ",'" . $us_id->user_id . "'";
                        }
                        
                        
                    }
                    $user_id = " users.id not in (" . $id_arr . ")";
                }
                $limit = "limit " . floor(bcdiv($mail->balans, $mail->price, 10));
                $y = 0;
                if (bccomp($mail->balans, bcmul($mail->price, $i, 10),10) == 1  || bccomp($mail->balans, bcmul($mail->price, $i, 10),10) == 0) {

                    $users = functions::count_banner($mail->as_array(), 'as_array', $limit, $user_id);
                    $users_count = functions::count_banner($mail->as_array(), 'count', $limit, $user_id);
                    if ($users_count > 999)
                        $max_str = 999;
                    if ($users_count < 999)
                        $max_str = $users_count;
                    $cou = 0;
                    foreach ($users as $user) {
                        if ($cou < $max_str && $cou != 0) {
                            $sql .= ", ('" . $mail->users_id . "', '" . $mail->id . "', '" . $user['id'] . "', '" . time() . "', '" . $mail->replay. "')";
                        }
                        if ($cou == 0) {
                            $sql = "
                                    INSERT into `advert_distrib` (`from`, `banner_id`, `user_id`, `time`, `replay`)
                                    values ('" . $mail->users_id . "', '" . $mail->id . "', '" . $user['id'] . "', '" . time() ."', '" . $mail->replay. "')";
                        }
                        $cou++;
                        if ($cou == $max_str) {
                            $sql .= ";";
                            DB::query(Database::INSERT, $sql)->execute();
                            $cou = 0;
                        }
                        $y++;
                    }
                    if ($max_str != $users_count && $max_str != 0) {
                        $sql .= ";";
                        DB::query(Database::INSERT, $sql)->execute();
                    }
                    $mail->status = 1;
                    $mail->limit_naw += $y;
                    $mail->save();
                    $users = true;
                } else {
                    $users = false;
                }
            }
        }
       return $y;
      }
    public static function stopMail($mail) {
        if (is_object($mail)) {
//            $users_count = ORM::factory('advertdistrib')
//                ->where('from', '=', $mail->users_id)
//                ->where('banner_id', '=', $mail->id)
//                ->where('status', '=', 0)
//                ->count_all();
            $del_mail = ORM::factory('advertdistrib')
                ->where('from', '=', $mail->users_id)
                ->where('banner_id', '=', $mail->id)
                ->where('replay', '=', $mail->replay)
                ->where_open()
                ->where('status', '=', '3')
                ->or_where('status', '=', '0')
                ->where_close()
                ->find_all();
            if(is_array($del_mail)){
                foreach ($del_mail as $val){
                    $val->delete();
                }
            }
//            else{
////                $del_mail[0]->delete();
//            }
            if (bccomp($mail->balans,0, 10) == 1) {
                $user = ORM::factory('user')->where('id', '=', $mail->users_id)->find();
                $user->money = bcadd($user->money, $mail->balans, 10);
                $user->save();

                $i = $mail->balans;

                $history = ORM::factory('moneys')
                    ->select(DB::expr('SUM("sum") as "sums"'))
                    ->where('id_user', '=', $mail->users_id)
                    ->where('where', '=', 3)
                    ->where('status', '=', 0)
                    ->where('user_id', '=', $mail->id)
                    ->order_by('time_pay', 'desc')
                    ->find();
                if(bccomp($history->sums,0,10) == 1){
                    while (bccomp($i,0,10) == 1) {
                        $history = ORM::factory('moneys')
                            ->where('id_user', '=', $mail->users_id)
                            ->where('where', '=', 3)
                            ->where('status', '=', 0)
                            ->where('user_id', '=', $mail->id)
                            ->order_by('time_pay', 'desc')
                            ->find();
                        $histsum = $history->sum;
                        if (bccomp($history->sum, $i, 10) == -1 || bccomp($history->sum, $i, 10) == 0)
                            $history->delete();
                        elseif (bccomp($history->sum, $i, 10) == 1) {
                            $history->sum = bcsub($history->sum, $i, 10);
                            $history->save();
                        }
                        $i = bcsub($i, $histsum, 10);
                    }
                }
                //================================== с истории списываем
                $mail->limit_naw = 0;
                $mail->status = 2;
                $mail->balans = 0;
                
                $mail->save();
//                 $sql = "
//                    UPDATE 
//                        `advert_distrib` 
//                    SET
//                        `status` = 3
//                    WHERE `from` = " . $this->user->id . "
//                    AND `banner_id` = " . $mail->id . "
//                    AND `status` = 0
//                ";
//                DB::query(Database::UPDATE, $sql)->execute();

//            $users_delete = ORM::factory('advertdistrib')
//                ->where('from', '=', $this->user->id)
//                ->where('banner_id', '=', $mail->id)
//                ->where('status', '=', 0)
//                ->delete_all();
            }
        }
       return true;
      }

    /*
     * подсчет и выборка банера и рассылки
     * $users = functions::count_banner($mail->as_array(), 'as_array', $limit, $user_id);
       $users_count = functions::count_banner($mail->as_array(), 'count', $limit, $user_id);
     */
    public static function count_banner($baner, $result, $limit = "", $user_id = "") {
//         if(is_object($baner)){
//             $baner = (array)$baner;
//        foreach ($baner as $key =>$val){
//            echo $key.' - '.$val.'<br>';
//            if(is_array($val)){
//                foreach ($val as $key1 =>$val1){
//                    echo '------'.$key1.' - '.$val1.'<br>';
//                     $baner[$key1] = $val1;
//                }
//            }
//        }
//      
//        }
       
       
//advertisement_user 
        $interes = '';
// интересы
        if (isset($baner['iteres']) && !empty($baner['iteres']) && $baner['iteres'] != '') {
            $inter_mass = explode(',', $baner['iteres']);
            $q = 0;
            foreach ($inter_mass as $item) {
                if (is_numeric($item)) {
                    if ($q == 0) {
                        $q = 1;
                        $interes .= " JOIN advertisement_user  ON advertisement_user.user_id = users.id AND advertisement_user.page = 'distributions'  AND (advertisement_user.tuningofadvertisement_id LIKE ',%$item%,' ";
                    } else {
                        $interes .= " OR advertisement_user.tuningofadvertisement_id LIKE ',%$item%,' ";
                    }
                }
            }
            if ($q == 1)
                $interes .=')';
        }
// _user_privat_data
        $prev = '';
// джойним таблицу
        if ($baner['sex'] != 2 || $baner['age_start'] != 6 || $baner['age_stop'] != 90 || $baner['birthday'] != 0 || $baner['region'] != '0') {
            $prev .= ' JOIN _user_privat_data ON _user_privat_data.user_id = users.id  ';
        }
// день рождения пользователя, девушки || парня, мамы, папы, бабушки, дедушки, брата, сестры
        if ($baner['birthday'] != 0) {
            if ($baner['birthday'] == 1) {
                $prev .= " 
                            JOIN _my_info_famely ON _my_info_famely.user_id = users.id
                            AND 
                            (
                                (
                                    MONTH(_user_privat_data.birth_date)  = " . date('n', time()) . " 
                                    AND
                                    DAY(_user_privat_data.birth_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_girl_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.birth_girl_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child1b_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.child1b_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child2b_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.child2b_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child3b_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.child3b_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_mom_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.birth_mom_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_father_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.birth_father_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_sister_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.birth_sister_date)  = " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_brother_date)= " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.birth_brother_date)= " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_grandmom_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.birth_grandmom_date)= " . date('d', time()) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_gfather_date)  = " . date('n', time()) . " 
                                    AND DAY(_my_info_famely.birth_gfather_date)  = " . date('d', time()) . "
                                )
                            )
                                ";
            }
            if ($baner['birthday'] == 2) {
                $tim = mktime(0, 0, 0, date('n'), date('d') + 1, date('Y'));
                $prev .= " 
                            JOIN _my_info_famely ON _my_info_famely.user_id = users.id
                            AND 
                            (
                                (
                                    MONTH(_user_privat_data.birth_date)  = " . date('n', $tim) . "
                                    AND
                                    DAY(_user_privat_data.birth_date)  = " . date('d', $tim) . " 
                                )
                                OR
                                (
                                    MONTH(_my_info_famely.birth_girl_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.birth_girl_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child1b_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.child1b_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child2b_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.child2b_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child3b)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.child3b)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_mom_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.birth_mom_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_father_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.birth_father_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_sister_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.birth_sister_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_brother_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.birth_brother_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_grandmom_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.birth_grandmom_date)  = " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_gfather_date)  = " . date('n', $tim) . " 
                                    AND DAY(_my_info_famely.birth_gfather_date)  = " . date('d', $tim) . "
                                )
                            )

                            
                            ";
            }
            if ($baner['birthday'] == 3) {
                $tim = mktime(0, 0, 0, date('n'), date('d') + 7, 1970);
                if (date('n', $tim) == date('n')) {

                    $prev .= " 
                            JOIN _my_info_famely ON _my_info_famely.user_id = users.id
                            AND
                            (
                                (
                                    MONTH(_user_privat_data.birth_date)  = " . date('n') . "
                                    AND  DAY(_user_privat_data.birth_date)  >= " . date('d') . "     
                                    AND  DAY(_user_privat_data.birth_date)  <= " . date('d', $tim) . " 
                                )
                                OR
                                (
                                    MONTH(_my_info_famely.birth_girl_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.birth_girl_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.birth_girl_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child1b_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.child1b_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.child1b_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child2b_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.child2b_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.child2b_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.child3b_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.child3b_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.child3b_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_mom_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.birth_mom_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.birth_mom_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_father_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.birth_father_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.birth_father_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_sister_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.birth_sister_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.birth_sister_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_brother_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.birth_brother_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.birth_brother_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_grandmom_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.birth_grandmom_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.birth_grandmom_date)  <= " . date('d', $tim) . "
                                )
                                    OR
                                (
                                    MONTH(_my_info_famely.birth_gfather_date)  = " . date('n') . " 
                                    AND DAY(_my_info_famely.birth_gfather_date)  >= " . date('d') . "
                                    AND DAY(_my_info_famely.birth_gfather_date)  <= " . date('d', $tim) . "
                                )
                            )
                                ";
                }
                if (date('n', $tim) > date('n')) {
                    $prev .= " 
                            JOIN _my_info_famely ON _my_info_famely.user_id = users.id
                            AND
                            (
                                (
                                    (
                                        MONTH(_user_privat_data.birth_date)  = " . date('n') . " 
                                        AND  DAY(_user_privat_data.birth_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_user_privat_data.birth_date)  = " . date('n', $tim) . " 
                                        AND DAY(_user_privat_data.birth_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.birth_girl_date) = " . date('n') . " 
                                        AND  DAY(_my_info_famely.birth_girl_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.birth_girl_date)  = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.birth_girl_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.child1b_date) = " . date('n') . " 
                                        AND  DAY(_my_info_famely.child1b_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.child1b_date)  = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.child1b_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.child2b_date) = " . date('n') . " 
                                        AND  DAY(_my_info_famely.child2b_date) >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.child2b_date)  = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.child2b_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.child3b_date) = " . date('n') . " 
                                        AND  DAY(_my_info_famely.child3b_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.child3b_date) = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.child3b_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.birth_mom_date)  = " . date('n') . " 
                                        AND  DAY(_my_info_famely.birth_mom_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.birth_mom_date)  = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.birth_mom_date) <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.birth_father_date)  = " . date('n') . " 
                                        AND  DAY(_my_info_famely.birth_father_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.birth_father_date)  = " . date('n', $tim) . " 
                                        AND DAY((_my_info_famely.birth_father_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.birth_sister_date)  = " . date('n') . " 
                                        AND  DAY(_my_info_famely.birth_sister_date) >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.birth_sister_date)  = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.birth_sister_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.birth_brother_date)  = " . date('n') . " 
                                        AND  DAY(_my_info_famely.birth_brother_date) >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.birth_brother_date) = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.birth_brother_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH(_my_info_famely.birth_gfather_date) = " . date('n') . " 
                                        AND  DAY(_my_info_famely.birth_gfather_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.birth_gfather_date) = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.birth_gfather_date)  <= " . date('d', $tim) . "
                                    )
                                )
                                OR
                                (
                                    (
                                        MONTH((_my_info_famely.birth_grandmom_date)  = " . date('n') . " 
                                        AND  DAY(_my_info_famely.birth_grandmom_date)  >= " . date('d') . "
                                    )    
                                        OR 
                                    (
                                        MONTH(_my_info_famely.birth_grandmom_date)  = " . date('n', $tim) . " 
                                        AND DAY(_my_info_famely.birth_grandmom_date)  <= " . date('d', $tim) . "
                                    )
                                )
                            )
                            ";
                }
            }
            if ($baner['birthday'] == 4) {
                $tim = mktime(0, 0, 0, date('n') + 1, date('d'), date('Y'));
                if (date('n', $tim) == date('n')) {
                    $prev .= " 
                                JOIN _my_info_famely ON _my_info_famely.user_id = users.id
                                AND
                                (
                                    (
                                        MONTH(_user_privat_data.birth_date)  = " . date('n') . "
                                        AND
                                        DAY(_user_privat_data.birth_date)  >= " . date('d') . "     
                                        AND
                                        DAY(_user_privat_data.birth_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.birth_girl_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.birth_girl_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.birth_girl_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.child1b_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.child1b_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.child1b_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.child2b_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.child2b_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.child2b_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.child3b_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.child3b_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.child3b_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.birth_mom_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.birth_mom_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.birth_mom_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.birth_father_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.birth_father_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.birth_father_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.birth_sister_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.birth_sister_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.birth_sister_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.birth_brother_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.birth_brother_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.birth_brother_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.birth_gfather_date)  = " . date('n') . "
                                        AND  DAY(_my_info_famely.birth_gfather_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.birth_gfather_date)  <= " . date('d', $tim) . " 
                                    )
                                    OR
                                    (
                                        MONTH(_my_info_famely.birth_grandmom_date) = " . date('n') . "
                                        AND  DAY(_my_info_famely.birth_grandmom_date)  >= " . date('d') . "     
                                        AND  DAY(_my_info_famely.birth_grandmom_date)  <= " . date('d', $tim) . " 
                                    )
                                )
                            ";
                }
                if (date('n', $tim) > date('n')) {
                    $prev .= " 
                                JOIN _my_info_famely ON _my_info_famely.user_id = users.id
                                AND
                                (
                                    (
                                        (
                                            MONTH(_user_privat_data.birth_date)  = " . date('n') . "
                                            AND
                                            DAY(_user_privat_data.birth_date)  >= " . date('d') . "
                                        )    
                                        OR 
                                        (
                                            MONTH(_user_privat_data.birth_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_user_privat_data.birth_date)  <= " . date('d', $tim) . "
                                        )
                                    ) 
                                    OR

                                    (
                                        (
                                            MONTH(_my_info_famely.birth_girl_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_girl_date)  >= " . date('d') . "
                                        )    
                                        OR 
                                        (
                                            MONTH(_my_info_famely.birth_girl_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_girl_date)  <= " . date('d', $tim) . "
                                        )
                                    ) 
                                    OR
                                    (
                                        (  MONTH(_my_info_famely.child1b_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.child1b_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.child1b_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.child1b_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (  MONTH(_my_info_famely.child2b_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.child2b_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.child2b_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.child2b_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (  MONTH(_my_info_famely.child3b_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.child3b_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.child3b_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.child3b_date)  <= " . date('d', $tim) . "
                                         )
                                       ) 
                                    OR
                                    (
                                        (
                                            MONTH(_my_info_famely.birth_mom_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_mom_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.birth_mom_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_mom_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (
                                            MONTH(_my_info_famely.birth_father_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_father_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.birth_father_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_father_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (
                                            MONTH(_my_info_famely.birth_sister_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_sister_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.birth_sister_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_sister_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (
                                            MONTH(_my_info_famely.birth_brother_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_brother_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.birth_brother_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_brother_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (
                                            MONTH(_my_info_famely.birth_brother_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_brother_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.birth_brother_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_brother_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (
                                            MONTH(_my_info_famely.birth_gfather_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_gfather_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.birth_gfather_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_gfather_date)  <= " . date('d', $tim) . "
                                         )
                                    ) 
                                    OR
                                    (
                                        (
                                            MONTH(_my_info_famely.birth_grandmom_date)  = " . date('n') . "
                                            AND
                                            DAY(_my_info_famely.birth_grandmom_date)  >= " . date('d') . "
                                          )    
                                          OR 
                                         (
                                            MONTH(_my_info_famely.birth_grandmom_date)  = " . date('n', $tim) . "
                                            AND
                                            DAY(_my_info_famely.birth_grandmom_date)  <= " . date('d', $tim) . "
                                         )
                                    )   
                                )
                                ";
                }
            }
        }
//если указан пол
        if ($baner['sex'] != 2)
            $prev .= "  AND _user_privat_data.sex = " . $baner['sex'] . " ";
// возраст   
        if ($baner['age_start'] != 6 || $baner['age_stop'] != 90)
            $prev .= " AND (" . time() . " - _user_privat_data.birth_time)/31536000 >= " . $baner['age_start'] . "  AND (" . time() . " - _user_privat_data.birth_time)/31536000 <= " . $baner['age_stop'] . "+1 ";
// страна 
        if ($baner['region'] != '0' && is_numeric($baner['region'])) {
            $prev.= "  AND (_user_privat_data.adress_area = '" . $baner['region'] . "' OR _user_privat_data.adress_city = '" . $baner['region'] . "')";
        }

//_my_info_famely
//женат
        if ($baner['marry'] != 0)
            $prev .= " JOIN _my_info_famely my ON my.user_id = users.id AND my.marry = " . $baner['marry'] . " ";

// users                        
//статус
        $and = '';
        if ($baner['to_whom'] == 1 && $baner['type'] == 2) {
            if ($user_id != '')
                $and = ' AND ';
            $online = " WHERE on_adv = 1 AND users.last_login + 180 > unix_timestamp(now()) " . $and . " " . $user_id;
        }elseif ($baner['to_whom'] == 2 && $baner['type'] == 2) {
            if ($user_id != '')
                $and = ' AND ';
            $online = " WHERE on_adv = 1 AND TO_DAYS(NOW()) - TO_DAYS(FROM_UNIXTIME(users.data_registr)) <=  users.logins " . $and . " " . $user_id;
        }else {
            if ($user_id == '')
                $online = 'WHERE on_adv = 1 ';
            else
                $online = ' WHERE on_adv = 1 AND ' . $user_id;
        }
//  ЗАПРОСС на подсчет количества   
        $sql = "SELECT 
                DISTINCT users.id,
                TO_DAYS(NOW()) - TO_DAYS(FROM_UNIXTIME(users.data_registr))/users.logins as coeff
                    FROM  users
                   $prev
                   $interes
                   $online
                    
                   ORDER BY coeff desc
                   $limit
                   ";
        if ($result == 'count')
            $count_aud = DB::query(Database::SELECT, $sql)->execute()->count();
        if ($result == 'as_array')
            $count_aud = DB::query(Database::SELECT, $sql)->execute()->as_array();
        return $count_aud;
    }
    
    /*
     * обрезка баннера
     */
     public static function _add_img($file, $name, $directory, $w, $h, $x, $y) {

      
        // сохраняем оригинал
//        $img = Image::factory($file);     
//        $img->save("$directory/$filename.$ext");
        // сохраняем оригинал вырезаного фото
        $img = Image::factory($file);
        $width = 300;
        $height = 300;
        if($img->height > $height || $img->width > $width){ 
            $ratio = $img->width / $img->height;// коефициент картинки
            // изменяем размер изобржаения и загружаем
            $original_ratio = $width / $height;// нужный коефициент картинки
            if($ratio < $original_ratio){
                $img->resize($width, $height, Image::AUTO);
            }else{
                $img->resize($width, $height, Image::AUTO);
            }
        }
            
        $img->crop($w, $h, $x, $y);
        $img->save("$directory/$name");
        
        // 200*150 
        $img->resize(200, 150, Image::NONE);
        $img->save("$directory/$name");
            
        return "$name";
    }
   
     public static function getName($length = NULL) {
        if($length == NULL)$length = 8;
        $symbols = '0123456789qwertHPTOEyuiopSDGEasdfghjklzxcvbnm';

        $string = '';
        for ($i = 0; $i < 10; $i++) {
                $key = rand(0, strlen($symbols)-1);
                $string .= $symbols[$key];
        }
        return $string;
    }
    
}

