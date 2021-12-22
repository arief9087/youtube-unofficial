<?php
error_reporting(0);
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type, *");
header('Content-Type: application/json; charset=utf-8');
function scramble($a) {
    $a = str_split($a);
    scr_splice($a, 1);
    scr_swap($a, 21);
    scr_reverse($a, 24);
    scr_swap($a, 34);
    scr_swap($a, 18);
    scr_swap($a, 63);
    return implode('', $a);
}



function bz($a) {
    $a = str_split($a);
    $a = cz($a, 61);
    $a = cz($a, 5);
    scr_reverse($a);
    array_slice($a,2);
    $a = cz($a, 69);
    $a = a.slice(2);
    scr_reverse($a);
    return implode('', $a);
}

function cz($a, $b) {
    $c = $a[0];
    $a[0] = $a[$b % count($a)];
    $a[$b % count($a)] = $c;
    return $a;
};



function scr_reverse(&$a) {
    $a = array_reverse($a);
}
function scr_splice(&$a, $b) {
    array_splice($a, 0, $b);
}
function scr_swap(&$a, $b) {
    $c = $a[0];
    $a[0] = $a[$b % count($a)];
    $a[$b % count($a)] = $c;
}
function decipherSignature($signature, $operators)
	{
		$op = explode(';', $operators);
		$s = $signature;
		
		foreach($op as $o)
		{
			$temp = explode('=', $o);
			if($temp[0] === "reverse")
				$s = strrev($s);
			else if($temp[0] === "splice")
				$s = substr($s, $temp[1]);
			else if($temp[0] === "swap")
			{
				$c = $s[0];
				$s[0] = $s[$temp[1] % strlen($s)];
				$s[$temp[1]] = $c;
			}
			else if($temp[0] === "ret")
				return $s;
		}
	}
function request($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $statustext = curl_getinfo($ch);
    return $statuscode;
}



$_GET['id'] = 'FMqYhnN0Qjk';  //example 403 with failed signaturechip
$id = $_GET['id'];
//$id = 'apcTjWv4d08';
if(!$id){
    $response = array('status'=>'error','message'=>'ID Tidak Ditemukan');
    echo json_encode($response);
}else{
$url = "https://www.youtube.com/watch?v=$id";
$thumbnail = "https://i.ytimg.com/vi/$id/maxresdefault.jpg";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.youtube.com/watch?v=$id");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true); 
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
$result = curl_exec($ch);
if(!$result){
    $response = array('status'=>'error','message'=>'Tidak dapat terhubung dengan server');
    echo json_encode($response);
}else{
$m1 = explode('var ytInitialPlayerResponse = ',$result);
$m2 = explode(';<',$m1[1]);
$s1 = explode('<meta name="title" content="',$result);
$s2 = explode('">',$s1[1]);
if(!$s2[0]){
    $response = array('status'=>'error','message'=>'Data response tidak valid');
    echo json_encode($response);
}else{
$q1 = explode('<link itemprop="name" content="',$result);
$q2 = explode('">',$q1[1]);
$output = $m2[0];
$baca = json_decode($output,true);
$durasi  = $baca['videoDetails']['lengthSeconds']; 
$file_data = array();
$file_data['status'] = "success";
$file_data['message'] = "Data ditemukan";
$file_data['song_title'] = "$s2[0]";
$file_data['song_channel'] = "$q2[0]";
$file_data['song_duration'] = "$durasi";
$file_data['song_thumb'] = "$thumbnail";
$file_data["files"] = array();
$o = 1;
$satu  = $baca['streamingData']['adaptiveFormats'];
foreach($satu as $data){
    $m3 = explode(';',$data['mimeType']);
    $file_data2['mimeType'] = $m3[0];
    $file_data2['quality'] = $data['quality'];
    if($data['url']){
        $file_data2['outputType'] = '1';
        $file_data2['url'] = $data['url'];
        $file_data2['cek_akses'] = request("$data[urls]");
    }else
    if($data['signatureCipher']) {
		$file_data2['outputType'] = '2';
        $gongon = $data['signatureCipher'];
        parse_str($data['signatureCipher'], $cipher);
		$downloadURL = $cipher['url']."&".$cipher["sp"]."=".scramble($cipher["s"]);
		//$downloadURL = $cipher['url']."&".$cipher["sp"]."=".bz($cipher["s"]);
		//$kon = urlencode($cipher["s"]);
		//$downloadURL = $cipher['url']."&lsig=$kon";
        $file_data2['url'] = $downloadURL;
        //$file_data2['signatureCipher'] = $gongon;
        $file_data2['cek_akses'] = request("$downloadURL");
    }else{
		$file_data2['url'] = 'unknown';
		$file_data2['cek_akses'] = 'unkown';
	}
	
	
    array_push($file_data["files"], $file_data2);
    $o++;
}
echo  json_encode($file_data);    
}
}
}
?>
