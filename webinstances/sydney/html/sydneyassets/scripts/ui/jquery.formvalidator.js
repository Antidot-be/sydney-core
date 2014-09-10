/**
 * jQuery plugin for form validation within Sydney and Zend_Forms
 */
(function($){

	var hasChange = false;

	/**
	 * jQuery pluging for managing the validation for a form in an Ajax way.
	 * I need to put more info here please ask me some (I'm Arnaud BTW)
	 * <code>
	 * 	$('#agdevents').formvalidator({
	 * 		'url': '/adminagenda/index/processedit/format/json/',
	 * 		'callbackfunc': function(e){ alert('OK man!'); }
	 * 		'ajaxboxid':'#ajaxbox',
	 * 		'addtopost': {},
	 * 		'buttonloc':'input[type=submit]',
	 * 		'btncontext': null,
	 * 		'hidesubmit': false,
	 * 		'checkforchanges' : true
	 * 	});
	 * </code>
	 * @constructor
	 */
	$.fn.formvalidator = function( prefs )
	{
		prefso = prefs;

		$(':input').change(function(e){
			if (!hasChange) {
				hasChange = true;
				//$('#submitbutton').fadeIn();
			}
		});

		//$('#submitbutton').fadeOut();

		return $(this).each(function(){
			var opts = {};
			var theform;
			var optsdefault = {
					//'url': '',
					'ajaxboxid':'#ajaxbox',
					'addtopost': {},
					'callbackfunc': function(){},
					'buttonloc':'input[type=submit]',
					'btncontext': null,
					'hidesubmit': false,
					'checkforchanges' : true
				};
			opts = $.extend(optsdefault, prefso);
			if (opts.btncontext == null) opts.btncontext = $(this);
			// puts the click event on the submit button
			$(opts.buttonloc, opts.btncontext).click( function(e){
				$(this).formvalidator.onClickSubmit(e, opts);
			} );
			if(opts.hidesubmit) $(opts.buttonloc, opts.btncontext).hide();
		});
	};
	/**
	 *
	 */
	$.fn.formvalidator.onClickSubmit = function(e, opts)
	{
		e.preventDefault();

		if (!hasChange && opts.checkforchanges) {
			$(opts.ajaxboxid).msgbox( {'message': 'Please change at least one value !', 'showtime':5,'modal':false} );
		} else {
			hasChange = false;
			//$('#submitbutton').fadeOut();
			$(opts.ajaxboxid).msgbox( {'message': 'Loading...', 'showtime':0,'modal':true} );
			//var dt = opts.addtopost = {};
			var dt = opts.addtopost;
			// gets the data from the form
			$('input,textarea,select', opts.btncontext).each(function(){
				var el = $(this);
				var elna = $(this).attr('name');
				if (el.attr('type') == 'checkbox') {
					if(el.prop('checked'))
					{
						if ( elna.match(/\[\]$/) ) {
							var vna=elna.substr(0, (elna.length-2) );
							if(dt[vna] == undefined) dt[vna] = [el.val()];
							else dt[vna].push(el.val());
						} else dt[elna] = el.val();
					}
				}
				else dt[elna]=el.val();
			});
			// post it the the service and get the errors if any or run the fallback function
			$.postJSON(opts.url, dt, function(j,s){
			    if( s == 'success')
			    {
			        $('div[class=errors]', opts.btncontext).remove();
			        $('input,textarea', opts.btncontext).removeClass('errors');
			        var era = j.ResultSet.errors;
			        if (j.ResultSet.hasOwnProperty('entry')) {
				        if (j.ResultSet.entry.hasOwnProperty('id'))
				        {
							$('input[type=hidden][name=id]', opts.btncontext).val(j.ResultSet.entry.id);
						};
			        };
			    	$(opts.ajaxboxid).msgbox(j);
			        var nberrors=0;
					for( var k in era ) {
						var msgs = '';
						for (var kk in era[k] ) msgs += '<div>'+era[k][kk]+'</div>';
						$('[name='+k+']', opts.btncontext).addClass('errors').after('<div class="errors">'+msgs+'</div>');
						nberrors++;
					};
					if (nberrors == 0) {
						opts.callbackfunc(j);
					};

					// redirect to urlforward
					if (j.status && dt.hasOwnProperty('urlforwards') && dt.urlforwards.length > 0) {
						document.location = dt.urlforwards;
					}

					// auto redirect to datalist page
					if (j.status && !dt.hasOwnProperty('noredirect')) {
						//document.location = '/' + j.moduleName + '/index/' + j.actionName.substr(4,j.actionName.length-4);
					}



			    } else {
			    	$(opts.ajaxboxid).msgbox({'message':'Error in the AJAX request, try again later...','showtime':5,'modal':true});
				};
			});
		}
	};
})(jQuery);



