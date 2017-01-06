<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/30
 * Time: 上午11:05
 */

class wx {

	/*获取access_token
	 *
	 */
	public function getWxAccessToken(){
		//将access_token存在session中
		if($_SESSION['access_token'] && $_SESSION['expire_time']>time()){
			return $_SESSION['access_token'];

		}else{
			$appid = 'wx01c7e071150216b7';
			$appsecret = 'c80c7b33ffbc0906a095567ceb872c93';
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
			$res = curlAPI($url, 'get', 'json');
			$access_token = $res['access_token'];
			$_SESSION['access_token'] = $access_token;
			$_SESSION['expire_time']  = time()+7000;
			return $access_token;
		}
	}

	/*微信被动回复之纯文本
		 * @$postObj 微信传回信息对象
		 * @$content 文本回复内容
		 * template模板样式参考微信公众平台开发文档
		 */
	public function responseText($toUser, $fromUser, $content){
		//template模板
		$template = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>";
		//注意模板中的中括号 不能少 也不能多
		$time = time();
		$msgType = 'text';
		return sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
	}

	/*微信被动回复之多图文
	* @$arr 图文回复内容
	* template模板样式参考微信公众平台开发文档
	*/
	public function responseNews($toUser, $fromUser, $arr)
	{
		$template = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<ArticleCount>" . count($arr) . "</ArticleCount>
						<Articles>";
		foreach ($arr as $k => $v) {
			$template .= "<item>
							<Title><![CDATA[" . $v['title'] . "]]></Title>
							<Description><![CDATA[" . $v['description'] . "]]></Description>
							<PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>
							<Url><![CDATA[" . $v['url'] . "]]></Url>
							</item>";
		}

		$template .= "</Articles>
						</xml> ";
		return sprintf($template, $toUser, $fromUser, time(), 'news');
	}

	/*获取jsapi_ticket
   *
   */
	public function getJsApiTicket(){
		//将jsapi_ticket存在session中
		if($_SESSION['jsapi_ticket'] && $_SESSION['jsapi_ticket_expire_time']>time()){
			return $_SESSION['jsapi_ticket'];

		}else{
			$access_token = $this->getWxAccessToken();

			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
			$res = $this->responseAPI($url);
			$jsapi_ticket = $res['ticket'];
			$_SESSION['jsapi_ticket'] = $jsapi_ticket;
			$_SESSION['jsapi_ticket_expire_time']  = time()+7000;
			return $jsapi_ticket;
		}
	}

	/*获取16位随机码
	 *由大小写字母和数字组成
	 */
	public function getRandCode($num=16){
		$arr=array(
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
			'0','1','2','3','4','5','6','7','8','9'
		);
		$tm = '';
		$max = count($arr);
		for($i=1;$i<=$num;$i++ ){
			$tm .=$arr[rand(0,$max-1)];
		}
		return $tm;
	}

	/*生成临时二维码ticket
	 *
	 */
	public function getQrTicket(){
		//将ticket存在session中
		if($_SESSION['ticket'] && $_SESSION['ticket_expire_time']>time()){
			return $_SESSION['ticket'];

		}else{
			$access_token = $this->getWxAccessToken();

			$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
			$postJson='{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 2500}}}';

			$res = $this->responseAPI($url , 'post','json',$postJson);

			$ticket = $res['ticket'];
			$_SESSION['ticket'] = $ticket;
			$_SESSION['ticket_expire_time']  = time()+604800;
			return $ticket;
		}
	}

	/*生成永久二维码ticket
	 *
	 */
	public function getForeverQrTicket(){
		//将ticket存在session中
		if($_SESSION['for_ticket'] ){
			return $_SESSION['for_ticket'];

		}else{
			$access_token = $this->getWxAccessToken();
			$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
			$postJson='{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 3000}}}';

			$res = $this->responseAPI($url , 'post','json',$postJson);

			$for_ticket = $res['for_ticket'];
			$_SESSION['for_ticket'] = $for_ticket;
			return $for_ticket;
		}
	}


	//上传素材
	public function sendPhoto($access_token,$type){
		//echo $access_token;
		$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$type;
		$mediaUrl = 'http://yylovell.cn/weixin/App/Public/Images/fsl.jpg';
		$postData = array('media'=>'@'.$mediaUrl) ;
		$postJson = json_encode($postData,true);
		//$postJson='{"media":"@http://yylovell.cn/weixin/App/Public/Images/fsl.jpg"}';
		$res = $this->responseAPI($url,'post','json',$postJson);
		//dump($res);
		return $res['media_id'];
	}

}