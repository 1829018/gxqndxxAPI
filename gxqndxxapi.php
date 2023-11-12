<?php
// 检测是有没有user-agent请求头
function isBrowser($UA,$keywords){
    foreach($keywords as $keyword){
        if(strpos($UA,$keyword) !== false){
            return true;
        }
        return false;
    }
    
}
// 判断用户传来的是不是经过url编码过的
function isurlencode($str){
    $decode = rawurldecode($str);
    if ($decode !== $str){
        return $decode;
    }else{
        return $str;
    }
}
// get请求
if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    $user_agent = array('Chrome', 'Firefox', 'Safari', 'Edge', 'Opera');
    if (isBrowser($_SERVER['HTTP_USER_AGENT'],$user_agent)){
        echo '<div style="text-align: center"><h1>前轱辘不转 后轱辘转 思密达！！</h1><br><p>不接受get请求思密达！！</p></div>';
    }else{
        echo '单纯让你加个user-agent';
    }
    return false;
// post请求
}elseif ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (!empty($_POST['userName'] && !empty($_POST['userId']))){
        null;
    }elseif(empty($_POST['userName'])) {
        echo "userName Null";
        return false;
    }elseif(empty($_POST['userId'])){
        echo "userId Null";
        return false;
        
    }
   
}
define("URL",'http://qndxx.bestcood.com/mp/WeixinAuth/LoginByUser2.html');
$userName = isurlencode($_POST['userName']);
$userId = $_POST['userId'];
// 登录参数
$loginData = array(
    'userName' => $userName,
    'userId' => $userId
);
// 请求参数
$headers = array(
    'User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
    'Referer'=>'http://qndxx.bestcood.com/nanning/daxuexi',
    'Content-Type: application/json' 
);
//todo 登录
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,URL);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($loginData));
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);


$data = curl_exec($ch);
$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);
if ($code == 200 && json_decode($data)->code == 0){
    $msg0 = json_decode($data)->msg;
}else{
    echo json_decode($data)->msg;
    return false;
}
//todo 获取信息
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,'http://qndxx.bestcood.com/nanning/daxuexi');
curl_setopt($ch,CURLOPT_COOKIE,$data);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

$html_data = curl_exec($ch);
curl_close($ch);

$dom = new DOMDocument();
$dom -> loadHTML($html_data);
$srcipt_data = $dom ->getElementsByTagName('script');

foreach($srcipt_data as $srcipt){
    $text_data = $srcipt->textContent;

    if(strpos($text_data,'learnData') !== false){
        $pattern = '/var learnData = (.*?);/';
        preg_match($pattern,$text_data,$matches);
        if (isset($matches[1])){
            $learning_data = json_decode($matches[1],true);
        }
        break;
    }
}

$id = $learning_data['learnContent']['id'];
$title = $learning_data['learnContent']['title'];
$titleSub = $learning_data['learnContent']['titleSub'];

$successdata = array('id'=>$id);

//todo 青年大学习
$su = curl_init('http://qndxx.bestcood.com/mp/gx/DaXueXi/LearnHit.html');
curl_setopt_array(
    $su,[
        CURLOPT_COOKIE =>$html_data,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($successdata),
        CURLOPT_RETURNTRANSFER => true
    ]
    );
$_success = curl_exec($su);

$CO = curl_getinfo($su,CURLINFO_HTTP_CODE);
curl_close($su);

if ($CO == 200){
    header('Content-Type: application/json');
    $return_data = array(
        'code'=>1,
        'msg' =>urldecode($userName).$title.$titleSub."学习成功！！！",
        'version' => 1,
        'info' =>'API by 魔法师',
        'email' =>'2782226338@qq.com',
        'warn' =>'本接口不保留任何个人隐私，只是连接官方接口的一个桥梁；如泄露隐私，则与本人无关！！'
    );
    echo json_encode($return_data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}else{
    echo $_success;
} 
