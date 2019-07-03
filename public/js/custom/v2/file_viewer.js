function viewPostAttachment(url, url_type, file_name, post_id, file_size, file_object) {
    updateFileViews(post_id);
    viewAttachment(url, url_type, file_name, file_size, file_object);
}

function viewAttachment(url, url_type, file_name, file_size, file_object) {
    if((file_size/1024)/1024 > 10 && url_type != 'video/mp4') {
        downloadFile(url, url_type, file_name);
    } else {    	
        viewFile(url, url_type, file_name, file_size, file_object);
    }
}

function checkDevice(){
	if(isAppleDevice()){
		swal({
			title: 'Unable to download',
	        text: 'This file type is not supported by your device',
	        confirmButtonText: 'close',
	        customClass: 'simple-alert'
		});
		e.preventDefault();
	}
}

function downloadFile(url, url_type, file_name){
	checkDevice();
	signed_url = signedUrl(url, false, file_name);
	if(signed_url.cloud === ''){
       alert('This file type is being converted and may take a few seconds to appear.');
       return false;
	}

	window.location = signed_url.cloud;
}

function updateFileViews(post_id){
	$.ajax({
	    type: 'GET',
	    url: baseurl+'/view_file?post_id='+post_id,
	});
}
	
function viewFile(url, url_type, file_name, file_size, file_object) {
    if (url_type === 'url') {
        var win = window.open(url, '_blank');
        win.focus();
        return false;
    }
    signed_url = signedUrl(url, false, file_name);
    if (signed_url.cloud === '') {
        alert('This file type is being converted and may take a few seconds to appear.');
        return false;
    }
    viewer_class = getViewerClass(signed_url, url_type, file_size);
    signed_url.file_info_cloud = documentViewer(signed_url);
    openFile(viewer_class, signed_url, file_name, file_object);
}

function documentViewer(file_info){
    var file_info_cloud = file_info.file_url;
    var file_ext = file_info.file_ext.toLowerCase();
	if(['pptx', 'ppt', 'ppsx', 'pps', 'potx', 'ppsm', 'docx', 'dotx'].indexOf(file_ext)>=0) file_info_cloud = 'https://view.officeapps.live.com/op/view.aspx?src='+encodeURIComponent(file_info.cloud);
	else if(['pdf'].indexOf(file_ext)>=0) file_info_cloud = baseurl+"/pdf_viewer/web/viewer.html?file="+file_info.document_viewer;
	return file_info_cloud;
}

function getViewerClass(file_info, url_type, file_size) {
	image = ['png','gif','jpg','jpeg'];
	video = ['mp4'];
	pdf_docs = ['pdf'];
	ppt_word_docs = ['ppt','pptx', 'ppsx', 'pps', 'potx', 'ppsm', 'docx', 'dotx'];
	excel_docs = ['xlsx', 'xlsb', 'xls', 'xlsm'];
	viewer = false;
        var file_ext = file_info.file_ext.toLowerCase();

	if(image.indexOf(file_ext)>=0) viewer = 'image-viewer';
	else if(video.indexOf(file_ext)>=0 || url_type === 'video/mp4') viewer = 'video-viewer';
	else if( 
                (ppt_word_docs.indexOf(file_ext) >=0 && ((file_size/1024)/1024 < 10) )
                || (pdf_docs.indexOf(file_ext)>=0 && ((file_size/1024)/1024 < 8) )
                ) {
            viewer = 'docs-viewer';
        } else {
		checkDevice();
		viewer = null;
		window.location = file_info.cloud;
	}
	return viewer;
}

function openFile(viewer_class, signed_url, file_name, file_object){
	if(isAppleDevice()) $('a.file-download').remove();

    if(file_object){
        $('.'+viewer_class).find('.file-download').attr('media-id', file_object.id);
    }

	$('h4.modal-title').html(decodeURIComponent(file_name));
	$('.'+viewer_class).find('.file-download').attr('href', signed_url.cloud);
	$('.'+viewer_class).find('.modal-loader').show();
	$('.'+viewer_class).modal('show');
	$('.'+viewer_class).find('.file-source').attr('src', signed_url.file_info_cloud);
	if($('.'+viewer_class).find('video').length){
		$('.'+viewer_class).find('video').load();
		$('.'+viewer_class).find('video')[0].play();
		$('.'+viewer_class).find('.modal-loader').hide();
	}

}

$(document).on("click", ".full_screen_toggle", function () {
	$(this).closest('.modal-dialog').toggleClass('full-width-doc');
	if($(this).closest('.modal-dialog').hasClass('full-width-doc')) {
        $(this).find('span').text('Minimise');
		$(this).find('img').attr('src', '../images/v2-images/collapse_light_Icon.svg');
    }else{
        $(this).find('span').text('Expand');
		$(this).find('img').attr('src', '../images/v2-images/expand.svg');
    }
});