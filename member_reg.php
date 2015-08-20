<?
include_once('inc/config.php');
include_once('includes/mysql_class.php');
include_once('libs/Smarty.class.php');
include_once('includes/sql_functions.php');
include_once('includes/upload_img_class.php');
include_once('includes/class.phpmailer.php');
include_once('languages/'.$default_lang.'/site_lang.php'); 
$smarty = new Smarty;
$smarty->assign('lang_arr',$_LANG);
#獲取網站基本信息
$get_web_basic_info=get_web_basic_info();
$smarty->assign('web_basic_info',get_web_basic_info());
#獲取佈局模組
$Modules=get_modules("left_modules",$layout_id=0);
$smarty->assign('modules',$Modules);

if($_GET["action"]=="send"){
	
	$code=$_POST['code'];
	$VerifyCode=$_SESSION['VerifyCode'];
	
	 if($VerifyCode!=$code or $VerifyCode=="")
	 {
		echo "<script>alert('".$_LANG['check_code_err']."');history.back();</script>";
		exit();
	 }
	 
	 /*echo "<pre>";
	 print_r($_POST);
	 echo "</pre>";
	 exit;*/
	/*
	$sql="select *from member_attribute where valid=1 order by order_num asc ";
	$rows = $db->query($sql);
	while ($record = $db->fetch_array($rows)){
		
	}
	
	foreach($data as $k=>$v){
	if($v["default_value"]!=""){
		$default_value_array[$v["id"]]=array_filter(explode("@@",$v["default_value"]));
		}
	}
	*/
	
	
	
	
	$data["user"]=post_check(trim($_POST["member_user"]));
	$data["passwd"]=post_check(trim($_POST["member_pswd"]));
	$sql="select *from member_attribute where valid=1 order by order_num asc ";
	$rows = $db->query($sql);
	while ($record = $db->fetch_array($rows)){
		$col="col_".$record["id"];
		switch($record["type"]){
			case "text":
			case "textarea":
			case "radio":
			case "option":
				$data[$col]=post_check(trim($_POST[$col]));
				break;
			case "checkbox":
				foreach($_POST[$col] as $key=>$value){
					$data[$col].=trim($value)."|";	
				}
				break;
			case "file":
				if($_FILES[$col]['tmp_name']!=""){
					$upload = new Upload();
					$upload_path="uploadfiles/images/";
					$upload->SetUploadDirectory($upload_path);
					$fileExt=array('gif', 'jpg', 'jpeg', 'png','doc','docx','xls','xlsx','pdf','ppt','pptx','txt','rar','zip');
					$upload->SetValidExtensions($fileExt);
					$upload->SetMaximumFileSize(15*1024*1024); 
					$upload->SetFileName($_FILES[$col]['name']);
					$upload->SetTempName($_FILES[$col]['tmp_name']);
					$file_path=$upload->UploadFile();
					$file_url="<a href='http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/uploadfiles/images/".$file_path."' target='_blank'>".$file_path."</a>";
					$file_url=str_replace("/plugin_form_self.php?action=send","",$file_url);
				}
				$data[$col]=$file_url;	
			break;
		}
	}
/*	for($i=1;$i<=30;$i++){
		$col="col_".$i;
		$data[$col]=post_check(trim($_POST[$col]));	
	}*/
	
	
	/*$data["col_1"]=post_check(trim($_POST["col_1"]));
	$data["col_2"]=post_check(trim($_POST["col_2"]));
	$data["col_3"]=post_check(trim($_POST["col_3"]));
	$data["col_4"]=post_check(trim($_POST["col_4"]));
	$data["col_5"]=post_check(trim($_POST["col_5"]));
	$data["col_6"]=post_check(trim($_POST["col_6"]));
	$data["col_7"]=post_check(trim($_POST["col_7"]));
	$data["col_8"]=post_check(trim($_POST["col_8"]));
	$data["col_9"]=post_check(trim($_POST["col_9"]));
	$data["col_10"]=post_check(trim($_POST["col_10"]));
	$data["col_11"]=post_check(trim($_POST["col_11"]));
	$data["col_12"]=post_check(trim($_POST["col_12"]));
	$data["col_13"]=post_check(trim($_POST["col_13"]));
	$data["col_14"]=post_check(trim($_POST["col_14"]));
	$data["col_15"]=post_check(trim($_POST["col_15"]));
	$data["col_16"]=post_check(trim($_POST["col_16"]));
	$data["col_17"]=post_check(trim($_POST["col_17"]));
	$data["col_18"]=post_check(trim($_POST["col_18"]));
	$data["col_19"]=post_check(trim($_POST["col_19"]));
	$data["col_20"]=post_check(trim($_POST["col_20"]));
	$data["col_21"]=post_check(trim($_POST["col_21"]));
	$data["col_22"]=post_check(trim($_POST["col_22"]));
	$data["col_23"]=post_check(trim($_POST["col_23"]));
	$data["col_24"]=post_check(trim($_POST["col_24"]));
	$data["col_25"]=post_check(trim($_POST["col_25"]));
	$data["col_26"]=post_check(trim($_POST["col_26"]));
	$data["col_27"]=post_check(trim($_POST["col_27"]));
	$data["col_28"]=post_check(trim($_POST["col_28"]));
	$data["col_29"]=post_check(trim($_POST["col_29"]));
	$data["col_30"]=post_check(trim($_POST["col_30"]));*/
	
	$db->query_insert("members",$data);
	echo "<script>alert('".$_LANG['member_reg_pass']."');window.history.go(-1);</script>";
	
}



$smarty->assign('top_menu',get_top_menu());#top單級選單
$smarty->assign('top_multiple_menu_module',get_multiple_menu());#top多級選單
$smarty->assign('marquee_js',get_marquee_modules());#獲取跑馬燈js
$smarty->assign('member_attribute',member_attribute());


function member_attribute(){
	global $db,$plugin_lang,$smarty,$lang_arr;
	$data=array();
	$sql="select *from member_attribute where valid=1 order by order_num asc ";
	$data = $db->fetch_all_array($sql);
	foreach($data as $k=>$v){
	if($v["default_value"]!=""){
		$default_value_array[$v["id"]]=array_filter(explode("@@",$v["default_value"]));
		}
	}
    $smarty->assign("default_value_array",$default_value_array);
/*	echo "<pre>";
	print_r($data);
	echo "</pre>";*/
	return $data;
}




$smarty->display('member_reg.html');
$db->close();
unset($get_web_basic_info,$Modules);
?>