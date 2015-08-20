<?
include_once('inc/config.php');
include_once('includes/mysql_class.php');
include_once('libs/Smarty.class.php');
include_once('includes/page.class.php');
include_once('includes/sql_functions.php');
include_once('languages/'.$default_lang.'/site_lang.php'); 
$smarty = new Smarty;
$lang_arr=$_LANG;
$smarty->assign('lang_arr',$lang_arr);
check_member_limit($module_or_menu="module",intval($_GET["p_id"]));#檢查限制會員訪問 module 為模組資訊， menu為選單資訊
#獲取網站基本信息
$get_web_basic_info=get_web_basic_info();
$smarty->assign('web_basic_info',get_web_basic_info());

#判別是否顯示top選單
/*if($get_web_basic_info['top_menu_valid']==true){
	if($Modules['top_modules']==0){
		$smarty->assign('top_menu',get_top_menu());#top單級選單
	}else{
		$smarty->assign('top_multiple_menu_module',get_multiple_menu());#top多級選單
	}
}*/
$smarty->assign('top_menu',get_top_menu());#top單級選單
$smarty->assign('top_multiple_menu_module',get_multiple_menu());#top多級選單
$smarty->assign('marquee_js',get_marquee_modules());#獲取跑馬燈js
#獲取單筆詳細資訊
$info_p_id=intval($_GET["p_id"]);
if(!empty($info_p_id)){
	
	$information_list=get_information_list($info_p_id);
	#獲取佈局模組
	$Modules=get_modules("all",intval($information_list["layout_id"]));
	$smarty->assign('modules',$Modules);
	$smarty->assign('information_list',$information_list);
	
	$smarty->assign('information_layer',get_information_layer($info_p_id));
}

$smarty->display('more_infor.html');
$db->close();
unset($get_web_basic_info,$Modules,$lang_arr);
?>