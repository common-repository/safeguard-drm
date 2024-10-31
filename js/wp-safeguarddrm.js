//  WP SafeGuard DRM
//  Copyright (c) 2023 ArtistScope. All Rights Reserved.
//  safeguard.media
//
// Debugging outputs the generated html into a textbox instead of rendering


//===========================
//   DO NOT EDIT BELOW 
//===========================

var m_bpDebugging = true;

function insertSafeGuarddrm() {
	var OSName = "Unknown";
	if (window.navigator.userAgent.indexOf("Windows")!= -1) OSName="Windows";
	if (window.navigator.userAgent.indexOf("Mac")            != -1) OSName="Mac/iOS";
	if (window.navigator.userAgent.indexOf("Android")            != -1) OSName="Android";
	
	if(m_allowwindows!=1 && OSName=='Windows' && browser == 'Artisbrowser' && parseFloat(version)>=parseFloat(av_Version_windows))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById("safeguarddrm-media-outer").appendChild(para);
		return false;
	}
	if(m_allowmac!=1 && OSName=='Mac/iOS'  && browser == 'Artisbrowser' && parseFloat(version)>=parseFloat(av_Version_mac))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById("safeguarddrm-media-outer").appendChild(para);
		return false;
	}
	if(m_allowandroid!=1 && OSName=='Android' && browser == 'Artisbrowser' && parseFloat(version)>=parseFloat(av_Version_android))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById("safeguarddrm-media-outer").appendChild(para);
		return false;
	}
	if(m_allowios!=1 && OSName=='iOS'  && browser == 'Artisbrowser' && parseFloat(version)>=parseFloat(av_Version_ios))
	{
		const para = document.createElement("p");
		para.innerHTML=errormessage;
		document.getElementById("safeguarddrm-media-outer").appendChild(para);
		return false;
	}
	var src='https://safeguard.media/drm/framed.php?id='+m_token;
	const iframe1 = document.createElement("iframe");

	iframe1.setAttribute('id', 'safeguarddrm');
	iframe1.setAttribute('src', src);
	iframe1.setAttribute('width', 0);
	iframe1.setAttribute('height', 0);
	iframe1.setAttribute('scrolling', 'no');
	
	if (m_bpDebugging == true) {
		const textarea1 = document.createElement("textarea");
		textarea1.setAttribute('rows', '27');
		textarea1.setAttribute('cols', '80');
		textarea1.appendChild(iframe1);
		textarea1.value = iframe1.outerHTML;

		document.getElementById("safeguarddrm-media-outer").appendChild(textarea1);
	}
	else
	{
		document.getElementById("safeguarddrm-media-outer").appendChild(iframe1);
	}
}