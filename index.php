<?php
error_reporting(0);

function getpage($url)
{
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/cookie.mp3");
	curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/cookie.mp3");

	$page = curl_exec($ch);
	return $page;
}

function proxy_ipv4($url)
{
	$url = urlencode($url);
	$server = "https://ipv4.trolyfacebook.com/proxy/?url=$url";
	return getpage($server);
}

function proxy_ipv6($url)
{
	$url = urlencode($url);
	$server = "https://ipv6.trolyfacebook.com/proxy/?url=$url";
	return getpage($server);
}

function check_info_ip()
{
	$ip = $_SERVER['REMOTE_ADDR'];
	if (stripos(strtolower($ip), ":") !== false )
	{
		$ip_type = "ipv6";
	}
	else
	{
		$ip_type = "ipv4";
	}
	return $ip_type;
}

function sort_array (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    natcasesort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

function get_link_drive($id)
{
	$data = array();
	$url = "https://docs.google.com/feeds/get_video_info?formats=ios&mobile=true&docid=$id";
	$ip_type = check_info_ip();
	
	if($ip_type == "ipv4")
	{
		//get bang ipv6
		$info_video = proxy_ipv6($url);
	}
	else
	{
		//get bang ipv4
		$info_video = proxy_ipv4($url);
	}
	
	if($info_video != "")
	{
		$temp = explode("&", $info_video);
		$dem = count($temp);
		
		for($i=0;$i<$dem;$i++)
		{
			$temp2 = null;
			$temp2 = explode("=", $temp[$i]);
			
			if($temp2[0] == "title")
			{	
				$data['title'] = urldecode($temp2[1]);
			}
			
			if($temp2[0] == "fmt_stream_map")
			{
				$temp3 = null;	
				$temp3 = explode(",", urldecode($temp2[1]));
				$stt = 0;
				for($j=0;$j<count($temp3);$j++)
				{
					$streamlink = str_replace("&driveid=$id", "", $temp3[$j]);
					$temp4 = null;
					$temp4 = explode("|", $streamlink);
					if($temp4[0] == "37")
					{
						$q = "1080p";
						$data['data'][$stt]['label'] = $q;
						$data['data'][$stt]['link'] = preg_replace("/\/[^\/]+\.google\.com/","/redirector.googlevideo.com", $temp4[1]);
						$stt = $stt + 1;
					}
					
					if($temp4[0] == "22")
					{
						$q = "720p";
						$data['data'][$stt]['label'] = $q;
						$data['data'][$stt]['link'] = preg_replace("/\/[^\/]+\.google\.com/","/redirector.googlevideo.com", $temp4[1]);
						$stt = $stt + 1;
					}
					
					if($temp4[0] == "59")
					{
						$q = "480p";
						$data['data'][$stt]['label'] = $q;
						$data['data'][$stt]['link'] = preg_replace("/\/[^\/]+\.google\.com/","/redirector.googlevideo.com", $temp4[1]);
						$stt = $stt + 1;
					}
					
					if($temp4[0] == "18")
					{
						$q = "360p";
						$data['data'][$stt]['label'] = $q;
						$data['data'][$stt]['link'] = preg_replace("/\/[^\/]+\.google\.com/","/redirector.googlevideo.com", $temp4[1]);
						$stt = $stt + 1;
					}
				}
			}
		}
		
	}
	else 
	{
		$data['error'] = "1";
		$data['msg'] = "Không lấy được thông tin video";
	}
	
	return $data;
}

if($_POST['submit'] != "")
{
	$url = $_POST['url'];
	$tmp = explode("file/d/",$url);
	$tmp2 = explode("/",$tmp[1]);
	$id = $tmp2[0];
	$info = get_link_drive($id);
	if(count($info['data'] > 0))
	{
		$showplayer = 1;
		$linkvideo = "";
		for($i=0;$i<count($info['data']);$i++)
		{
			$linkvideo.= '{file: "'.$info['data'][$i]['link'].'",label: "'.$info['data'][$i]['label'].'", type: "video/mp4"},';
		}
	}
	
}

?>

<html>
<head>
	<title>Google Drive</title>
<script src="//ssl.p.jwpcdn.com/player/v/7.11.2/jwplayer.js"></script>
<script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
	<script>jwplayer.key = "dWwDdbLI0ul1clbtlw+4/UHPxlYmLoE9Ii9QEw==";</script>
</head>
<body>
	<form action="" method="POST">
	
	<input type="text" size="50" name="url" value="https://drive.google.com/file/d/0BypABqNqmyIaZHFNQnVrcF8tdWM/view?usp=sharing"/>
	<input type="submit" value="GET" name="submit" />
	</form>
	
	<br/>
	Your IP: <?php echo $_SERVER['REMOTE_ADDR'];?>
	<br/>
	<div id="mediaplayer"></div>

	
	<pre>
	<?php
		print_r($info);
	?>
	</pre>
	
	<?php
	if($showplayer == 1)
	{
	?>
	<script>
	jwplayer("mediaplayer").play();
	
	jwplayer("mediaplayer").setup({
	autostart: false,
	controls: true,
	primary: "html5",
	flashplayer: "jwplayer/jwplayer.flash.swf",
	skin: {"name": "tube", "url":"jwplayer/tube/tube.min.css"},
	stagevideo: false,
	stretching: "uniform",
	width: "800px",
	height: "500px",
	allowfullscreen: true,
	allowscriptaccess: "always",
	title: "<?php echo $info['title'];?>",
	sources: [
	<?php echo $linkvideo;?>
	],
	});
	
	
	jwplayer("mediaplayer").addButton( "jwplayer/downloads.png", "Download Video", function() { window.open(jwplayer("mediaplayer").getPlaylistItem()["file"], "_blank").blur(); }, "download" );
	</script>
	
	
	<?php } ?>
</body>
</html>