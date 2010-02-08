<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <title>Funadvice Chat</title>
   <link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
   <link rel="stylesheet" href="/stylesheets/main.css">
	 <script type="text/javascript">
	 var user = "<?= $_GET['u'] ?>";
	 var room = "<?= $_GET['r'] ?>";
	 var sound_file = "/assets/brad-receive.wav";
	 </script>
	 <script type="text/javascript" src="/javascripts/jquery.js"></script>
	 <script type="text/javascript" src="/javascripts/jquery.periodicalupdater.js"></script>
	 <script type="text/javascript" src="/javascripts/jquery.sound.js"></script>
	 <script type="text/javascript" src="/javascripts/app.js"></script>
</head>
<body style="margin: 0; padding: 0;">
<div id="doc3" class="yui-t7">
   <div id="hd">
	 		[ <b><?= strtolower(trim($_GET['u'])) ?></b> ] &nbsp;
			<!-- <a href="#">Invite:</a> | -->
			<span class="fclose"><a href="#" onClick="leave(); return(false);">Leave</a></span> &nbsp; |
			<a href="http://www.funadvice.com/contact" target="_new">Feedback</a>
			&nbsp;&nbsp;
			<span id="info" style="display: none;"></span>
	 </div>
   <div id="bd">
	<div class="yui-g">
		<div id="wall">
		<ul id="stream">
			<li><span>00:00</span> <i>Welcome to the chat room!</i></li>
		</ul>
		</div>
	</div>
	</div>
   <div id="ft">
	 	<form method="POST" action="/" onSubmit="msg_send(); return(false);">
			<input type="text" name="msg" id="msg" value="" autocomplete="off" onFocus="this.autocomplete='off'" />
		</form>
	 </div>
</div>
</body>
</html>
