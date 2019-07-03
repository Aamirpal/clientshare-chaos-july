function poplateProfileImage() {
    $('.lazy-asset').each(function () {
        if (!$(this).attr('data-lazy-asset'))
            return true;
        $(this).css("background-image", "url('" + $(this).attr('data-lazy-asset') + "')");
        $(this).removeClass('lazy-asset');
    });
}

$(window).on('load', function () {
    poplateProfileImage();
});

function getParameterByName(required_parameter) {
    var url = decodeURIComponent(window.location.search.substring(1)),
      url_parameter = url.split('&'),
      parameter_name;
  
    for (var index = 0; index < url_parameter.length; index++) {
      parameter_name = url_parameter[index].split('=');
      if (parameter_name[0] === required_parameter) {
        return parameter_name[1] === undefined ? true : parameter_name[1];
      }
    }
    return null;
  };

  function change_company(element){
    var userId = $(element).attr('user-id');
    var companyId = $(element).val();
    $('.modal_title').html( "Change "+$(element).closest('.tablerow').find('.mem_name').html().trim()+"'s Company " );
    $('.modal_text').html( "Are you sure you want to change "+$(element).closest('.tablerow').find('.mem_name').html().trim()+"'s selected Company? " );
    $('.hidden_company_id').val(companyId);
    $('.hidden_user_id').val(userId);
    $('#editcompany').modal('show');
  }
  
  function submit_company_form(element){
    var company_id = $('.hidden_company_id').val();
    var user_id = $('.hidden_user_id').val();
    $.ajax({
      type: "GET",
      url:  baseurl+'/companyupdate?company_id='+company_id+ '&user_id=' + user_id+'&space_id='+current_space_id ,
      success: function(response) {
      $('.setting_tabs a[href="#user-management-tab').trigger('click');
      $('#editcompany').modal('hide');
      $('.user'+user_id).find('.companyNameEdit').hide();
      $('.user'+user_id).find('.companyName').show();
      $('.user'+user_id).find('.companyName').html($('.user'+user_id).find(".select_company_n option[value='"+company_id+"']").text());
      },error: function(message) {  }
    });
  }
  
  function func_edit_company(element){
    var userid = $(element).attr('userid');
    $('.companyName').show();
    $('.companyNameEdit').hide();
    $('.user'+userid).find('.companyName').hide();
    $('.user'+userid).find('.companyNameEdit').show();
    $('.user'+userid).find('.bootstrap-select').prop('selectedIndex',0);//reset selec box
  }