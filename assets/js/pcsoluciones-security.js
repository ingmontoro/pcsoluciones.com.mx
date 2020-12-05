var logingout = false;
/*function lockScreen() {
	location.href = "loginScreen.php";
}*/
function logout() {
	if(!logingout) {
		window.location = 'phrapi/access/logout';
		logingout = true;	
	}
}
function stopTimer() { // to be called when you want to stop the timer
	if (myTimer !== null) {
		clearTimeout(myTimer);
		myTimer = null;
	}
}
function loadIndex() {
	//loadSection("ordenes.php");
}
function showName() {
	$("#divUserIntro").show('slide', {direction: 'left'}, 500).delay(1000).hide('slide', {direction: 'left'}, 350, function(){loadIndex();});
	$("#divUserIntroN").show('slide', {direction: 'right'}, 500).delay(1000).hide('slide', {direction: 'left'}, 350);
}
function startTimer() {
	myTimer = setTimeout(function() {logout();}, (minutes  * 60 * 1000));
	//showName();
	//loadIndex();*/
	/*if (myTimer != null) {
		stopTimer();
		myTimer = setTimeout(function() {logout();}, (minutes * 60 * 1000));
	} else {
		myTimer = setTimeout(function() {logout();}, (minutes  * 60 * 1000));
	}*/
	//printTimer();
}
function resetTimer() {
	if (myTimer != null) {
		stopTimer();
		myTimer = setTimeout(function() {logout();}, (minutes * 60 * 1000));
	}
}
/*function printTimer() {
	
	var d = new Date();
	d.setMinutes(d.getMinutes() + 10);
	var cdd = d.getTime();
	var x = setInterval(function() {
		var n = new Date();
		var n = n.getTime();
		// Find the distance between now an the count down date
	    var distance = cdd - n;
	    // Time calculations for days, hours, minutes and seconds
	    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
	    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
	    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
	    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
	    // Output the result in an element with id="demo"
	    document.getElementById("timer").innerHTML = days + "d " + hours + "h "
	    + minutes + "m " + seconds + "s ";
	}, 1000);
}*/