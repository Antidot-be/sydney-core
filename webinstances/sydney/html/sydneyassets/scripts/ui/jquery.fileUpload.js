/**
 * Group: jquery plugins
 */
(function($){
	/**
	 * main div containing all the file actions stuffs
	 * @type string
	 * @memberOf $.fn.fileUpload
	 */
	var mainDiv;
	/**
	 *	Message box in the main div
	 * @type string
	 * @memberOf $.fn.fileUpload
	 */
	var upldMsgBox;
	/**
	 * Heavy content in Mo
	 * @type int
	 * @memberOf $.fn.fileUpload
	 */
	var heavylength = 5;
	/**
	 *	Max file size in Mo
	 * @type int
	 * @memberOf $.fn.fileUpload
	 */
	var maxlength = 200;
	/**
	 *	contains the state of the Gears installation
	 * @type boolean
	 * @memberOf $.fn.fileUpload
	 */
	var gearsInstalled = false;
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */
	var upldStatus;
	/**
	 * @memberOf $.fn.fileUpload
	 */
	var fileSubmit;
	/**
	 * @memberOf $.fn.fileUpload
	 * @type array
	 */
	var filelist=[];
	/**
	 * Array of categories selected from the foldermanager, all files uploaded will be assignes these
	 * @memberOf $.fn.fileUpload
	 */
	var categoriesIds=[];
	/**
	 * @type int
	 * @memberOf $.fn.fileUpload
	 */
	var currentfile = 0;
	/**
	 * @type object
	 * @memberOf $.fn.fileUpload
	 */
	var msgs = { 	'gearsnot' : 'Gears is not installed, please go to <a href="http://gears.google.com/?action=install" targer="_blank">http://gears.google.com/</a> to install it.',
					'gearsis' : 'Gears is installed on your machine. You can upload multiple files browsing your computer. Click on shift or ctrl key to select multiple files.'
	};
	/**
	 * @type string
	 * @memberOf $.fn.fileUpload
	 */
	var uploadURL = '/adminfiles/services/uploadfile/';
	/**
	 * Object litteral containing the file types. It gets them by a request to the PHP var Sydney_Medias_Utils::$ftypes
	 * @type object
	 * @memberOf $.fn.fileUpload
	 */
	var extCor ={};
	/**
	 * 'numberOfFiles':0 => unlimited
	 * @memberOf $.fn.fileUpload
	 * @type object
	 */
	var params = {'numberOfFiles':0};
	/**
	 * Indicate if files already existing on server
	 * @memberOf $.fn.fileUpload
	 * @type boolean
	 */
	var hasExistingFiles = false;
	/**
	 * if already existing on server, choice for this files
	 * @memberOf $.fn.fileUpload
	 * @type string
	 */
	var actionForExistingFiles = ''; // allowed value:  '' (empty) / replace / rename / skip
	/**
	 * dialogbox
	 * @memberOf $.fn.fileUpload
	 */
	var jDialog 		= null;
	/**
	 * @type string
	 * @memberOf $.fn.fileUpload
	 */
	var jDialogRemember = ''; 
	/**
	 * Method: getType
	 * Returns the type of a file based on the extension
	 * @memberOf $.fn.fileUpload
	 * @param string fname Filename
	 * @returns string
	 */
	function getType( fname )
	{
		var reg = new RegExp("[.](.{2,5})$","g");
		if ( ext = reg.exec(fname)) return ext[1].toUpperCase();
		else return '';
	};
	/**
	 * Returns true or false if the extension is known
	 * @memberOf $.fn.fileUpload
	 * @param string ext - {String} ext Extention
	 * @returns Boolean
	 */
	function getExtType( ext )
	{
		var toret = ['Unknown', false];
		if (ext != null && extCor[ext]) toret = extCor[ext];
		return toret;
	};
	/**
	 * Returns the lenght from bytes to a human readable format
	 * @param ln - {Int} ln
	 * @memberOf $.fn.fileUpload
	 */
	function getHumanLength( ln )
	{
		var cln = (ln/1024);
		var hln = cln.toFixed(2) + ' Ko';
		return hln;
	};
	/**
	 * Upload a chunk of data of an element in the list
	 * @memberOf $.fn.fileUpload
	 * @param elid - {int} elid Id of the element in the list
	 * @param bfrom - {int} bfrom Byte number we should start from
	 */
	function upgearsDoit( elid, bfrom )
	{
		$.log(elid+':'+bfrom);
		var blength = (8*1024);
		var btotall = filelist[elid].blob.length;
		if ( (bfrom+blength) > btotall) blength=btotall-bfrom;
		
		var cdi = '';
		for (var i=0; i < categoriesIds.length; i++) cdi += categoriesIds[i]+',';
		
		var bchunk = filelist[elid].blob.slice(bfrom, blength);
		var request = google.gears.factory.create('beta.httprequest');
		request.open('POST', uploadURL + 'calledBy/' + params.calledBy + '/peopleId/' + params.peopleId + '/catids/'+cdi);
		request.setRequestHeader('Content-Disposition', 'attachment; filename="' + filelist[elid].name + '"');
		request.setRequestHeader('Content-Type', 'application/octet-stream');
		request.setRequestHeader('Content-Range', 'bytes ' + bfrom + '-' + blength + '/' + btotall);
		request.onreadystatechange = function(){
			if (request.readyState == 4) {
				// $('#percupl' + elid).html(request.responseText);
				$('#percupl' + elid).css('width',request.responseText);
				if ($.trim(request.responseText) == '100%') {
					$('#percupl' + elid).css('background-color', 'green');
					$('#percupl' + elid).css('border-right', 'none');
					
					if (params.calledBy == "adminpeople") {						
						$.ajax({
						      url: "/adminfiles/services/searchfile/format/json",
						      global: false,
						      type: "POST",
						      data: ({'filename':filelist[elid].name}),
						      dataType: "json",
						      async:true,
						      success: function(jsond,r){
									if (r == 'success') {
										if (jsond.file > 0) {
											$('#avatar').attr('src','/adminfiles/file/thumb/id/'+ jsond.file +'/ts/1/fn/' + jsond.file + '.png');											
											$('#avatar-remove').fadeIn();
											$('#box-upload').fadeOut();											
											
											var ico_add = $('#avatar-add img').attr('src');
											var ico_swap = ico_add.replace('icon_add.png','icon_swap.png');
											$('#avatar-add img').attr('src',ico_swap);
											
										}
									}
								}
						   }
						);						
					}
					
				}
				if (bfrom < btotall) upgearsDoit(elid, (bfrom+blength) );
			}
		};
		request.send(bchunk);
	};
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */
	function renameFile(fname) {
		var newDate = new Date;
		return fname.replace(new RegExp("[.](.{2,4})$","g"),'.'+newDate.getTime()+'.$1');		
	};
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */
	function skipFile(filekey) {
		$('#percupl' + filekey).css('width', '100%');
		$('#percupl' + filekey).css('background-color', 'red');
		$('#percupl' + filekey).css('border-right', 'none');
	};
	
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */	
	function _dialogbox(filename,filekey,funcrename,funcreplace,funcskip,event,optionRemember) {
		var filename 	= filename;
		var filekey 	= filekey;
		var funcrename 	= funcrename;
		var funcreplace = funcreplace;
		var funcskip 	= funcskip;
		var event 		= event;
		
		var sOptionRemember = optionRemember?'<p><input id="jDialogRemember" type="checkbox"> <label for="jDialogRemember">Remember decision</label></p>':'';
		
		jDialog = $('<div></div>')
		.html('<p>The file "' + filename + '" already exist on library. Please choose an action...</p>' + sOptionRemember)
		.dialog({
			autoOpen: false,
			title: 'File already existing !',
			buttons: { 
					"Rename": function() {
						funcrename.call(this,event,filename,filekey);
					},
					"Replace": function() {
						funcreplace.call(this,event,filename,filekey);
					},
					"Skip": function() {
						funcskip.call(this,event,filename,filekey);						
					}
			},
			modal: true,
			resizable: false
		});
		jDialog.dialog('open');
	}
	
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */	
	function gearsRename(event,filename,filekey) {
		filelist[filekey].name = renameFile(filename);
		upgearsDoit(filekey,0);
		
		if ($('#jDialogRemember',this).is(':checked')) {
			jDialogRemember = 'rename';
		} else {
			jDialogRemember = '';
		}
		
		currentfile = filekey + 1;
		uploadGearsData(event);							
		$(this).dialog("close");
	} 
	
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */	
	function gearsReplace(event,filename,filekey) {
		upgearsDoit(filekey,0);
		
		if ($('#jDialogRemember',this).is(':checked')) {
			jDialogRemember = 'replace';
		} else {
			jDialogRemember = '';
		}
		
		currentfile = filekey + 1;
		uploadGearsData(event);
		
		$(this).dialog("close");
	}
	
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */	
	function gearsSkip(event,filename,filekey) {
		skipFile(filekey);
		
		if ($('#jDialogRemember',this).is(':checked')) {
			jDialogRemember = 'skip';
		} else {
			jDialogRemember = '';
		}
		
		currentfile = filekey + 1;
		uploadGearsData(event);
		
		$(this).dialog("close");
	}
	
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */	
	function stdRename(event,filename,filekey) {
		$('#fileupload-new-filename').val(renameFile(filename));
		$('.fileupload-listform').submit();
		$(this).dialog("close");
	} 
	
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */	
	function stdReplace(event,filename,filekey) {
		$('.fileupload-listform').submit();
		$(this).dialog("close");
	}
	
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 */	
	function stdSkip(event,filename,filekey) {
		$(this).dialog("close");
	}		
	
	function dialogbox(event, filekey) {		
		_dialogbox(filelist[filekey].name,filekey,gearsRename,gearsReplace,gearsSkip,event,true);
	};
	/**
	 * Upload all the files with the gears system in chunk
	 * @memberOf $.fn.fileUpload
	 * @param event - {Object} event
	 */
	function uploadGearsData( event ) {		
		event.preventDefault();
		
		fileSubmit.hide();
		for (var i = currentfile; i < filelist.length; i++) {
			if (filelist[i].tobeuploaded) {
				if (filelist[i].alreadyExisting) {
					
					switch (jDialogRemember) {
						case 'rename' :
							filelist[i].name = renameFile(filelist[i].name);
							upgearsDoit(i, 0);
							break;
						case 'replace' :
							upgearsDoit(i, 0);
							break;
						case 'skip' :
							skipFile(i);
							break;
						default:
							dialogbox(event, i);
							return false;
							break;
					}
						
				} else {
					upgearsDoit(i, 0);
				}				
			} else {
				skipFile(i);
			}
			currentfile = i;
		}
	};
	/**
	 * Checks if the file exist and change style according to that.
	 * @memberOf $.fn.fileUpload
	 */
	function isFileExists(filename) {
		var returnvalue = false;
		$.ajax({
		      url: "/adminfiles/services/searchfile/format/json",
		      global: false,
		      type: "POST",
		      data: ({'filename':filename}),
		      dataType: "json",
		      async:false,
		      success: function(jsond,r){
					if (r == 'success') {
						if (jsond.file > 0) {
							returnvalue = jsond.file;
						}
					}
				}
		   }
		);
		return returnvalue;
	}
	function checkFileExists(fl, heavyClassForName, hasExistingFiles) {
		var fileid = isFileExists(fl.name);		
		if (fileid) {
			//clsn				= 'deadcol';
			heavyClassForName 	= ' heavyfile';
			//fl.tobeuploaded 	= false;
			fl.name 			= fl.name;// + " (!!! already exist !!!)";
			fl.id				= fileid;
			hasExistingFiles	= true;
			fl.alreadyExisting 	= true;			
		}
		
	};
	/**
	 * Action executed when the button save folders is pressed.
	 * @memberOf $.fn.fileUpload
	 */
	function onSaveFolders(e)
	{
		categoriesIds = e;
		$('#linkfoldb').html(e.length+' categorie(s) selected.');
	};
	/**
	 * Add the click event on the folder button
	 * @memberOf $.fn.fileUpload
	 */
	function addEventLinkToDb() {
		$('#linkfoldb').click(function(e){
			e.preventDefault();
			var sel= [];
			$('#folderreldiv').foldermanager({
				'title': 'Categories',
				'cortable': 'filfolders_filfiles',
				'labeltable': 'filfolders',
				'datatable': 'filfiles',
				'dialog': true ,
				'onSaveMCallback': onSaveFolders,
				'selectedItems': sel,
				'closeOnEscape':true,
				'fileid': '',
				'autoSaveOnSelect': false
			});
		});
	};
	/**
	 * Gears files upload managent
	 * @memberOf $.fn.fileUpload
	 * @param files - {Object} files
	 */
	function gearsFiles(files)
	{
		filelist = files;
		
		if (filelist.length <= 0) {
			return false;
		}
		fileSubmit.show();		
		//$('.fileupload-listform').hide();
		
		var htmlmsg='';
		var cntFilesToBeUploaded = 0;		
		var totalWeight=0;
		htmlmsg += 'You picked the following files.<br>Press the <b>upload</b> button to upload the files or <b>pick</b> some new files to replace this list.';
		htmlmsg += '<table class="ui-table" style="background: #EEE;">';
		htmlmsg += '<thead><tr><th scope="col">Name</th><th scope="col">Type</th><th scope="col">Size</th><th scope="col">Loaded</th></tr></thead><tbody>';
		for (var i = 0; i < filelist.length; i++) {
			var clsn 					= '';
			var hvcls 					= '';
			var heavyClassForName 		= '';
			filelist[i].id				= 0;
			filelist[i].type 			= getType(filelist[i].name);
			filelist[i].typedata 		= getExtType(filelist[i].type);
			filelist[i].tobeuploaded 	= filelist[i].typedata[2];
			filelist[i].humanlength 	= getHumanLength(filelist[i].blob.length);
			filelist[i].isHeavy 		= false;
			filelist[i].alreadyExisting = false;
			if (filelist[i].blob.length > (maxlength * 1024 * 1024)) { filelist[i].isHeavy = true; hvcls =' heavyfile'; }
			checkFileExists(filelist[i], heavyClassForName, hasExistingFiles);
			if (!filelist[i].tobeuploaded || (params.numberOfFiles > 0 && cntFilesToBeUploaded >= params.numberOfFiles)) { 
				filelist[i].tobeuploaded = false; 
				clsn='deadcol';
			} else {
				cntFilesToBeUploaded++;
				totalWeight += filelist[i].blob.length;
			}
			htmlmsg += '<tr>';
			htmlmsg += '<td class="'+clsn+' '+heavyClassForName+'">'+filelist[i].name+'</td>';
			htmlmsg += '<td class="'+clsn+'">'+filelist[i].typedata[1]+'</td>';
			htmlmsg += '<td class="'+clsn+hvcls+'">'+filelist[i].humanlength+'</td>';
			htmlmsg += '<div class="fillbar"><div id="percupl'+(i)+'" style="width: 0%;"></div></div>';
			htmlmsg += '</tr>';
		}
		htmlmsg += '<tr>';
		htmlmsg += '<td style="background: #EEE;"></td>';
		htmlmsg += '<td style="background: #EEE; font-weight: bold;">TOTAL weight</td>';
		htmlmsg += '<td style="background: #EEE; font-weight: bold;">'+getHumanLength(totalWeight)+'</td>';
		htmlmsg += '</tr>';
		htmlmsg += '</tbody></table>';
		htmlmsg += '<br><div id="linkfoldb" title="Link to folders" class="agdbutlinkfolders" style="height:20px; align:left; background-repeat: no-repeat; padding-left:30px;">Link to categories</div>';
		htmlmsg += '<br><i style="color:red; font-size: .8em;">ATTENTION: The files highlighted in grey (if any) will not be uploaded.</i><br><br>';
		upldStatus.html(htmlmsg);
		addEventLinkToDb();
		fileSubmit.removeClass('muted');
		fileSubmit.unbind('click');
		fileSubmit.click( uploadGearsData );
		currentfile = 0;jDialogRemember = '';
	};
	/**
	 * 
	 * @memberOf $.fn.fileUpload
	 * @param event - {event}
	 */
	function gearsBrowse(event)
	{
		event.preventDefault();
	  	var desktop = google.gears.factory.create('beta.desktop');
	  	desktop.openFiles(gearsFiles);
	    // { filter: ['text/plain', '.png'] }
	};
	/**
	 * Checks if gears is installed
	 * @memberOf $.fn.fileUpload
	 */
	function checkGears()
	{
		if (!window.google || !google.gears) {
			upldMsgBox.html(msgs.gearsnot);
			$('#upldfieldgears').remove();
			$('#upldfieldnogears').show();
			fileSubmit.show();
			fileSubmit.removeClass('muted');
			fileSubmit.click( function(event) {
				if(!gearsInstalled) {
					var filename = $("input:file").val();
					//$("input:file").val()
					var filetypedata 		= getExtType(getType(filename));
					var filetobeuploaded 	= filetypedata[2];
					
					if (filetobeuploaded) {
						if (isFileExists(filename)) {
							_dialogbox($("input:file").val(),0,stdRename,stdReplace,stdSkip,event,false);
						} else {
							//alert('submit');
							$('.fileupload-listform').submit();						
						}
					} else {
						alert('Invalid extension files!');
					}
					// return false to stop click event
					return false;
				}
			});
		} else {
			gearsInstalled = true;
			upldMsgBox.html(msgs.gearsis);
			$('#upldfieldnogears').remove();
			$('#upldfieldgears').show();
			$('#upldfieldcls').hide();
			$('#browseButton').click(gearsBrowse);
		}
		
		upldMsgBox.append('<br />');
		var separator	= '';
		var strExt 		= '<p style="font-size:120%;">Valid extensions: ';
		for (property in extCor) {
			if (property != 'unknown') {
				strExt = strExt + separator + property.toUpperCase();
				separator = ', ';
			}
			//output += property + ': ' + object[property]+'; ';
		}
		strExt = strExt + '</p>';
		upldMsgBox.append(strExt);
	};
	/**
	 * Main initialization
	 * @memberOf $.fn.fileUpload
	 */
	function thisinit()
	{
		upldMsgBox = $('#upldMsgBox');
		upldStatus = $('#upldStatus');
		fileSubmit = $('#fileSubmit');
		checkGears();
	};
	/**
	 * jQuery plugin for the file uploader
	 * @constructor
	 */
	$.fn.fileUpload = function(p)
	{
		// extends params
		$.extend(params,p);
		// gets the filetypes list from PHP Sydney_Medias_Utils::$ftypes
		$.getJSON('/adminfiles/services/getftypes/format/json', params, function(jsond,r){
			if (r == 'success') {
				var u='Here:\n';
				extCor = jsond.ftypes;
			} else {
				alert('Error while geting file types :'+r);
			}
			thisinit();
		});
		mainDiv = $(this);		
		return $(this);
	};
})(jQuery);
