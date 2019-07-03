var temperary_element = '';
$(document).ready(function(){
$('.more-categories a.btn').hide();
var categories_height = $('.categories-wrap ul').height();
var hidden_element = new Array();
    $(".categories-wrap ul").find("li").each(function(e){
        if ($(this).position().top > categories_height){
            if(temperary_element == ''){
              temperary_element = e;  
            }
            hidden_element.push($(this));
        }
    });
    if(temperary_element){
      var width = $(window).width();
      var number = (width < 767) ? 2 : 3;
      var index_element = temperary_element-parseInt(number);
      $('.categories-wrap ul li:gt('+index_element+')').addClass('hide-category').hide();
    }

$('.categories-wrap ul').css("overflow", "inherit");
if(hidden_element.length > 0){
  $('.more-categories a.more').show();
  $('.more-categories a.more span').text(hidden_element.length+parseInt(2));
}

$(".categories-wrap ul li:hidden").find(".category-selected").each(function(e){
      $('.more-categories a.more').hide();
      $('.categories-wrap ul').css({'height':'auto','max-height':'inherit'});
      $('.categories-wrap ul li:hidden').show();
});

$(document).on('click','.more-categories a.more',function(){
      $(this).hide();
      $('.categories-wrap ul').css({'height':'auto','max-height':'inherit'});
      $('.more-categories .less').show();
      $('.categories-wrap ul li.hide-category').addClass('show-category').removeClass('hide-category').show();
});

$(document).on('click','.more-categories a.less',function(){
      $(this).hide();
      var width = $(window).width();
      var height = '60px';
      if(width<767){
        $('.categories-wrap ul').css('max-height',height);
      }else{
        $('.categories-wrap ul').css('height',height);
      }
      $('.more-categories .more').show();
      $('.categories-wrap ul li.show-category').hide().addClass('hide-category').removeClass('show-category');
});

$('.executive_show_more').hide();
$(document).on('click','.executive_show_less a',function(){
      $('.executive_show_less').hide();
      $('.executive_show_more').show();
      $('.executive_col_tile').css('overflow','auto');
});

$(document).on('click','.executive_show_more a',function(){
      $('.executive_show_less').show();
      $('.executive_show_more').hide();
      $('.executive_col_tile').css('overflow','unset');
});

if(current_space_id && file_view_count) {
    $.ajax({
         type: 'get',
         url: file_view_count+"?space_id="+current_space_id+"&order_by=created_at&offset=0&order=desc&post_subject=",
         success: function (response) {
           $('.file-upload-col .file-count').text(response.count);
         },
         error: function (xhr, status, error) {
           logErrorOnPage(xhr, status, error, 'executive_show_more');
         }
    });
}

$.ajax({
     type: 'get',
     url: get_quick_links+"?space_id="+current_space_id,
     success: function (response) {
       if(response.result){ 
           addQuickLinks(response);
       }
        return false;
     },
     error: function (xhr, status, error) {
      logErrorOnPage(xhr, status, error, 'get_quick_links');
     }
});

$(document).on('submit','#edit_post_category_form',function(e){
       error = false;
       $(this).find('input.category_value').each(function () {
           if ($(this).val().trim()== '') {
             $(this).removeClass('error');
             $(this).parent().find('p').remove();
             $(this).after('<p class="error-msg error-body text-left" style="text-align: left;">This field is mandatory</p>');
             $(this).addClass('error');
             error = true;
           }
        }); 
       if(error)
          return false;

       e.preventDefault();
       $('.btn-quick-links').attr('disabled',true);
       $('.category_error').text('');
       var form =$(this);
       var form_data = new FormData(form[0]);
       
       $.ajax({
              type: 'post',
              url: baseurl+'/save_editcategory_ajax',
              data:  form_data,
              async: true,
              success: function (response) {
                 $('.btn-quick-links').attr('disabled',false);
                 if(response.result){ 
                    location.reload();
                 }else{
                    $('.category_error').text('Duplicate category exist.');
                 }
              },
              error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'save_editcategory_ajax');
              },
              cache: false,
              contentType: false,
              processData: false
            });
        return false;
});

$(document).on('submit','#quick_links_form',function(e){
       e.preventDefault();
       var form =$(this);
       var form_data = new FormData(form[0]);
       
       $('.executive-link .executive-link-col').html('');
       $.ajax({
              type: 'post',
              url: save_quick_links+"?space_id="+current_space_id,
              data:  form_data,
              async: true,
              success: function (response) {
                 $('.btn-quick-links-button').attr('disabled',false);
                 if(response.result){ 
                    $('#quick_links_model').modal('hide');
                    current_step = form.closest('.welcome-cs-popup');
                    updateTourStep(current_step.attr('data-step'));
                    current_step.addClass('hidden');
                    current_step.next($('.welcome-cs-popup')).removeClass('hidden');
                    addQuickLinks(response);
                    addProgressBarsection();
                 }
              },
              error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'executive-link');
              },
              cache: false,
              contentType: false,
              processData: false
            });
        return false;
});

});
$('.executive_show_more').hide();

function addQuickLinks(response){
    var html = '';
    $('.hyperlink,.link_name').val('');
    var quick_links_length = response.quick_links.length;
    for(var i = 0;i<quick_links_length;i++){
        var quick_links = response.quick_links[i];
        var url = quick_links.link_url;
        var link_name = quick_links.link_name;
        if (url && !url.match(/^http([s]?):\/\/.*/))
          url = 'http://'+url;

        html +='<a class="full-width" href="'+url+'" target="_blank">'+link_name+'</a>';
        $('.hyperlink_'+i).val(url);
        $('.link_name_'+i).val(link_name);
    }

    if(html == '' && response.quick_links.length == 0){
      html = '<a class="full-width" href="http://myclientshare.com/" target="_blank">Client Share</a><a class="full-width" href="http://myclientshare.com/quick-links/" target="_blank">What are Quick Links?</a> ';
    }
    
    $('.executive-link .executive-link-col').append(html);
}

function sendQuickLinks(btn){ 
     var link_count = 0;
     var reference = $(btn).closest('.quick_links_form');
     var pattern = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
     var quick_links_error = reference.find('.quick_links_error');
     quick_links_error.text('');
     var hyperlink = reference.find('.hyperlink[name="hyperlink[]"]');
     var link_name = reference.find('.link_name[name="link_name[]"]');
     for (i=0; i<hyperlink.length; i++){
            if(hyperlink[i].value.trim() != '' && link_name[i].value.trim() != ''){
                   link_count++;
            }
            if(hyperlink[i].value.trim() != '' && link_name[i].value.trim() == ''){
                   quick_links_error.text('Please enter link name.');
                   return false;
            }
            if(hyperlink[i].value.trim() == '' && link_name[i].value.trim() != ''){
                   quick_links_error.text('Please add hyperlink.');
                   return false;
            }
            if(hyperlink[i].value.trim() != '' && !pattern.test(hyperlink[i].value.trim())){
                   quick_links_error.text('Please add correct hyperlink.');
                   return false;
            }
     }
     if(link_count >= 2){ 
        var twitter_handle_last = $('#twitter_handle_2').val();
        if((typeof twitter_handle_last != 'undefined' && twitter_handle_last.length < 2) || twitter_handle_last == '')
          $('#twitter_handle_2').closest('.twitter-input-col').find('.remove-handle').trigger('click');

        reference.find('.btn-quick-links-button').attr('disabled',true);
        reference.submit(); 
     }else{
        quick_links_error.text('You need to add a minimum of 2 quick links to your client share.');
        return false;
     }
}
