var permanotice, tooltip, _alert;
		$(function(){
			// This is how to change the default settings for the entire page.
			//$.pnotify.defaults.width = "400px";
			// If you don't want new lines ("\n") automatically converted to breaks ("<br />")
			//$.pnotify.defaults.insert_brs = false;

			$.pnotify({
				title: "Pines Notify",
				text: "Welcome. Try hovering over me. You can click things behind me, because I'm non-blocking.",
				nonblock: true,
				before_close: function(pnotify){
					// You can access the notice's options with this. It is read only.
					//pnotify.opts.text;

					// You can change the notice's options after the timer like this:
					pnotify.pnotify({
						title: pnotify.opts.title+" - Enjoy your Stay",
						before_close: null
					});
					pnotify.pnotify_queue_remove();
					return false;
				}
			});



		function show_rich() {
			$.pnotify({
				title: '<span style="color: green;">Rich Content Notice</span>',
				text: '<span style="color: blue;">Look at my beautiful <strong>strong</strong>, <em>emphasized</em>, and <span style="font-size: 1.5em;">large</span> text.</span>'
			});
		}



		var stack_topleft = {"dir1": "down", "dir2": "right", "push": "top"};
		var stack_bottomleft = {"dir1": "right", "dir2": "up", "push": "top"};
		var stack_custom = {"dir1": "right", "dir2": "down"};
		var stack_custom2 = {"dir1": "left", "dir2": "up", "push": "top"};
		var stack_bar_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 0, "spacing2": 0};
		var stack_bar_bottom = {"dir1": "up", "dir2": "right", "spacing1": 0, "spacing2": 0};
		/*********** Positioned Stack ***********
			* This stack is initially positioned through code instead of CSS.
			* This is done through two extra variables. firstpos1 and firstpos2
			* are pixel values, relative to a viewport edge. dir1 and dir2,
			* respectively, determine which edge. It is calculated as follows:
			*
			* - dir = "up" - firstpos is relative to the bottom of viewport.
			* - dir = "down" - firstpos is relative to the top of viewport.
			* - dir = "right" - firstpos is relative to the left of viewport.
			* - dir = "left" - firstpos is relative to the right of viewport.
			*/
		var stack_bottomright = {"dir1": "up", "dir2": "left", "firstpos1": 25, "firstpos2": 25};

		function show_stack_topleft(type) {
			var opts = {
				title: "Over Here",
				text: "Check me out. I'm in a different stack.",
				addclass: "stack-topleft",
				stack: stack_topleft
			};
			switch (type) {
				case 'error':
					opts.title = "Oh No";
					opts.text = "Watch out for that water tower!";
					opts.type = "error";
					break;
				case 'info':
					opts.title = "Breaking News";
					opts.text = "Have you met Ted?";
					opts.type = "info";
					break;
				case 'success':
					opts.title = "Good News Everyone";
					opts.text = "I've invented a device that bites shiny metal asses.";
					opts.type = "success";
					break;
			}
			$.pnotify(opts);
		};
		function show_stack_bottomleft(type) {
			var opts = {
				title: "Over Here",
				text: "Check me out. I'm in a different stack.",
				addclass: "stack-bottomleft",
				stack: stack_bottomleft
			};
			switch (type) {
				case 'error':
					opts.title = "Oh No";
					opts.text = "Watch out for that water tower!";
					opts.type = "error";
					break;
				case 'info':
					opts.title = "Breaking News";
					opts.text = "Have you met Ted?";
					opts.type = "info";
					break;
				case 'success':
					opts.title = "Good News Everyone";
					opts.text = "I've invented a device that bites shiny metal asses.";
					opts.type = "success";
					break;
			}
			$.pnotify(opts);
		};
		function show_stack_bottomright(type) {
			var opts = {
				title: "Over Here",
				text: "Check me out. I'm in a different stack.",
				addclass: "stack-bottomright",
				stack: stack_bottomright
			};
			switch (type) {
				case 'error':
					opts.title = "Oh No";
					opts.text = "Watch out for that water tower!";
					opts.type = "error";
					break;
				case 'info':
					opts.title = "Breaking News";
					opts.text = "Have you met Ted?";
					opts.type = "info";
					break;
				case 'success':
					opts.title = "Good News Everyone";
					opts.text = "I've invented a device that bites shiny metal asses.";
					opts.type = "success";
					break;
			}
			$.pnotify(opts);
		};
		function show_stack_bar_top(type) {
			var opts = {
				title: "Over Here",
				text: "Check me out. I'm in a different stack.",
				addclass: "stack-bar-top",
				cornerclass: "",
				width: "100%",
				stack: stack_bar_top
			};
			switch (type) {
				case 'error':
					opts.title = "Oh No";
					opts.text = "Watch out for that water tower!";
					opts.type = "error";
					break;
				case 'info':
					opts.title = "Breaking News";
					opts.text = "Have you met Ted?";
					opts.type = "info";
					break;
				case 'success':
					opts.title = "Good News Everyone";
					opts.text = "I've invented a device that bites shiny metal asses.";
					opts.type = "success";
					break;
			}
			$.pnotify(opts);
		};
		function show_stack_bar_bottom(type) {
			var opts = {
				title: "Over Here",
				text: "Check me out. I'm in a different stack.",
				addclass: "stack-bar-bottom",
				cornerclass: "",
				width: "70%",
				stack: stack_bar_bottom
			};
			switch (type) {
				case 'error':
					opts.title = "Oh No";
					opts.text = "Watch out for that water tower!";
					opts.type = "error";
					break;
				case 'info':
					opts.title = "Breaking News";
					opts.text = "Have you met Ted?";
					opts.type = "info";
					break;
				case 'success':
					opts.title = "Good News Everyone";
					opts.text = "I've invented a device that bites shiny metal asses.";
					opts.type = "success";
					break;
			}
			$.pnotify(opts);
		};
		 });