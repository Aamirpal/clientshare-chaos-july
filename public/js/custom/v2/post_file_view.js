var more_post_files = true;
var post_file_filter_call = null;
var post_file_filters = {
    'order_by':'created_at', 
    'offset':0, 
    'order':'desc',
    'post_subject':'',
    'file_type':[]
};

function cleanPostFileTable(){
    $('i.fa').removeClass('active')
    post_file_filters['offset'] = 0;
    $('.post_file_view_body').empty();
    more_post_files = true;
}

function resetPostFileFilter(){
    post_file_filters = {
        'order_by':'created_at', 
        'offset':0, 
        'order':'desc',
        'post_subject':'',
        'file_type':[]
    };
    form = $('.post-file-form');
    form[0].reset();
    form.find('.post-file-type.active').removeClass('active');
    $('.post-added-select').multiselect('rebuild');
    $('.post-category-select').multiselect('rebuild');
    cleanPostFileTable();
    postFiles();
}

function spaceUserAndCategories(){
    $.ajax({
        type: "GET",
        url: baseurl + '/space-categories/'+current_space_id,
        beforeSend: function () {
        },
        success: function(response){
            setPostCategories(response.data.space_categories);
        }
    });

    $.ajax({
        type: "GET",
        url: baseurl + '/get-space-users/'+current_space_id,
        beforeSend: function () {
        },
        success: function(response){
            setPostUsers(response.data);
        }
    });
}

function setPostCategories(categories){
    $.each(categories, function(id, category){
        category_html = '<option value="'+id+'">'+category.category_name+'</option>';
        $('.post-category-select').append(category_html);
    });
    $('.post-category-select').multiselect('rebuild');
}
function setPostUsers(space_users){
    $.each(space_users, function(id, space_user){
        space_user_html = '<option value="'+id+'">'+space_user.user.fullname+'</option>';
        $('.post-added-select').append(space_user_html);
    });
    $('.post-added-select').multiselect('rebuild');
}

function postFilesData(){
    post_file_filters['filters'] = $('.post-file-form').serialize();
	post_file_filter_call = $.ajax({
        type: "POST",
        data: post_file_filters,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: baseurl + '/files-data?space_id='+current_space_id,
        beforeSend: function () {
            if (post_file_filter_call != null) {
                post_file_filter_call.abort();
                post_file_filter_call = null;
            }
            $('.main_content_loader').show();
            $('.no-result-div').hide()
        },
        success: function(response){
        	if(!response||!(response.files_data.length)) more_post_files = false;
        	showResult(response.files_data);
        	post_file_filters['offset'] = response.offset;
            order_arrow = post_file_filters['order']=='desc'?'fa-long-arrow-down':'fa-long-arrow-up';
            $('th[data-column='+post_file_filters['order_by']+']').find('i.'+order_arrow).addClass('active');
        },
        complete: function(){
            $('.main_content_loader').hide();
        }
    });
}

function postFiles(){
    postFilesData();
}

function showResult(post_files){
    post_files.forEach(function(element){
        element.file_url = encodeURI(element.file_url.replace(/"/g, ""));
        params = element.file_url + "', '" + element.file_extension.toLowerCase() + "', '"+element.file_name+'.'+element.file_extension+"', '" + element.post.id +"', '" + element.file_size;

        anchor = element.file_extension.toUpperCase() == 'URL'? '<a target="_blank" href="'+element.file_name+'">'+limitText(element.file_name, 50)+'</a>':
        '<a onclick = "viewPostAttachment(' + "'" + params +"'" +' )" href="#!">'+limitText(element.file_name, 50)+'</a>';
        category_name = (typeof(element.post.category_name) != "undefined" && element.post.category_name !== null) ? element.post.category_name.name : ''
        post_file_row = '<tr class="table-row">'+
            '<td class="table-cell file-name">'+anchor+'</td>'+
            '<td class="table-cell file-type"> <span class="file-type-'+element.file_extension.toLowerCase()+'">'+element.file_extension.toUpperCase()+'</span></td>'+
            '<td class="table-cell category-name">'+category_name+'</td>'+
            '<td class="table-cell created-date">'+stringDateFormat(element.created_at, 'DD-MMM-YYYY')+'</td>'+
            '<td class="table-cell post-subject"><a target="_blank" href="'+baseurl+'/clientshare/'+current_space_id+'/'+element.post_id+'">'+limitText(element.post.post_subject, 50)+'</a></td>'+
            '<td class="table-cell added-by-name">'+element.post.user.fullname+'</td>'+
        '</tr>';
        $('.post_file_view_body').append(post_file_row);
    });
    afterResultDisplay();
}

function afterResultDisplay(){
    !$('.post_file_view_body').find('tr').length ? $('.no-result-div').show():$('.no-result-div').hide();
}

function postFileTypeFilter(file_type){
    file_type = $(file_type).html().toLowerCase();
    file_type_index = post_file_filters['file_type'].indexOf(file_type);
    if(file_type_index>=0) post_file_filters['file_type'].splice(file_type_index, 1);
    else post_file_filters['file_type'].push(file_type);
    postFiles();
}

$(window).bind('scroll', function() {
    if($(window).scrollTop() >= $('.post_file_view_body').offset().top + $('.post_file_view_body').outerHeight() - window.innerHeight && more_post_files === true){
    	postFiles();
    }
});

function enableMultiselect(element, placeholder){
    element.multiselect({
        numberDisplayed: 1,
        includeSelectAllOption: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        nonSelectedText: placeholder,
        onChange: function(option, checked, select) {
            cleanPostFileTable();
            postFiles();
        }
    });
}

function pageReady(){
    $('.lazy-loading').hide();
    $('.filter_section').removeClass('hidden');
}

$(document).ready(function() {
    postFiles();
    spaceUserAndCategories();
    pageReady();

    enableMultiselect($('.post-category-select'), 'Any');
    enableMultiselect($('.post-added-select'), 'Everyone');

    $('.result-ordering').on('click', function(){
        cleanPostFileTable();
        post_file_filters['order_by'] = $(this).attr('data-column');
        if(post_file_filters['order'] == 'desc') post_file_filters['order'] = 'asc'; else post_file_filters['order'] = 'desc';
        postFiles();
    });

    $('.post-file-form input').on('keyup', function(){
        cleanPostFileTable();
        postFiles();
    });

    $('.post-file-type').on('click', function(){
        cleanPostFileTable();
        var upper = $(this);
        postFileTypeFilter(this);
        $(this).toggleClass('active');
        let i = 0;
        $('.post-file-type').each(function(){
            if(!$(this).hasClass('active')) {
                $(this).addClass('gray')
                i += 1;
            }else{
                $(this).removeClass('gray')
            }
        });
        if(i === 5){
            $('.post-file-type').each(function(){
                $(this).removeClass('gray')
            });
        }
    });

    $('.post-file-date-filter').daterangepicker({
        autoUpdateInput: false,
        maxDate: new Date()
    });

    $('.post-file-date-filter').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
        cleanPostFileTable();
        postFiles();
    });

    $('.post-file-date-filter').on('cancel.daterangepicker', function(ev, picker) {
        $('.post-file-date-filter').val('');
        cleanPostFileTable();
        postFiles();
    });

    $('.reset-post-file-filter').on('click', function(){
        resetPostFileFilter();
    });
});