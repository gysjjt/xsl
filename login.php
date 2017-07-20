<?php
/**************************************
* Project Name:盛传移动商务平台
* Time:2016-03-22
* Author:MarkingChanning QQ:380992882
**************************************/
session_start();
set_time_limit(0);
header("Content-Type: text/html;charset=utf-8");
include_once("curlapi.class.php");
$curl = new curlapi();
if($_GET['action'] == "code"){//获取验证码
	$curl -> url = "http://vip.minicon.net/validatecode.aspx";
	echo $curl -> get_code();
}else if($_GET['action'] == "login"){
	$login = urlencode($_POST['login']);
	$passwd = $_POST['passwd'];
	$rand = $_POST['rand'];
	$params = "name=$login&pwd=$passwd";
	$curl -> url = "http://mis.xingshalong.com/Login/Login";
	$curl -> params = $params;
	$result = $curl -> login();
	$result = json_decode($result,true);
	if(isset($result['Success']) && $result['Success'] == 1){
		echo 1;
	}else {
		echo "账号密码或者验证码错误";
	}
}else if($_GET['action'] == 'curlmember'){
	$shopname = $_REQUEST['shopname'];
	$data = '';

    //获取总数
	$shopid = ceil($_SESSION['shopid']);
	$url = "http://mis.xingshalong.com/member/member/GetPage?pageindex=1&pagesize=1&CardCategoryID=-1&TrueName=&Sex=-1&Mobile=&SourceType=-1&Level=-1&ShopID=$shopid&LastConsumeTime=-1&ConsumeSum=&HaveCard=&StoredCardBalance=&ConsumeTimes=&DebtAmount=&Birthday=&IsDelete=&IsLost=&deletetitle=";
    $curl -> url = $url;
    $rs = $curl -> curl();
	$rs = json_decode($rs,true);
	$totals = $rs['Message'];
	//$totals = 10;

	$url = "http://mis.xingshalong.com/member/member/GetPage?pageindex=1&pagesize=$totals&CardCategoryID=-1&TrueName=&Sex=-1&Mobile=&SourceType=-1&Level=-1&ShopID=$shopid&LastConsumeTime=-1&ConsumeSum=&HaveCard=&StoredCardBalance=&ConsumeTimes=&DebtAmount=&Birthday=&IsDelete=&IsLost=&deletetitle=";
	$curl -> url = $url;
	$data = $curl -> curl();
	$data = json_decode($data,true);

//    //总页数
//    $pages = ceil($totals/500);
//	for($i=1; $i<=$pages; $i++){
//		$url = "http://mis.xingshalong.com/member/member/GetPage?pageindex=1&pagesize=$totals&CardCategoryID=-1&TrueName=&Sex=-1&Mobile=&SourceType=-1&Level=-1&ShopID=$shopid&LastConsumeTime=-1&ConsumeSum=&HaveCard=&StoredCardBalance=&ConsumeTimes=&DebtAmount=&Birthday=&IsDelete=&IsLost=&deletetitle=";
//		$curl -> url = $url;
//		$pagesData = $curl -> curl();
//		$pagesData = json_decode($pagesData,true);
//		$data .= $curl ->getMembersInfo($pagesData, $i);
//		sleep(10);
//	};

    if($data == '') {
        header('Location: index.php');
    }

	$curl -> downMembersCvs($data, $shopname);
}else if($_GET['action'] == 'curlpackage'){
    $shopname = $_REQUEST['shopname'];
    $data = '';

    //获取总数
    $curl -> url = "http://vip8.sentree.com.cn/shair/timesItem!initTreat.action?set=cash";
    $rs = $curl -> curl();
    preg_match('/共(.*)条/isU', $rs, $totals);
    $totals = isset($totals[1])?$totals[1]:100;

	//总页数
    $pages = ceil($totals/100);
    for($i=1; $i<=$pages; $i++){
        $params = "page.currNum=$i&page.rpp=100&set=cash&r=0.3421386775783387";
        $curl -> params = $params;
        $curl -> url = "http://vip8.sentree.com.cn/shair/timesItem!initTreat.action";
        $pagesData = $curl -> getPackagePage();
        $data .= $curl ->getPackageInfo($pagesData, $i);
    };
    if($data == '') {
        header('Location: index.php');
    }
    $curl -> downPackageCvs($data, $shopname);
}else if($_GET['action'] == 'curlstaff'){
	$shopname = $_REQUEST['shopname'];
	$data = '';

	//获取员工数据
	$curl -> url = "http://vip8.sentree.com.cn/shair/employee!employeeInfo.action?set=manage&r=0.5704847458180489";
	$rs = $curl -> curl();

	$rsBlank = preg_replace("/\s\n\t/","",$rs);
	//$rsBlank = str_replace(' ', '', $rsBlank);
	preg_match_all("/table_fixed_head.*>(.*)<\/form>/isU", $rsBlank ,$tables);

    if(count($tables[0]) == 0) {
        header('Location: index.php');
    }
	$curl -> downStaffCvs($tables[1][0], $shopname);
}
?>