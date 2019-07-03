var max_twitter_handle_limit = 3;
function addFeedInput(){
    var add_feed_btn = $('a.add-twitter-feed.add-handle');
    var input_count = $(add_feed_btn).siblings('div.twitter-handle-wrap').find('div.twitter-handle').length;
    if(input_count >= max_twitter_handle_limit){
        $(add_feed_btn).hide(); 
        return false;
    }    
    var cloned_div = $(add_feed_btn).siblings('div.twitter-handle-wrap').children('div.twitter-handle:last-child').clone();
    var twitter_handle_input = $(cloned_div).find('input[name="twitter_handles[]"]');
    $(twitter_handle_input).val('');
    $(twitter_handle_input).attr('id', 'twitter_handle_' + (input_count - 1));
    $(cloned_div).find('span.link-input-icon p').html((input_count + 1));
    $(add_feed_btn).siblings('div.twitter-handle-wrap').append(cloned_div);
    var new_input_count = $(add_feed_btn).siblings('div.twitter-handle-wrap').find('div.twitter-handle').length;
    if(new_input_count >=3){
       $(add_feed_btn).hide(); 
    }
}

function removeFeedInput(element){
    var modal_body = $(element).closest('#manage_twitter_feed_modal .modal-body');
    var input_count = $(modal_body).find('div.twitter-handle-wrap').find('div.twitter-handle').length;

    if(input_count <= 1){
       $(element).closest('div.twitter-handle').find('input.twitter-feed-input').val('');
       $(element).closest('#manage_twitter_feed_modal .modal-footer button[type="submit"]').attr('disabled', 'disabled');
       return false;
    }
    $(element).closest('div.twitter-handle').remove();
    modal_body.find('a.add-twitter-feed.add-handle').show();
    modal_body.find('a.add-twitter-feed.add-handles').show();
    $('.tour-twitter-list').each(function(index){
        $(this).find('.link-input-icon p').html(index+1)
    });
    enableDisableSubmitBtn();
}

function validateInputs(selector){
    var validate = true;
    var twitter_handle_regex = /^@[a-zA-Z0-9_]{1,15}$/i;
    var twitter_handle_input = $('.'+selector+' .modal-body .twitter-handle-wrap').find('input[name="twitter_handles[]"]');
    
    $(twitter_handle_input).each(function(){
        $(this).closest('div.twitter-handle').find('span.error').remove();
        var handle_value = $(this).val();

        handle_value = handle_value.trim();
        if((handle_value != '@' && selector != 'welcome_twitter_feed_modal') || 
            (selector == 'welcome_twitter_feed_modal' && handle_value != '' && handle_value != '@')){
            if(handle_value.indexOf('@') == -1) 
                handle_value = '@'+handle_value;
        
            if(twitter_handle_input.length > 1){
                if(handle_value == '' || typeof handle_value == 'undefined'){
                   validate = false;
                   $(this).closest('div.twitter-handle').find('input.twitter-feed-input').addClass('has-error');
                   $(this).closest('div.twitter-handle').append($('<span class="error twitter_links_error error-msg"> Please add a twitter handle or remove feed</span>'));
                   return;
                }
            }

            if(!twitter_handle_regex.test(handle_value)){ 
                $(this).closest('div.twitter-handle').find('input.twitter-feed-input').addClass('has-error');
                $(this).closest('div.twitter-handle').append($('<span class="error twitter_links_error error-msg"> Please add valid twitter handle e.g. @handle</span>'));
                validate = false;
            }
        }else{
             if(handle_value == '' || typeof handle_value == 'undefined'){
                   validate = false;
                   $(this).closest('div.twitter-handle').find('input.twitter-feed-input').addClass('has-error');
                   $(this).closest('div.twitter-handle').append($('<span class="error twitter_links_error error-msg"> Please add a twitter handle or remove feed</span>'));
                   return;
                }
        }
        
        $(this).val(handle_value);
    });

    return validate;
}

function toggleAddFeedButton(){
    var add_feed_btn = $('a.add-twitter-feed.add-handle');
    var add_feed_button = $('a.add-twitter-feed.add-handles');
    var twitter_handle_input = $('.twitter_feed_modal .modal-body .twitter-handle-wrap').find('input.twitter-feed-input');
    if(twitter_handle_input.length >= max_twitter_handle_limit){
        $(add_feed_btn).hide();
        $(add_feed_button).hide();
    }
    else{
        $(add_feed_btn).show();
        $(add_feed_button).show();
    }
}

function enableDisableSubmitBtn(){
        var modal_body = $(".manage_twitter_feed_modal .modal-body");
        var twitter_handle_input = $(modal_body).find('.twitter-handle-wrap').find('input.twitter-feed-input');
        var input_count = $(twitter_handle_input).length;
        if(input_count <= 1){
            var handle_value = $(twitter_handle_input).val();
            if(handle_value == '' || typeof handle_value == 'undefined'){
                $('.manage_twitter_feed_modal .modal-footer button[type="submit"]').attr('disabled', 'disabled');
            }
        }
}

$('document').ready(function(){
    var form_html = $(".manage_twitter_feed_modal .modal-body").find('.twitter-handle-wrap').html();
    toggleAddFeedButton();
    $('form#twitter_handles').on('submit', function(){
        return validateInputs('twitter_feed_modal');
    });

    $('a.add-twitter-feed.add-handle').on('click', function(){
        addFeedInput(this);
    });

    $('#manage_twitter_feed_modal .modal-body .twitter-handle-wrap, #onboarding_twitter_handles .twitter-handle-wrap')
    .on('click', '.twitter-handle span.remove-handle', function(){
        removeFeedInput(this);
    });

    $('#manage_twitter_feed_modal .modal-body .twitter-handle-wrap').on('blur', '.twitter-handle input.twitter-feed-input', function(){
        var handle_value =  $(this).val();
        if(typeof handle_value == 'undefined')
            removeFeedInput(this);
    });

    $('#manage_twitter_feed_modal .modal-body .twitter-handle-wrap').on('change keyup', 'input.twitter-feed-input', function(){
        var handle_value =  $(this).val();
        if(handle_value != '' || typeof handle_value != 'undefined'){
           $('#manage_twitter_feed_modal .modal-footer button[type="submit"]').removeAttr('disabled');
        }
    });

    $(".twitter-popup-custom").on("hidden.bs.modal", function() {
        $(".manage_twitter_feed_modal .modal-body").find('.twitter-handle-wrap').html(form_html);
        enableDisableSubmitBtn();
        toggleAddFeedButton();
    });

});