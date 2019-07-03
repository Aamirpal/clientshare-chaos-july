$(document).ready(function(){

  $('.multiselect-shares').multiselect({
    numberDisplayed: 1,
    includeSelectAllOption: false,
    selectAllText: 'Select All',
    enableCaseInsensitiveFiltering: true,
    buttonWidth: '100%'
  });

  $(document).on('click', '.user_copy_share', function() {
    $('.flash-message').hide();
    $('form.user-copy-form').trigger('reset');
    $('form.user-copy-form').find('.multiselect-shares').multiselect('rebuild');
    var space_id = $(this).attr('spaceid');
    $('.old_share_id').val(space_id);

  });

     $(document).on('click', '.copy-user-trigger', function() {
      $(this).attr('disabled', true);
      $('.user-copy-error,.user-copy-success').text('');
      var form_data = new FormData($('#user-invitation-form')[0]); 
      $.ajax({
          type: "POST",
          headers: {
              "cache-control": "no-cache",
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          url: baseurl+'/migrate_user',
          data: form_data,
          async: false,
          success: function (response) {
          $('.send-email,.copy-user-trigger').attr('disabled', false);
          $('.mi-overlay-div').addClass('hidden');
           if(response.success){
              $('.flash-message').show().removeClass('alert-danger').addClass('alert-success').html("User's copied successfully.");
              $("#migrate-user .close-inner").trigger('click');
           }else{
            $('.flash-message').show().removeClass('alert-success').addClass('alert-danger').html(response.message);
           }
          },
          error: function(response){
            alert('Something went wrong. Try again later.');
          },
          cache: false,
          contentType: false,
          processData: false
    });
      return false;
  });

  $(".email_alert_prevent").change(function() {
          $(this).val('false');
      if(this.checked) {
          $(this).val('true');
      }
  });


  $(document).on('change', '.multiselect-shares.shares', function(){
    $.ajax({
      type: 'GET',
      url: baseurl+'/share_communities/'+$(this).val(),
      async: false,
      success: function(res){
        options = [
          {label: 'Select community'},
          {label: res.seller_name.company_name+' - Seller community', value: res.seller_name.id},
          {label: res.buyer_name.company_name+' - Buyer community', value: res.buyer_name.id}
        ];
        $('.multiselect-shares.communities').multiselect('dataprovider', options);
        $('.multiselect-shares.communities').multiselect('rebuild');
      }
    });
  });
    
   $(document).on('click', '#show_manage_share_popup', function () {
       var space_id=$(this).data('space-id');
        $("#business_review_visibility").attr("space_id", space_id);
        var categories = '';
        
        $.ajax({
          type: 'GET',
          url: baseurl + '/get-categories/' + space_id,
          async: false,
          success: function (response) {
              (response.data.is_business_review_enabled) ? $(document).find('#business_review').prop('checked', true) : $(document).find('#business_review').prop('checked', false);
              $.each(response.data.categories, function(index, category){
                if(category.name != "Business Reviews"){
                  categories += '<input type="text" name="'+category.id+'" value="'+category.name+'">'
                }
              })         
              $('.category-list-input').html(categories);
              $("#manage_share_popup").modal("show");
          }
        });
    }); 
    $(document).on('click', '#business_review_visibility', function () {
        $("#manage_share_popup").modal("hide");
        // var show_business_review = $(document).find('#business_review').prop('checked');
        var space_id = $(this).attr('space_id');
        $('.manage-share-popup-space-id').val(space_id)
        data = $('.manage-share-popup-form').serialize();
        $.ajax({
            type: 'POST',
            url: baseurl + '/update-categories',
            headers: { 'X-CSRF-TOKEN': $('.access_token').val() },
            data: data,
            async: false,
            success: function (response) {
                if (response) {
                    (show_business_review) ? $(document).find('#business_review').prop('checked', true) : $(document).find('#business_review').prop('checked', false);
                    $("#manage_share_popup").modal("hide");
                }
            },
            error: function (response) {
                alert('Something went wrong. Try again later.');
            },
        });
    });
});

