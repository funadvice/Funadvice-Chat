// Funadvice chat
// Sigh... you're in here. This is not really rocket science. 
// Go ahead! copy whats here if you wish.

var last_poll_time = 0;
var intval = 0;
var requests = 0;
var max_requests = 1800;

// resize wall
function resize_chat_window() {
	$('#wall').css({'height':(($(window).height())-80)+'px'});
}

// Set the msg focus and clear
function set_msg_focus() {
	$('#msg').focus();
}

// Update our wall with some returned objects
function update_wall(obj) {
	
	var play = false;
	
	if (obj && obj[0] && obj[0].m) {
		for (key in obj) {
			if (obj[key].u != user) {
				play = true;
			}
			
			// Change it to me, if its me
			if (obj[key].u == user) {
					obj[key].u = '<span style="color: blue;">' + user + '</span>';
			}
			
			str = '<li><span>' + obj[key].t + '</span> <strong>' + obj[key].u + '</strong>' + obj[key].m + '</li>';
			$('#stream').append(str);
		}
		
		if (play) { $.sound.play(sound_file);	}
	}	
	
	// Scroll to the bottom of the wall
	$("#wall").attr({ scrollTop: $("#wall").attr("scrollHeight") });
	set_msg_focus();
}

// Send a message
function msg_send() {

	$.post('/chat.php', 
		{u: user, r: room, msg: $('#msg').val()},
		function(data) {
			update_wall(eval(data));
		}
	);
	
	// Reset the requests counter
	requests = 0;
	
	// Set the message focus and clear the field
	set_msg_focus();
	$('#msg').val('');
}

// Leave the room
function leave() {
	$.post('/chat.php',
		{u: user, r: room, bye: 1},
		function(data) {
		}
	);

	$.post('/chat.php',
		{u: user, r: room, bye: 1},
		function(data) {
		}
	);
	
	window.close();	
}

// Maybe will work in IE?
function npoll() {
	$.get('/chat.php', {u: user, r: room, g: Math.round(new Date().getTime() / 1000)},
	function(data){
		// Write to the wall
		if (obj = eval(data)) {
			update_wall(obj);
		}		
	});
	
	requests = requests + 1;
	
	// Stop polling after a bunch of requests
	if (requests > max_requests) {
		clearInterval(intval);
	}
}

// -- Deprecated
/*
function poll() {
	// Lets start polling
	$.PeriodicalUpdater('/chat.php', {
		method: 'get',
		data: {u: user, r: room},
		minTimeout: 1000,
		maxTimeout: 3000,
		multiplier: 0,
		type: 'text',
		maxCalls: 86400,
		autoStop: 0,
	}, function(data) {
		// Save the last time we polled
		// last_poll_time = Math.round(new Date().getTime() / 1000);
		
		// Write to the wall
		if (obj = eval(data)) {
			update_wall(obj);
		}
	});	
}
*/

// begin chat
$(function(){
	
	// Focus msg
	set_msg_focus();

	// Lets initially resize
	resize_chat_window();
	
	//poll();
	intval = setInterval(npoll, 2000);
	
	// resize wall height if browser height changes
	$(window).resize(function(){
		resize_chat_window();
	});
	
	// If we receive the focus, try to restart the polling if we had shutdown
	// -- later --
	
});

