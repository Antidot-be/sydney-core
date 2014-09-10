/**
 * 
 */
$(document).ready(function() {
	$('#formPublicmsSearchIndex').submit(function (event) {
		event.preventDefault();
		loadSearchResult('/publicms/search/result/rest/y/?q=' + encodeURIComponent($("#q").val()));
	});
	
	if ($("#q").val().length > 0) {
		loadSearchResult('/publicms/search/result/rest/y/?q=' + encodeURIComponent($("#q").val()));
	}
	
});
/**
 * 
 * @param url
 */
function loadSearchResult(url) {
	$('#publicmsSearchIndexLoading > img').show();
	$("#publicmsSearchIndexResult").load(url, function () {
		addEventToPaginationControl();
		$('#publicmsSearchIndexLoading > img').hide();
	});
}
/**
 * 
 */
function addEventToPaginationControl() {
	$('#publicmsSearchIndexPaginationControl a').each(function(index,element) {
		$(this).click(function (event) {
			event.preventDefault();
			loadSearchResult($(this).attr('href'));
		});
	});	
}