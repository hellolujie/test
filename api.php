<?php
    /**
     * 微信api接口(wx_sample.php)
     */
    // 调试储存错误日志
    ini_set("error_log",__DIR__."/error.log");

    require 'common.php';
    require 'WeChat.class.php';
    // 定义TOKEN密钥
    define("TOKEN", "weixin");


class wechatCallbackapiTest extends WeChat
{
	/**
	 * 调用checkSignature规则验证认证
	 */
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        //判断验证数据来源的验证是否通过
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
    /**
     * 自动回复功能
     */
    public function responseMsg()
    {
		//get post data, May be due to the different environments
		//接收微信客户端(手机)发送过来的XML数据
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
      	// 判断数据是否为空
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
		        // 主要功能：防止XXE攻击
                libxml_disable_entity_loader(true);
                // 对XML数据进行解析生成simplexml对象
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
              	// 微信的客户端openid
                $fromUsername = $postObj->FromUserName;
                // 微信公众平台
                $toUsername = $postObj->ToUserName;
                // 微信客户端(手机)向公众平台发送的关键词
                $keyword = trim($postObj->Content);
                // 时间戳
                $time = time();
                // 调用$tmp_arr;定义的消息模板
                global $tmp_arr;
				// 接收MsgType节点并判断其类型
				switch($postObj->MsgType) {
				    case 'text':
				    	// 判断接受过来文本的值
				        if($keyword == '图片') {
				            //定义相关变量
				            $msgType = "image";
				            //定义mediaid
				            $mediaid = 'S7jUH_TUZw8JQuOMkVIuWPvS-Wz6n8hw3yHKAr-4gqrBjmj3oQLi76POXSVEj_w0';
				            //使用sprintf函数格式化XML文档
				            $resultStr = sprintf($tmp_arr['image'], $fromUsername, $toUsername, $time, $msgType, $mediaid);
				            //返回格式化后的XML数据
				            echo $resultStr;
				        } elseif($keyword == '音乐') {
				            //定义相关的变量
				            $msgType = "music";
				            //定义与音乐相关的变量信息
				            $title = '冰雪奇缘';
				            $description = '冰雪奇缘原生大碟';
				            $url = 'http://www.zdzd.shop/music.mp3';
				            $hqurl = 'http://www.zdzd.shop/music.mp3';
                            $thumbMediaId = 'aavu9b4pX_iBoSLiNCquXN5uNYuvQGXAqGTDtbbbOJqFn2_a5rPimtxglDmXnfPU';
				            //使用sprintf函数对music模板进行格式化
				            $resultStr = sprintf($tmp_arr['music'], $fromUsername, $toUsername, $time, $msgType, $title, $description, $url, $hqurl,$thumbMediaId);
				            file_put_contents('wx.log', $resultStr, FILE_APPEND);
				            //返回格式化后的XML数据到客户端
				            echo $resultStr;
				        } elseif($keyword == '单图文') {
				            //定义相关的变量
				            $msgType = "news";
				            $count = 1;
				            $str = '<item>
                                    <Title><![CDATA[最实用的47个让你拍照好看的方法]]></Title>
                                    <Description><![CDATA[怎样拍照好看?有个会拍照的男朋友是怎么样的体验?怎么样把女朋友拍得漂亮...]]></Description>
                                    <PicUrl><![CDATA[http://www.zdzd.shop/images/1.jpg]]></PicUrl>
                                    <Url><![CDATA[http://www..com/]]></Url>
                                    </item>';
				            //使用sprintf函数对XML模板进行格式化
				            $resultStr = sprintf($tmp_arr['news'], $fromUsername, $toUsername, $time, $msgType, $count, $str);
				            //使用file_put_contents把格式化后的XML代码写入到日志中
				            file_put_contents('wx.log', $resultStr, FILE_APPEND);
				            //返回格式化后的XML数据到客户端
				            echo $resultStr;
				        } elseif($keyword == '多图文') {
				            //定义相关变量
				            $msgType = "news";
                            //链接数据库
                            mysql_connect('localhost','root','mysql');
                            mysql_query('use wechat');
                            mysql_query('set names utf8');
                            //定义sql语句
                            $sql = 'select title,description,url,picurl from wc_article Limit 4';
                            //执行sql语句
                            $res = mysql_query($sql);
				            //定义图文数量
				            $count = 4;
				            //定制$str(item选项）
				            $str ="";
                            while($row = mysql_fetch_assoc($res)) {
                                $str .= "<item>
                                    <Title><![CDATA[{$row['title']}]]></Title>
                                    <Description><![CDATA[{$row['description']}]]></Description>
                                    <PicUrl><![CDATA[{$row['picurl']}]]></PicUrl>
                                    <Url><![CDATA[{$row['url']}]]></Url>
                                    </item>";
                            }
				            //使用sprintf函数对XML模板进行格式化
				            $resultStr = sprintf($tmp_arr['news'], $fromUsername, $toUsername, $time, $msgType, $count, $str);
				            //返回格式化后的XML数据到客户端
				            echo $resultStr;
				        }elseif($keyword == '文本'){
                            //第一步:获取access_token
                            $access_token = $this->get_token();
                            //第二步:定义请求的url链接
                            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
                            //第三步:定义要回复的文本内容
                            $contentStr = '这里是使用json数据方式处理的客服消息接口';
                            //第四步:使用urlencode函数进行汉字的编码，防止出现中文乱码
                            $contentStr = urlencode($contentStr);
                            //第五步:组装数组
                            $content_arr = array('content'=>$contentStr);
                            //第六步:组装要发送的数据结构
                            $reply_arr = array('touser'=>"{$fromUsername}",'msgtype'=>'text','text'=>$content_arr);
                            //第七步:使用json_encode对数据进行转义
                            $data = json_encode($reply_arr);
                            //第八步:使用urldecode对数据进行解码操作
                            $data = urldecode($data);
                            //第九步:发送http请求（POST方式）
                            $this->http_request($url, $data);
                        }elseif($keyword == '图文') {
                            //第一步:获取access_token
                            $access_token = $this->get_token();
                            //第二步:定义请求的url链接
                            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
                            //第三步:定义要回复的图文信息
                            $content_arr1 = array(
                                'title'=>urlencode('最好看的桌面壁纸没有之一'),
                                'url'=>'http://www.zdzd.shop/',
                                'picurl'=>'http://www.zdzd.shop/images/3.jpg'
                            );
                            $content_arr2 = array(
                                'title'=>urlencode('福利来一波~~~'),
                                'url'=>'http://www.zdzd.shop/',
                                'picurl'=>'http://www.zdzd.shop/images/4.jpg'
                            );
                            //第四步:把图文信息进一步组装
                            $content_arr = array($content_arr1, $content_arr2);
                            $content_arr = array('articles'=>$content_arr);
                            //第五步:定义要回复的数据格式
                            $reply_arr = array('touser'=>"{$fromUsername}",'msgtype'=>'news','news'=>$content_arr);
                            //第六步:使用json_encode对$reply_arr进行转义，生成json格式
                            $data = json_encode($reply_arr);
                            //第七步:使用urldecode进行解码操作
                            $data = urldecode($data);
                            //第八步:调用curl库实现数据的发送
                            $this->http_request($url, $data);
                        }else{
                            //$msgType = "text";
				            //$contentStr = "我书读的少,能说话就请你不要码字啦";
				            //$resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
				            //echo $resultStr;

                            //接收微信语音识别结果
                            $rec = $postObj ->Content;
                            //定义请求的url地址
                            $url = "http://www.tuling123.com/openapi/api";
                            //第一步：创建curl
                            $ch = curl_init();
                            //第二步：设置curl
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            //定义要传输的数据
                            $data = array(
                                'key'=>'9009fc44f168cfc7055c8a469821ce9b',
                                'info'=>$rec,
                                'userid'=>'12345678'
                            );
                            //使用json_encode转化为json格式
                            $data = json_encode($data);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            //设置HTTP头信息
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                    'Content-Type:application/json',
                                    'Content-Length:'.strlen($data))
                            );
                            //第三步：执行curl
                            $str = curl_exec($ch);
                            //第四步：关闭curl
                            curl_close($ch);
                            //使用json_decode进行转义操作
                            $json = json_decode($str);
                            //获取输出结果
                            echo $json->text;
                            $msgType = "text";
                            $contentStr = $json ->text;
                            $resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                            break;
				        }
				    break;

				    case 'image':
                        $msgType = "text";
                        $contentStr = "图片";
                        $resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        break;

                    case 'voice':
                        //接收微信语音识别结果
                        $rec = $postObj ->Recognition;
                        //定义请求的url地址
                        $url = "http://www.tuling123.com/openapi/api";
                        //第一步：创建curl
                        $ch = curl_init();
                        //第二步：设置curl
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        //定义要传输的数据
                        $data = array(
                            'key'=>'9009fc44f168cfc7055c8a469821ce9b',
                            'info'=>$rec,
                            'userid'=>'12345678'
                        );
                        //使用json_encode转化为json格式
                        $data = json_encode($data);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        //设置HTTP头信息
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type:application/json',
                                'Content-Length:'.strlen($data))
                        );
                        //第三步：执行curl
                        $str = curl_exec($ch);
                        //第四步：关闭curl
                        curl_close($ch);
                        //使用json_decode进行转义操作
                        $json = json_decode($str);
                        //获取输出结果
                        echo $json->text;
                        $msgType = "text";
                        $contentStr = $json ->text;
                        $resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        break;

                    case 'video':
                        $msgType = "text";
                        $contentStr = "视屏";
                        $resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        break;

                    case 'shortvideo':
                        $msgType = "text";
                        $contentStr = "小视屏";
                        $resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        break;

                    case 'location':
                        //获取经度
                        $longitude = $postObj->Location_Y;
                        //获取维度
                        $latitude = $postObj->Location_X;
                        $keyword = urlencode('肯德基');
                        $url = "http://restapi.amap.com/v3/place/around?key=a640408b4602efeab177e0dcb2b65d14&location={$longitude},{$latitude}&keywords={$keyword}&types=050301&offset=1&page=1&extensions=all";
                        //发送请求
                        $str = $this->http_request($url);
                        //使用json_decode对$str进行转义，生成$json对象
                        $json = json_decode($str);
                        //获取店铺名称、地址位置、电话、距离长度
                        $name = $json->pois[0]->name;
                        $address = $json->pois[0]->address;
                        $tel = $json->pois[0]->tel;
                        $distance = $json->pois[0]->distance;
                        //以文本形式返回数据
                        $msgType = "text";
                        $contentStr = "离您最近的KFC，名称：{$name}，详细地址：{$address}，联系电话：{$tel}，距离长度：{$distance}";
                        $resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;

                        break;

				    case 'event':
				        if($postObj->Event == 'subscribe') {
				            $msgType = "text";
				            $contentStr = "谢谢你长得那么好看还关注我！  回复:图片 音乐 单图文 多图文 有惊喜哦........";
				            $resultStr = sprintf($tmp_arr['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
				            echo $resultStr;
				        }
                        //判断单击按钮的事件推送
                        if($postObj->Event == 'CLICK' && $postObj->EventKey == 'V1001_TODAY_MUSIC') {
                            //定义相关的变量
                            $msgType = "music";
                            //定义与音乐相关的变量信息
                            $title = '冰雪奇缘';
                            $description = '冰雪奇缘原生大碟';
                            $url = 'http://www.zdzd.shop/music.mp3';
                            $hqurl = 'http://www.zdzd.shop/music.mp3';
                            //使用sprintf函数对music模板进行格式化
                            $resultStr = sprintf($tmp_arr['music'], $fromUsername, $toUsername, $time, $msgType, $title, $description, $url, $hqurl);
                            //返回格式化后的XML数据到客户端
                            echo $resultStr;
                        }
				    break;
				}
        }else {
        	echo "";
        	exit;
        }
    }
    /**
     * 数据来源的验证规则
     */
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        // 判断TOKEN常量是否定义
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        // 开发者提交信息后，微信服务器将发送GET请求到填写的服务器地址URL上
		// echostr-->随机字符串	nonce-->随机数	timestamp-->时间戳
		// signature-->微信加密签名(结合token+timestamp+nonce)
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce     = $_GET["nonce"];

		$token     = TOKEN;
		$tmpArr    = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        // 字典序排序:SORT_STRING -->单元被作为字符串来比较,返回true or false
		sort($tmpArr, SORT_STRING);
		// 将数组拼接成一个字符串,进行shal加密
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		// 加密的字符串和签名进行对比,标识请求来源于微信
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

    // 实例化wechatCallbackapiTest生成$wechatObj对象
    $wechatObj = new wechatCallbackapiTest();
    // 调用valid方法->checkSignature()实现数据验证
    // $wechatObj->valid();
    // 开启自动回复功能
    $wechatObj->responseMsg();
?>