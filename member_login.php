<?
include_once('inc/config.php');
include_once('includes/mysql_class.php');
include_once('libs/Smarty.class.php');
include_once('includes/sql_functions.php');
include_once('includes/upload_img_class.php');
include_once('includes/class.phpmailer.php');
include_once('languages/'.$default_lang.'/site_lang.php'); 
error_reporting(0);
session_start();
$smarty = new Smarty;
$smarty->assign('lang_arr',$_LANG);
#獲取網站基本信息
$get_web_basic_info=get_web_basic_info();
$smarty->assign('web_basic_info',get_web_basic_info());
//$smarty->assign("member_session",$_SESSION["member"]);	

//echo $_SESSION["member"]["id"];

//$member_id=$_SESSION["member"]["id"];
//get_member($member_id);

if($_GET["action"]=="send"){
	
	$code=$_POST['member_check_code']==""?$_POST['login_code']:$_POST['member_check_code'];
	
	$VerifyCode=$_SESSION['VerifyCode'];
	
	 if($VerifyCode!=$code or $VerifyCode=="")
	 {
		echo "<script>alert('".$_LANG['check_code_err']."');location.href='index.php';</script>";
		exit();
	 }
	
	if(trim($_POST["login_user"])=="" and trim($_POST["member_id"])==""){
		echo "<script >alert('".$_LANG['member_user'].$_LANG['post_null']."');location.href='index.php';</script>";
		exit();
	}
	
	if(trim($_POST["login_pswd"])==""  and trim($_POST["member_pswd"])==""){
		echo "<script >alert('".$_LANG['member_pswd'].$_LANG['post_null']."');location.href='index.php';</script>";
		exit();
	}
	
	$member_user=trim($_POST["login_user"])==""?trim($_POST["member_id"]):trim($_POST["login_user"]);
	$member_pswd=trim($_POST["login_pswd"])==""?trim($_POST["member_pswd"]):trim($_POST["login_pswd"]);
	
	$sql="select * from members where user='".post_check($member_user)."' and passwd='".post_check($member_pswd)."'";
	
	$rows = $db->query($sql);
	$result = $db->fetch_array($rows);
	if($result){
		$_SESSION["member"]=$result;
		//$smarty->assign("member_session",$_SESSION["member"]);
		if($result["member_sort"]==12){
	   	echo "<script>location.href='information.php?p_id=152';</script>";
		}elseif($result["member_sort"]==13){
		echo "<script>location.href='information.php?p_id=139';</script>";
		}
	}else{
		echo "<script>alert('".$_LANG['member_login_err']."');location.href='index.php';</script>";
		exit();
	}


	
}





function member_attribute(){
	global $db,$plugin_lang,$smarty,$lang_arr,$member;
	$data=array();
	$sql="select *from member_attribute where valid=1 order by order_num asc ";
	$data = $db->fetch_all_array($sql);
	foreach($data as $k=>$v){
		if($v["default_value"]!=""){
		$default_value=str_replace("\r\n","",$v["default_value"]);
		$default_value_array[$v["id"]]=array_filter(explode("@@",$default_value));
		}
		
		$col="col_".$v["id"];
		$member_col_val=($member[$col]);
		if($v["type"]=="checkbox"){
			$member_col_val=array_filter(explode("|",$member_col_val));
		}
		$member_attri[$v["id"]]=$member_col_val;
		
	}
    $smarty->assign("default_value_array",$default_value_array);
	$smarty->assign("member_attri",$member_attri);
/*	echo "<pre>";
	print_r($member_attri);
	echo "</pre>";*/
	return $data;
}







if($_GET["action"]=="modify"){
	$code=$_POST['code'];
	$VerifyCode=$_SESSION['VerifyCode'];
	
	 if($VerifyCode!=$code or $VerifyCode=="")
	 {
		echo "<script>alert('".$_LANG['check_code_err']."');history.back();</script>";
		exit();
	 }
	 

	
	
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
					$data[$col]=$file_url;	
				}
			break;
		}
	}

	$id=$_SESSION["member"]["id"];
	$where="id='$id' limit 1";
	
	if($db->query_update("members", $data, $where)){
		$sql="select * from member where id=".$id;
		$rows = $db->query($sql);
		$result = $db->fetch_array($rows);
		if($result){
			$_SESSION["member"]=$result;
		}
		echo "<script>alert('".$_LANG['modify_success']."');window.location.href='member_login.php';</script>";   
	}else{
		echo "<script>alert(''".$_LANG['modify_error']."');window.location.href='member_login.php';</script>";  
	}
	
	
	

}





if($_GET["action"]=="login_out"){
	unset($_SESSION["member"]);
	echo "<script>location.href='index.php';</script>";
}



if($_SESSION["member_session"]!=""){
	$smarty->assign("member_session",$_SESSION["member"]);
}

function get_member($member_id){
	global $db,$plugin_lang,$smarty,$lang_arr;
	if($member_id>0){
		$sql="select * from members where id=".$member_id;
		$data = $db->query_first($sql);
	}
	return $data; 
}


#獲取佈局模組
$Modules=get_modules("left_modules",$layout_id=0);
$smarty->assign('modules',$Modules);

$smarty->assign('top_menu',get_top_menu());#top單級選單
$smarty->assign('top_multiple_menu_module',get_multiple_menu());#top多級選單
$smarty->assign('marquee_js',get_marquee_modules());#獲取跑馬燈js
$member=get_member($_SESSION["member"]["id"]);
$smarty->assign("member_session",$member);	
$smarty->assign('member_attribute',member_attribute());
$smarty->display('member_login.html');

$db->close();
unset($get_web_basic_info,$Modules);
?>