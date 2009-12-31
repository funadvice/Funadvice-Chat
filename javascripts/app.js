// Funadvice chat
// Sigh... you're in here. This is not really rocket science. 
// Go ahead! copy whats here if you wish.

// resize wall
function resize_chat_window() {
	$('#wall').css({'height':(($(window).height())-80)+'px'});
}

// Set the msg focus and clear
function set_msg_focus() {
	$('#msg').focus();
	$('#msg').val('');
}

// Update our wall with some returned objects
function update_wall(obj) {
	
	var play = false;
	
	if (obj[0].m) {
		for (key in obj) {
			if (obj[key].u != user) {
				play = true;
			}
			
			str = '<li><span>' + obj[key].t + '</span> <strong>' + obj[key].u + '</strong>' + obj[key].m + '</li>';
			$('#stream').append(str);
		}
		
		if (play) { $.sound.play(sound_file);	}
	}	
	
	// Scroll to the bottom of the wall
	$("#wall").attr({ scrollTop: $("#wall").attr("scrollHeight") });
}

// Send a message
function msg_send() {

	$.post('/chat.php', 
		{u: user, r: room, msg: $('#msg').val()},
		function(data) {
			update_wall(eval(data))
		}
	);

	set_msg_focus();
}

// Leave the room
function leave() {
	$.post('/chat.php',
		{u: user, r: room, bye: 1},
		function(data) {
			window.close();
		}
	);
}

// begin chat
$(function(){
	
	// Focus msg
	set_msg_focus();

	// Lets initially resize
	resize_chat_window();
	
	// Lets start polling
	$.PeriodicalUpdater('/chat.php', {
		method: 'get',
		data: {u: user, r: room},
		minTimeout: 1000,
		maxTimeout: 5000,
		multiplier: 2,
		type: 'text',
		maxCalls: 0,
		autoStop: 0,
	}, function(data) {
		if (obj = eval(data)) {
			update_wall(obj);
		}
	});
	
	// resize wall height if browser height changes
	$(window).resize(function(){
		resize_chat_window();
	});
});