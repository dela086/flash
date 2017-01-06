<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/29
 * Time: 下午10:27
 */
namespace app\ctrl;
use core\flash;

class indexCtrl extends flash  {

	/*public function index() {
		$temp = \core\lib\conf::get('CTRL', 'route');

		new \core\lib\model();
		echo phpversion() > '8.7.11' ;
		phpinfo();

	}*/

	public function index(){
		//获得参数 signature nonce token timestamp echostr
		$nonce = $_GET['nonce'];
		$token = 'wx01c7e071150216b7';
		$timestamp = $_GET['timestamp'];
		$echostr = $_GET['echostr'];
		$signature = $_GET['signature'];

		//形成数组，然后按字典序排序
		$array = array($nonce, $timestamp, $token);
		sort($array);
		//拼接成字符串,sha1加密 ，然后与signature进行校验
		$str = sha1(implode($array));
		if ($str == $signature && $echostr) {
			//第一次接入weixin api接口的时候
			echo $echostr;
			exit;
		} else {
			$this->responseMsg();
			$this->defineItem();
		}
	}

	public function test() {

//		$tbkM = M('tbk');
//		$c = $tbkM -> get_TopClient();
//		$c->appkey = '23536810';
//		$c->secretKey = '634c0d55e61e771e6de61c99d1a1cd25';

	//	$req = $tbkM->get_tbkItem();

		//->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick");
		//->setQ("大衣");
		//$req->setCat("16,18");
		//$resp = $c->execute($req);

//		console('TBK ===1 '. print_r($resp,true), 'tbk');
//		p( $resp);

		//$response = curlAPI('http://e22a.com/h.1WbdQS?cv=AAdluWjO&sm=c60ec8','get','xml');
		$response = curlAPI('http://e22a.com/h.1WbdQS?cv=AAdluWjO&sm=c60ec8','get','xml');

		/*$urlStart = mb_strpos($response, 'var url =');
		p($urlStart);

		$urlEnd = mb_strpos($response, ';',$urlStart + 10);
		p($urlEnd);

		$url = mb_substr($response, $urlStart + 11, $urlEnd - $urlStart - 12);
		p($url);*/

		$urlStart = mb_strpos($response, 'var url =') + 20;
		$urlEnd = mb_strpos($response, '.htm?',$urlStart);
		$url = mb_substr($response, $urlStart, $urlEnd - $urlStart);

		$keyStart = mb_strpos($url, '.com/') + 6;
		$goods_id = mb_substr($url, $keyStart);

	}

	// 接收事件推送并回复
	protected function responseMsg() {

		//判读当前 PHP 版本
		$postArr =  phpversion() > '5.7'  ? file_get_contents('php://input') : $GLOBALS['HTTP_RAW_POST_DATA'];

		//1.获取到微信推送过来post数据（xml格式）
		if (!empty($postArr)) {
			$postObj = simplexml_load_string($postArr);

			//toUser为微信用户，fromUser为公众账号
			$toUser = $postObj->FromUserName;
			$fromUser = $postObj->ToUserName;
			//实例化对象
			$wxObj = M("wx");
			//判断该数据包是否是订阅的事件推送
			if (strtolower($postObj->MsgType) == 'event') {

				//如果是关注 subscribe 事件
				if (strtolower($postObj->Event == 'subscribe')) {
					//回复用户消息(纯文本格式)
					$content = '欢迎关注嗨淘优惠 !';
					$returnContent = $wxObj->responseText($toUser, $fromUser, $content);
					echo $returnContent;
				}

				//重扫码事件触发
				if (strtolower($postObj->Event == 'SCAN')) {
					if (strtolower($postObj->EventKey == '2500')) {
						$content = '临时扫码事件';
						$this->sendTemplateMsg();//调用模板消息
					}
					if (strtolower($postObj->EventKey == '3000')) {
						$content = '永久扫码事件';
					}
					$returnContent = $wxObj->responseText($toUser, $fromUser, $content);
					echo $returnContent;
				}

				//自定义菜单事件点击
				if (strtolower($postObj->Event == 'CLICK')) {
					if (strtolower($postObj->EventKey == 'user')) {
						$content = '这是个人中心';
					}
					if (strtolower($postObj->EventKey == 'activity')) {
						$content = '这是活动';
					}
					$returnContent = $wxObj->responseText($toUser, $fromUser, $content);
					echo $returnContent;
				}

				if (strtolower($postObj->Event == 'VIEW')) {
					$content = 'url' . $postObj->EventKey;
					$returnContent = $wxObj->responseText($toUser, $fromUser, $content);
					echo $returnContent;
				}
			}

			if (strtolower($postObj->MsgType) == 'text') {
				$tbkM = M('tbk');
				$c = $tbkM -> get_TopClient();
				$c->appkey = '23536810';
				$c->secretKey = '634c0d55e61e771e6de61c99d1a1cd25';

				$content = trim($postObj->Content);
				$needle = mb_strpos($content, '买');
				if ($needle !== false) {  //如果发送的是 买******
					$postStr = mb_substr($content, $needle + 1);
					$req = $tbkM->get_tbkItem();

					$req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick");
					$req->setQ("{$postStr}");
					$resp = $c->execute($req);
					$contents = array();
					if (!empty($resp->results->n_tbk_item)) {
						$res = $resp->results->n_tbk_item;
						$i = 0;
						foreach ($res as $v) {
							if ($i >= 10) break;
							$contentArr = array(
								'title' => object_to_array($v->title)[0],
								'description' => '',
								'picUrl' => object_to_array($v->small_images->string[0])[0],
								'url' => object_to_array($v->item_url)[0]
							);

							$contents[] = $contentArr;
							$i ++;
						}
					}
					$returnContent = $wxObj->responseNews($toUser, $fromUser, $contents);
					echo $returnContent;

				} else if (is_numeric($content) && strlen($content) == 16) { // 如果发送订单号 , 绑定动作

				} else if (mb_strpos($content, '或复制这条信息') !== false && mb_strpos($content, 'http://') !== false) { // 如果发送的是淘宝口令
					$urlIndex = mb_strpos($content, 'http');
					$wordIndex = mb_strpos($content, '点击链接');
					if($urlIndex !== false && $wordIndex !== false) {
						$url = mb_substr($content, $urlIndex, $wordIndex - $urlIndex);
						$response = curlAPI($url,'get','xml');
						$urlStart = mb_strpos($response, 'var url =') + 20;
						$urlEnd = mb_strpos($response, '.htm?',$urlStart);
						$url = mb_substr($response, $urlStart, $urlEnd - $urlStart);

						$keyStart = mb_strpos($url, '.com/') + 6;
						$goods_id = mb_substr($url, $keyStart);
						if ($goods_id) {
							$req = $tbkM->get_ItemInfo();
							$req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url");
							$req->setNumIids("{$goods_id}");
							$resp = $c->execute($req);

							console('GOODS INFO ==='.print_r($resp,true), 'tbk');
						}
					}

				} else {
					//非上述3种情况
				}
			}
			if (strtolower($postObj->MsgType) == 'text' && trim($postObj->Content) == '4') {
				//注意：进行多图文发送时，子图文个数不能超过10个
				$arr = array(
					array(
						'title' => 'imooc',
						'description' => "imooc is very cool",
						'picUrl' => 'http://www.imooc.com/static/img/common/logo.png',
						'url' => 'http://www.imooc.com',
					),
					array(
						'title' => 'hao123',
						'description' => "hao123 is very cool",
						'picUrl' => 'https://www.baidu.com/img/bdlogo.png',
						'url' => 'http://www.hao123.com',
					),
					array(
						'title' => 'qq',
						'description' => "qq is very cool",
						'picUrl' => 'http://www.imooc.com/static/img/common/logo.png',
						'url' => 'http://www.qq.com',
					),
				);
				$returnContent = $wxObj->responseNews($toUser, $fromUser, $arr);
				echo $returnContent;

			} else if (strtolower($postObj->MsgType) == 'text' && trim($postObj->Content) == '5') {
				$arr = array(
					array(
						'title' => 'imooc',
						'description' => "imooc is very cool",
						'picUrl' => 'http://www.imooc.com/static/img/common/logo.png',
						'url' => 'http://www.imooc.com',
					)
				);
				$returnContent = $wxObj->responseNews($toUser, $fromUser, $arr);
				echo $returnContent;
			} else {
				if (trim($postObj->Content) == '6') {//上海天气
					$url = 'https://api.thinkpage.cn/v3/weather/now.json?key=ozytdx8sjl3b9jqa&location=shanghai&language=zh-Hans&unit=c';
					$output = curlAPI($url);
					foreach ($output as $value) {
						foreach ($value as $v) {
							$content = "上海天气：" . $v['now']['text'] . "\n" . "当前温度：" . $v['now']['temperature'] . "\n" . "相对湿度：" . $v['now']['humidity'] . "\n" . "风向：" . $v['now']['wind_direction'];
						}
					}
					$returnContent = $wxObj->responseText($toUser, $fromUser, $content);
					echo $returnContent;

				} else {
					switch (trim($postObj->Content)) {
						case 1:
							$content = 'tel:13661524424';
							break;
						case 2:
							$content = '姚远';
							break;
						case 3:
							$content = '<a href=\'http://yylovell.cn\'>我的主页</a>';
							break;
						case 'help':
							$content = 'please call the Number:' . "\n" . '1-我的联系方式' . "\n" . '2-我的名字' . "\n" . '3-我的主页' . "\n" . '4-多图文' . "\n" . '5-单图文' . "\n" . '6-上海即时天气情况';
							break;
						/*default:
							$content = '未知指令，输入help查看所有指令';
							break;*/
					}
					$returnContent = $wxObj->responseText($toUser, $fromUser, $content);
					echo $returnContent;
				}
			}//if end
		} else {
			echo "";
			exit;
		}
	}//reponseMsg end

	protected function defineItem()
	{
		//创建微信菜单
		$wxObj = M("wx");
		$access_token = $wxObj->getWxAccessToken();

		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
		$postJson = '{
		 "button":[
				{
				   "name":"个人中心",
				   "sub_button":[
						{
							"type":"view",
							"name":"订单总数",
							"url":"http://http://yhq.pillowabc.com/my/order"
						}
					]
				}
			]
 		}';
		$res = curlAPI($url, 'post', 'json', $postJson);
		echo $res;
	}

	//usetSession[''access_token]
	protected function unsetSession(){
		if($_SESSION['access_token']){
			unset($_SESSION['access_token']);
			echo $_SESSION['access_token']?$_SESSION['access_token']:'无';
		}else{
			echo '无';
		}
	}

	//上传图文
	protected function sendNews(){
		$wxObj = M("wx");
		$access_token = $wxObj->getWxAccessToken();
		$type = 'image';
		$thumb_media_id = $wxObj->sendPhoto($access_token,$type);
		//echo $access_token;
		//echo "<hr>";
		echo $thumb_media_id;
		$url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=".$access_token;
		$postJson='{
                    "articles":
                   [
		            {
                        "thumb_media_id":"'.$thumb_media_id.'",
                        "author":"someone",
			             "title":"菲仕乐",
			             "content_source_url":"www.qq.com",
			             "content":"<h1>菲仕乐活动</h1>",
			             "digest":"this is des",
                        "show_cover_pic":1
		             }
                    ]
                }';

		$res = curlAPI($url, 'post', 'json', $postJson);
		echo $res;
	}

	//群发
	protected function  sendMsgAll(){
		$wxObj = M("wx");
		$access_token = $wxObj->getWxAccessToken();
		$url='https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token='.$access_token;
		$postJson='{
					"touser":"odhT_wn0pp53EbxFKRKNwe7HAlRw",
					"text":{
						   "content":"你好"
						   },
					"msgtype":"text"
				}';
		$res = curlAPI($url, 'post', 'json',$postJson);
		echo $res;
	}

	//网页授权
	protected function getUserDetail(){
		header('content-type:text/html;charset=utf-8');
		//1获取code
		$appid = 'wx66bfe6a77a31a2f9';
		$redirect_uri = urlencode('http://yylovell.cn/lucky/index.php/Index/getUserInfo');
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=yy#wechat_redirect";
		header('location:'.$url);
	}

	protected function getUserInfo(){
		header('content-type:text/html;charset=utf-8');
		//2获取网页授权的access_token
		$wxObj = M("wx");
		$appid = 'wx66bfe6a77a31a2f9';
		$appsecret = '11255b050bb8199c37ffb5cc1740a936';
		$code = $_GET['code'];
		//echo $code;
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
		$res = $wxObj->responseAPI($url, 'get');
		$openid = $res['openid'];
		$access_token = $res['access_token'];
		$info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
		$info_res = $wxObj->responseAPI($info_url, 'get');
		echo $info_res;
	}

	//模板消息
	protected function sendTemplateMsg(){
		//1获取access_token
		$wxObj = M("wx");
		$access_token = $wxObj->getWxAccessToken();
		//echo $access_token;
		//echo "<hr>";
		$name='姚远';
		$money='￥250';
		$data='欢迎再次购买';
		$url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
		$postJson='{
           "touser":"odhT_wn0pp53EbxFKRKNwe7HAlRw",
           "template_id":"3TMG0S4PgsIGVFpL3al9Zd5VgECQSBdfrbL-ZoU9xRE",
           "url":"http://www.baidu.com",
           "data":{
                   "name": {
                       "value":"'.$name.'",
                       "color":"#173177"
                   },
                   "money":{
                       "value":"'.$money.'",
                       "color":"#173177"
                   },
                   "data":{
                       "value":"'.$data.'",
                       "color":"#f45675"
                   }
           }
       }';
		$res = curlAPI($url, 'post','json',$postJson);
		echo ($res);
	}

	//分享朋友圈
	protected function share(){
		//1获取jsapi_ticket
		$wxObj = M("wx");
		$jsapi_ticket = $wxObj->getJsApiTicket();

		$url = "http://yylovell.cn/lucky/index.php/Index/share";
		$timestamp = time();
		$noncestr = $wxObj->getRandCode();
		$signature = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
		$signature = sha1($signature);

		if($signature){
			echo $signature;
			echo '<hr>';
		}else{
			echo '<span style="color:red">未生成</span>';
			echo '<hr>';
		}

		$this->assign('timestamp',$timestamp);
		$this->assign('noncestr',$noncestr);
		$this->assign('signature',$signature);
		$this->display('cakeInfo');
	}

	//生成临时二维码
	protected function getQrcode(){
		header('content-type:text/html;charset=utf-8');
		//1获取ticket
		$wxObj = M("wx");
		$ticket = $wxObj->getQrTicket();

		//2使用ticket获取二维码图片
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
		echo "<h1>临时二维码</h1><img src=".$url." />";
	}

	//生成永久二维码
	protected function getForeverQrcode(){
		header('content-type:text/html;charset=utf-8');
		//1获取ticket
		$wxObj = M("wx");
		$ticket = $wxObj->getForeverQrTicket();

		//2使用ticket获取二维码图片
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
		echo "<h1>永久二维码</h1><img src=".$url." />";
	}
}