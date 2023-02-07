// -------------------------------------------------------------------------------------------
// /lib/javascript.php/.../mod/assign/feedback/copybridge/module.js
// -------------------------------------------------------------------------------------------
M.mod_assign_feedback_copybridge = {};

M.mod_assign_feedback_copybridge.init = function(Y, scripturl, bridgeurl, group_id, lang) {
	isScriptLoad = !0;
	lang = (lang) ? lang : 'en';

	if (scripturl == '') { 
		alert(M.util.get_string('url_script_empty', 'plagiarism_copybridge')); 
		isScriptLoad = !1; 
	}
	if (bridgeurl == '') { 
		alert(M.util.get_string('url_bridge_empty', 'plagiarism_copybridge')); 
		isScriptLoad = !1; 
	}

	if (isScriptLoad === !0) {
		$.getScript(scripturl, function() {
			CopymonitorBridge.setUrl(M.cfg.wwwroot + bridgeurl);
			CopymonitorBridge.setLang(lang);
			CopymonitorBridge.initGetCopymonitorInfo(".total-copy-ratio", group_id);
		});
	}
};
