<?
 if($_REQUEST['act']=="excel")
{
	header('Content-Type: application/vnd.ms-excel;');
	header ("Content-Disposition: attachment; filename=excel.xls" );
}
 
	 if($_REQUEST['action']=="excel")
{
	header('Content-Type: application/vnd.ms-excel;');
	header ("Content-Disposition: attachment; filename=excel.xls" );
}
		 
if($_REQUEST['type']=="detail")
{
	header('Content-Type: application/msword;');
	header ("Content-Disposition: attachment; filename=word.doc" );

}
include_once('../../inc/config.php');
include_once('../../includes/mysql_class.php');
include_once('../../libs/Smarty.class.php');
include_once('../../includes/sql_functions.php');
include_once('../../includes/upload_img_class.php');
include_once('../../includes/class.phpmailer.php');
include_once('../../includes/uploadpic.class.php');
include_once('../../languages/'.$default_lang.'/site_lang.php'); 
//check_limit();#檢查限制會員訪問

if(intval(($_GET["action"]=='send') )){
	/*$code=$_POST['check_code'];
	$VerifyCode=$_SESSION['VerifyCode'];
	if($VerifyCode!=$code or $VerifyCode=="")
	 {
		echo "<script>alert('驗證碼錯誤');history.back();</script>";
		exit();
	 }*/
	
	/*if($_SESSION["member"]["user"]==""){
		echo "<script>alert('請登入才能回覆！');history.back();</script>";
		exit();	
	}*/
	blog_news_add_update();	
}


$smarty = new Smarty;
$smarty->compile_dir ='../templates_c';
$smarty->assign('lang_arr',$_LANG);
#獲取網站基本信息
$get_web_basic_info=get_web_basic_info();
$smarty->assign('web_basic_info',get_web_basic_info());
#獲取佈局模組
//check_limit();#檢驗訪問權限
$Modules=get_modules("left_modules",$layout_id=0);
$smarty->assign('modules',$Modules);
if($_REQUEST['type']=='detail'){
	$smarty->assign('list_data',show_data_all());
}else{
	$smarty->assign('list_data',show_data());
}
$smarty->assign('list_data2',show_school_name());
$smarty->assign('top_menu',get_top_menu());#top單級選單
$smarty->assign('top_multiple_menu_module',get_multiple_menu());#top多級選單
$smarty->assign('marquee_js',get_marquee_modules());#獲取跑馬燈js



//$smarty->assign('nav',get_multiple_menu_nav($info_p_id));
function show_school_name(){
	global $db,$smarty;
	
	$sql="select *from new_ziping_table";
	
	$data = $db->fetch_all_array($sql);
	
	return $data;

	
}
function show_data(){
	global $db,$smarty,$dbhost,$dbuser,$dbpw,$dbname;
	//$author=get_blog_author();
	if($_REQUEST['action']=="no")
	 {
		 $sql="select members.* from members left join `new_ziping_table` on members.id=new_ziping_table.school_id where new_ziping_table.school_id is null ";
		 if($_REQUEST['school_name2']!="" && $_REQUEST['school_name2']!="所有類別")
		{
			$smarty->assign('school_name2',$_REQUEST['school_name2']);
			$sql.=" and members.member_sort='".$_REQUEST['school_name2']."'";
		}
		 //$sql="select distinct members.* from members,new_ziping_table where members.id!=new_ziping_table.school_id";
		 //$sql="select * from members left join `new_ziping_table` on members.id!=new_ziping_table.school_id";
		 $data = $db->fetch_all_array($sql);
		 return $data;
	 }else{
		 //echo $dbhost.aa;
		 $con = mysql_connect($dbhost,$dbuser,$dbpw);
		 mysql_select_db($dbname,$con);
		 mysql_query("set names 'utf8'");
		$sql="select *from new_ziping_table";
		if($_REQUEST['school_name']!="" && $_REQUEST['school_name']!="所有類別")
		{
			$smarty->assign('school_name',$_REQUEST['school_name']);
			$sql.=" where member_sort='".$_REQUEST['school_name']."'";
		}
		$data2=array();
		$res=mysql_query($sql);
		
		while($row=mysql_fetch_array($res))
		{
			$str=0;
			$str1=0;
			//echo $row['col_28'];
			$strs1=split("#",$row['col_28']);
			for($i=0;$i<count($strs1);$i++)
			{
				$str1=$str1+(int)$strs1[$i];
			}
			$str2=0;
			$strs2=split("#",$row['col_34']);
			for($i=0;$i<count($strs2);$i++)
			{
				$str2=$str2+(int)$strs2[$i];
			}
			$str=$str1+$str2;
			$col_29 = prev(explode('#',$row['col_29']))!=''?'有':'沒有';
			$data2[]=array('id'=>$row['id'],
							'col_1'=>$row['col_1'],
							'col_6'=>$row['col_6'],
							'col_7'=>$row['col_7'],
							'col_8'=>$row['col_8'],
							'col_9'=>$row['col_9'],
							'col_10'=>$row['col_10'],
							'col_11'=>$row['col_11'],
							'col_12'=>$row['col_12'],
							'col_13'=>$row['col_13'],
							'col_14'=>$row['col_14'],
							'col_15'=>$row['col_15'],
							'col_16'=>$row['col_16'],
							'col_18'=>$row['col_18'],
							'col_19'=>$row['col_19'],
							'col_20'=>$row['col_20'],
							'col_21'=>$row['col_21'],
							'col_22'=>$row['col_22'],
							'col_23'=>$row['col_23'],
							'col_48'=>$row['col_48'],
							'col_28'=>$str,
							'col_29'=>$col_29
							);
			//$data2[]=$row;
				//echo $str.qq;
		}
		//$data = $db->fetch_all_array($sql);
		return $data2;
	 }
		//echo $sql.aa;
	
	 
	
	
}



#新增,修改模組function
function blog_news_add_update(){
	global $db,$lang_arr;
	
	for($i=1;$i<24;$i++)
	{
		
		$data["col_".$i]=$_POST["col_".$i];
	}
	for($i=36;$i<47;$i++)
	{
		if($i==41)
		{
			$colstr="";
			$pics = count($_FILES['col_41']['name']);
			for($j=0;$j<$pics;$j++)
			{
			if($_FILES['col_41']['name'][$j]!=""){
				$up=new uploadPic();
				$up->upfile("uploadfiles/images/","col_41",$j);
				$colstr.=$up->picname."#";
				}
			}
			if($colstr!="")
			{
			$data["col_".$i]=$colstr;
			}
			}else if($i==40){
				$colstr="";
				$pics = count($_FILES['col_40']['name']);
			for($j=0;$j<$pics;$j++)
			{
				if($_FILES['col_40']['name'][$j]!=""){
				$up=new uploadPic();
				$up->upfile("uploadfiles/images/","col_40",$j);
				$colstr.=$up->picname."#";
				
				}
				
			}
			if($colstr!="")
			{
			$data["col_".$i]=$colstr;
			}
				}
			else{
		$data["col_".$i]=$_POST["col_".$i];
		}
	}
	for($i=24;$i<36;$i++)
	{
		$colstr="";
		if($i==29)
		{
			$pics = count($_FILES['col_29']['name']);
			for($j=0;$j<$pics;$j++)
			{
				if($_FILES['col_29']['name'][$j]!=""){
				$up=new uploadPic();
				$up->upfile("uploadfiles/file/","col_29",$j);
				$colstr.=$up->picname."#";
				}
				
			}
			//$colstr="29";
			/*foreach($_POST["col_".$i] as $key=>$value){
				/*echo 'qq';
				$up=new uploadPic();
				$up->upfile("uploadfiles\file","col_29");
				$colstr.=$up->picname."#";
				$colstr="29";
			}*/
			if($colstr!="")
			{
			$data["col_".$i]=$colstr;
			}
			}else if($i==35)
		{
			$pics = count($_FILES['col_35']['name']);
			for($j=0;$j<$pics;$j++)
			{
				if($_FILES['col_35']['name'][$j]!=""){
				$up=new uploadPic();
				$up->upfile("uploadfiles/images/","col_35",$j);
				$colstr.=$up->picname."#";
				}
				
			}
			if($colstr!="")
			{
			$data["col_".$i]=$colstr;
			}
			}else
		{
		
			foreach($_POST["col_".$i] as $key=>$value){
				$colstr.=$value."#";
			}
			$data['col_'.$i]=$colstr;
		}
		
	}
	
   
	/*exit;
	
	$upload = new Upload();
	$upload_path="uploadfiles/blog/";
	$upload->SetUploadDirectory($upload_path);
	$upload->SetValidExtensions(array('doc','docx','xls','xlsx','pdf','aif','aifc','aiff','asf','avi','rm','wma','wmv','swf'));
	$upload->SetMaximumFileSize(20*1024*1024); 
	if($_FILES['upfile']['tmp_name']){
		
		$upload->SetFileName($_FILES['upfile']['name']);
		$upload->SetTempName($_FILES['upfile']['tmp_name']);
		$data['file_path']=$upload->UploadFile();
	}
	

	if($_FILES['upfile_video']['tmp_name']){
		
		$upload->SetFileName($_FILES['upfile_video']['name']);
		$upload->SetTempName($_FILES['upfile_video']['tmp_name']);
		$data['file_path_video']=$upload->UploadFile();
	}
	*/
	if($_GET["action"]=="send"){
		//$data["sort"]=$_GET["id"];
		/*if($_SESSION["member"]["member_code"]==""){
			$data['creator']=$_SESSION["member"]["col_1"]; #col_1 為自定義欄位會員名稱
		}else{
			$data['creator']=$_SESSION["member"]["col_1"];	
		}*/
		//$data['creator']=$_SESSION["member"]["col_1"];
		if($_REQUEST['id']=="")	{
		$db->query_insert("new_ziping_table",$data);
		echo "<script>alert('新增成功');location='zipingbiao.php';</script>";
		}else{
	
	
		$where="id=".intval($_REQUEST["id"])." limit 1";
		$db->query_update("new_ziping_table", $data, $where) ;
		echo "<script>alert('修改成功');history.go(-1);</script>";  
		}
	}
		unset($data);  
}



/*function check_limit(){
	global $db;
	$data = get_blog_author();
	$limit_member_str=$data['viewer'];
	$member_sort="-|".$_SESSION['member']['member_sort']."-|";
	if($_SESSION['member']==""){
		echo "<script >alert('沒有權限訪問該內容');history.back();</script>";
		exit();	
	}
	$pass=strstr($limit_member_str,$member_sort); 
	if($pass==false){
		echo "<script >alert('沒有權限訪問該內容');history.back();</script>";
		exit();	
	}

	
}*/

function escape($string) { 
    if(get_magic_quotes_gpc()) $string = stripslashes($string); 
    return $string; 
}

function get_blog_author(){
	global $db;
	$sql="select *from blog_author where id=1 ";
	$data = $db->query_first($sql);	
	return $data;
}
function show_data_all(){
	global $db,$smarty;
	$sql = "select * from new_ziping_table";
	if($_REQUEST['school_name']!="" && $_REQUEST['school_name']!="所有類別")
	{
		$smarty->assign('school_name',$_REQUEST['school_name']);
		$sql.=" where member_sort='".$_REQUEST['school_name']."'";
	}
	$res = $db->query($sql);
	$data = $db->fetch_all_array($sql);
	for($di=0;$di<count($data);$di++){
		$data[$di]['data2'] = show_data2($data[$di]['school_id']);
		$col_24=array();
		$count_24=0;
		$col_25=array();
		$col_26=array();
		$col_27=array();
		$col_28=array();
		$col_29=array();
		$col_30=array();
		$count_30=0;
		$col_31=array();
		$col_32=array();
		$col_33=array();
		$col_34=array();
		$col_35s=array();
		$col_35str="";
		//print_r($col_35s);
		$col_35str2="";
		$col_24=split("#",$data[$di]['col_24']);
		$count_24=count($col_24);
		$col_25=split("#",$data[$di]['col_25']);
		$col_26=split("#",$data[$di]['col_26']);
		$col_27=split("#",$data[$di]['col_27']);
		$col_28=split("#",$data[$di]['col_28']);
		$col_29=split("#",$data[$di]['col_29']);
		$col_30=split("#",$data[$di]['col_30']);
		$count_30=count($col_30);
		$col_31=split("#",$data[$di]['col_31']);
		$col_32=split("#",$data[$di]['col_32']);
		$col_33=split("#",$data[$di]['col_33']);
		$col_34=split("#",$data[$di]['col_34']);
		$col_35s=split("@",$data[$di]['col_35']);
		$col_35str="";
		//print_r($col_35s);
		$col_35str2="";
		$col_35str_arr = array();
		$i=0;
		foreach($col_35s as $c35){
			$c35 = substr($c35,strlen($c35)-1,1)=='#'?substr($c35,0,strlen($c35)-1):$c35;
			$c35 = $c35!=''?explode('#',$c35):array();
			$temp_str = '';
			foreach($c35 as $img){
				
				$jpgdata=GetImageSize("../../uploadfiles/images/".$img);
				$newjpgdata = get_width_height(213,213,$jpgdata[0],$jpgdata[1]);
				$temp_str.="<img id='col_l_35_$i' src='http://ee.tp.edu.tw/uploadfiles/images/".$img."' width='".$newjpgdata['width']."' height='".$newjpgdata['height']."'  />";
				$temp_str.="<br />";
				$i++;
			}
			$col_35str_arr[] = $temp_str;
			
		}
		$col_40 = array();
		$col_40 = split("#",$data[$di]['col_40']);
		$str="";
		$c40_pd = count($col_40);
		for($i=1;$i<$c40_pd;$i++){
				$jpgdata=GetImageSize("../../uploadfiles/images/".$col_40[$i-1]);
				$newjpgdata = get_width_height(213,213,$jpgdata[0],$jpgdata[1]);
				
				$str.="<img id='col_l_40_$i' src='http://ee.tp.edu.tw/uploadfiles/images/".$col_40[$i-1]."' width='".$newjpgdata['width']."' height='".$newjpgdata['height']."'  />";
				$str.="<br />";
		}
		$data[$di]['str'] = $str;
	
		$colstr="";
		for($i=0;$i<count($col_24)-1;$i++){
			$colstr.="
			<tr id='row".$i."' name='row".$i."'>
			<td>
			<table width=\"100%\" border=\"1\" bgcolor='#FFFFFF'>
			<tr>
			<td>活 動 ". $i ." &nbsp;：".$col_24[$i]."</td>
			<td>&nbsp;</td>
			</tr>
			<tr>
			<td> 辦理時間：".$col_25[$i]."</td>
			<td>活動地點： ".$col_26[$i]."</td>
			</tr>
			<tr>
			<td> 辦理方式： ".$col_27[$i]."</td>
			<td>參加人次： ".$col_28[$i]."</td>
			</tr>
			<tr>
			<td colspan=\"3\">
			<a id='col_l_29_$i' href='http://ee.tp.edu.tw/uploadfiles/file/".$col_29[$i]."' >".$col_29[$i]."</a>
			</td>
			</tr>
			</table>
			</td>
			</tr>";
		}
		$colstr2="";
		for($i=0;$i<count($col_30)-1;$i++){
			$colstr2.="
			<tr id='row" .$i. "' name='row" .$i. "'>
			<td>
			<table width=\"100%\" border=\"1\">
			<tr>
			<td>活 動 ". $i ." &nbsp;： ".$col_30[$i]."</td>
			<td>&nbsp;</td>
			</tr>
			<tr>
			<td> 辦理時間： ".$col_31[$i]." </td>
			<td>活動地點： ".$col_32[$i]." </td>
			</tr>
			<tr>
			<td> 辦理方式： ".$col_33[$i]."</td>
			<td>參加人次： ".$col_34[$i]." </td>
			</tr>
			<tr>
			<td colspan=\"3\">".$col_35str_arr[$i]."</td>
			</tr>
			</table>
			</td>
			</tr>";
		}
		if($data[$di]['col_41']!=''){
			$jpgdata=GetImageSize("../../uploadfiles/images/".$data[$di]['col_41']);
			$newjpgdata = get_width_height(213,213,$jpgdata[0],$jpgdata[1]);
			$data[$di]['col_41'] = $data[$di]['col_41']."'  width='".$newjpgdata['width']."' height='".$newjpgdata['height']."";
		}
		
		
		$data[$di]['count_24']=$count_24;
		$data[$di]['col_24']=$colstr;
		$data[$di]['count_30']=$count_30;
		$data[$di]['col_30']=$colstr2;
	}
	//print_r($data);
	return $data;
}
function show_data2($sid){
	global $db,$smarty;
	$sql2="select *from members where id=".$sid;
	$data2 = $db->fetch_all_array($sql2);
	return $data2[0];
}
function get_width_height($limit_width,$limit_height,$source_width,$source_height){
$wh=array();
if($source_width<=$limit_width && $source_height<=$limit_height){
$wh["width"]=$source_width;
$wh["height"]=$source_height;
}
else{
$w=$source_width/$limit_width;
$h=$source_height/$limit_height;
if($w>$h){
$wh["width"]=$limit_width;
$wh["height"]=($w>=1?($source_height/$w):($source_height*$w));
}
elseif($w<$h){
$wh["width"]=($h>=1?($source_width/$h):($source_width*$h));
$wh["height"]=$limit_height;
}
else{
$wh["width"]=$limit_width;
$wh["height"]=$limit_height;
}
}
return $wh;
}
if($_REQUEST['action']=="no")
	 {
		 if($_REQUEST['act']=="excel")
		{
			$smarty->assign("excel","NO");
		}
		 $smarty->display('new_admin_zipingbiaono.html');
		 }else{
		if($_REQUEST['action']=="excel")
		{

			$smarty->assign("excel","YES");
		}
		
		if($_REQUEST['type']=='detail'){
			$smarty->display('new_admin_zipingbiao_detail.html');
		}else{
			$smarty->display('new_admin_zipingbiao.html');
		}
}

$db->close();
unset($get_web_basic_info,$Modules);
?>