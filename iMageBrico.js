function iMageBrico_saveImage(base64String) {
	if(base64String != null){
		postMessage(base64String,"*");
		dialog.hide();
		return "ok";
	}
}
(function() {
	tinymce.init({
		plugins: "autoresize"
	});
	tinymce.PluginManager.add('iMageBrico', function(editor, url) {
		editor.addCommand('iMageBrico_command', function() {
			var listenerAdded = false;

			editor.windowManager.open({
				title : 'iMage Brico',
				width : jQuery(window).width()*0.8,
				height : jQuery(window).height()*0.8,
				html:'\n\
					<object id="iMageBrico" name="iMageBrico" type="application/x-shockwave-flash" data="<<baseUrl>>iMageBrico.swf" style="width:100%;height:100%;">\n\
						<param name="classid" value="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" />\n\
						<param name="movie" value="<<baseUrl>>iMageBrico.swf" />\n\
						<param name="base" value="<<baseUrl>>">\n\
						<param name="quality" value="high" />\n\
						<param name="bgcolor" value="#ffffff" />\n\
						<param name="wmode" value="direct"/>\n\
						<param name="allowScriptAccess" value="sameDomain" />\n\
						<param name="allowFullScreen" value="true" />\n\
						<a href="http://www.adobe.com/go/getflash">\n\
							<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player"/>\n\
						</a>\n\
					</object>\n\
				',
				buttons: [
						{
							text: 'Insert',
							onclick: function(){
								var SWF = document.getElementById("iMageBrico");
								SWF.takeSnapshot();
								return false;
							}
						},
						{
							text: 'Close', 
							onclick: 'close'
						}
					],
			});
			function iMageBrico_listener(event){
				if(event.data!= null){
					data = {
						src: event.data
					};
					var win = editor.windowManager.getWindows()[0];
					win.close();
					if (window.removeEventListener) {         // For all major browsers, except IE 8 and earlier
						removeEventListener("message", iMageBrico_listener);
					} else if (document.detachEvent) {          // For IE 8 and earlier versions
						detachEvent("onmessage", iMageBrico_listener);
					}
					jQuery(document).ready(function($) {
						var popup_FileName = prompt("Please enter the name you want to give to your image", "iMageBrico");
						if (popup_FileName != null && popup_FileName.length > 0) {
							$.ajax({
								url: ajaxurl, 
								type: "post",
								data: {
									file: event.data,
									fileName: popup_FileName,
									url: url+'/images/icon.svg',
									action: 'iMageBrico_get_results'
									},
								success: function(jsonData){
									editor.selection.setContent(editor.dom.createHTML('img', jsonData));
								},				
								complete:function( jsonData ) {
									//console.log( jsonData );
								},
								dataType: "json" 
							});
						}
					});
				}
			}
			if (!listenerAdded){
				if (window.addEventListener && !listenerAdded){
				  addEventListener("message", iMageBrico_listener, false);
				} else {
				  attachEvent("onmessage", iMageBrico_listener);
				}
				listenerAdded = true;
			}
		});

		editor.addButton('iMageBrico', {
			title : 'iMage Brico',
			image: url+'/images/icon.svg',
			cmd : 'iMageBrico_command'
		});

		editor.addMenuItem('iMageBrico', {
			text : 'iMage Brico',
			image: url+'/images/icon.svg',
			cmd : 'iMageBrico_command',
			context: 'tools'
		});
	});
})();