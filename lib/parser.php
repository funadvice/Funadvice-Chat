<?php

// Convert images and videos to proper embeds
function parser($text=false) {
	
	$smilies = array(
		':D' => 'icon_e_biggrin.gif',
		':-D' => 'icon_e_biggrin.gif',
		':)' => 'icon_e_smile.gif',
		':-)' => 'icon_e_smile.gif',
		';)' => 'icon_e_wink.gif',
		';-)' => 'icon_e_wink.gif',
		':(' => 'icon_e_sad.gif',
		':-(' => 'icon_e_sad.gif',
		':o' => 'icon_e_surprised.gif',
		':-o' => 'icon_e_surprised.gif',
		':?' => 'icon_e_confused.gif',
		':-?' => 'icon_e_confused.gif',
		':???:' => 'icon_e_confused.gif',
		'8-)' => 'icon_cool.gif',
		':x' => 'icon_mad.gif',
		':-x' => 'icon_mad.gif',
		':P' => 'icon_razz.gif',
		':-P' => 'icon_razz.gif',
		':!:' => 'icon_exclaim.gif',
		':?:' => 'icon_question.gif',
		':|' => 'icon_neutral.gif',
		':-|' => 'icon_neutral.gif'
	);
	
	$YOUTUBE = '<br/><object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/###&hl=en_US&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/###&hl=en_US&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="344"></embed></object><br/>';	
	
	$SCHEMES = array('http', 'https', 'ftp', 'mailto');
	$URL_FORMAT = '~(?<!\w)((?:'.implode('|',
   	$SCHEMES).'):' # protocol + :
		.   '/*(?!/)(?:' # get any starting /'s
		.   '[\w$\+\*@&=\-/]' # reserved | unreserved
		.   '|%%[a-fA-F0-9]{2}' # escape
		.   '|[\?\.:\(\),;!\'](?!(?:\s|$))' # punctuation
		.   '|(?:(?<=[^/:]{2})#)' # fragment id
		.   '){2,}' # at least two characters in the main url part
		.   ')~';	
			
		
		// Deal with URLS
		if (preg_match_all($URL_FORMAT, $text, $matches)) {
			
			foreach ($matches[1] as $url) {	
				if (preg_match('/youtube\.com\/watch\?(v=.*?)$/ix', $url, $match)) {
					// Youtube Videos
					$vars = array();
					parse_str($match[1], $vars);
					if ($vars['v']) {
						$text = str_replace($url, str_replace('###', $vars['v'], $YOUTUBE), $text);
					}
				} elseif (preg_match('/\.gif$|\.png$|\.jpg$|\.jpeg$/ix', trim($url))) {
					// Images
					$text = str_replace($url, "<br/><a href=\"{$url}\" target=\"_new\" rel=\"nofollow\"><img src=\"{$url}\" style=\"uimage\" /></a><br/>", $text);
				} else {
					// Any other urls, including mailtos
					$text = str_replace($url, "<a href=\"{$url}\" rel=\"nofollow\" target=\"_new\">{$url}</a>", $text);
				}
			}
		}
		
		// Deal with emoticons (only match first one)
		while(list($key, $val) = each($smilies)) {
			$pos = strpos($text, $key);
			if ($pos === false) { continue; }
			$text = str_replace($key, "<img src=\"/assets/smilies/{$val}\" />", $text);
			//break;
		}		
		
		return($text);
}


?>
