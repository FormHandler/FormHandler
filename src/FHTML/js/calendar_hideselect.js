// Hides SELECT boxes that will be under the popup
// Checking Gecko version number to try to include other browsers based on the Gecko engine
function hideSelectBox() {
	if(olNs4 || olOp || olIe55) return;
	var px, py, pw, ph, sx, sw, sy, sh, selEl, v;

	if(olIe4) v = 0;
	else {
		v = navigator.userAgent.match(/Gecko\/(\d{8})/i);
		if(!v) return;   // return if no string match
		v = parseInt(v[1]);
	}

	if (v < 20030624) {  // versions less than June 24, 2003 were earlier Netscape browsers
		px = parseInt(over.style.left);
		py = parseInt(over.style.top);
		pw = o3_width;
		ph = (o3_aboveheight ? parseInt(o3_aboveheight) : over.offsetHeight);
		selEl = (olIe4) ? o3_frame.document.all.tags("SELECT") : o3_frame.document.getElementsByTagName("SELECT");
		for (var i=0; i<selEl.length; i++) {
			if(!olIe4 && selEl[i].size < 2) continue;  // Not IE and SELECT size is 1 or not specified
			sx = pageLocation(selEl[i],'Left');
			sy = pageLocation(selEl[i],'Top');
			sw = selEl[i].offsetWidth;
			sh = selEl[i].offsetHeight;
			if((px+pw) < sx || px > (sx+sw) || (py+ph) < sy || py > (sy+sh)) continue;
			selEl[i].isHidden = 1;
			selEl[i].style.visibility = 'hidden';
		}
	}
}

// Shows previously hidden SELECT Boxes
function showSelectBox() {
	if(olNs4 || olOp || olIe55) return;
	var selEl, v;

	if(olIe4) v = 0;
	else {
		v = navigator.userAgent.match(/Gecko\/(\d{8})/i);
		if(!v) return; 
		v = parseInt(v[1]);
	}

	if(v < 20030624) {
		selEl = (olIe4) ? o3_frame.document.all.tags("SELECT") : o3_frame.document.getElementsByTagName("SELECT");
		for (var i=0; i<selEl.length; i++) {
			if(typeof selEl[i].isHidden !=  'undefined' && selEl[i].isHidden) {
				selEl[i].isHidden = 0;
				selEl[i].style.visibility = 'visible';
			}
		}
	}
}

// function gets the total offset properties of an element
// this same function occurs in overlib_mark.js.
function pageLocation(o,t){
	var x = 0

	while(o.offsetParent){
		x += o['offset'+t]
		o = o.offsetParent
	}

	x += o['offset'+t]

	return x
}