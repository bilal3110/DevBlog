<?php
namespace App\Helpers;

use App\Models\Notifications;

class Notify{
    public static function send($user_id,$type,$data){
        Notifications::create([
            'user_id' => $user_id,
            'type' => $type,
            'data' => $data,
        ]);
    }
}

?>

