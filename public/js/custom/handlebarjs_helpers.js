Handlebars.registerHelper('extention', function(url) {
	url = url.fn(this).split('.');
	return url.pop();
});

Handlebars.registerHelper('extention_icon', function(data) {
	return extension_wise_img(data.fn(this));
});

Handlebars.registerHelper('feedSignedUrl', function(url, element, post_id) {
	return feedSignedUrl(url, element, post_id);
});

Handlebars.registerHelper('endorse', function(endorse_by_me, endorse_by_others, options) {
  if(endorse_by_me.length && !endorse_by_others.length) {
    return 'You';
  }else if(endorse_by_me.length && endorse_by_others.length) {
    if(endorse_by_others.length > 1)
      return 'You, '+endorseUserWrap(endorse_by_others[0]['user'])+' and '+ endorseUsersList(endorse_by_others.slice(1));
    else 
      return 'You & '+endorseUserWrap(endorse_by_others[0]['user']);
  } else if(!endorse_by_me.length && endorse_by_others.length){
    switch(endorse_by_others.length){
      case 1:{
        return endorseUserWrap(endorse_by_others[0]['user']);
      }case 2:{
        return endorseUserWrap(endorse_by_others[0]['user'])+' & '+endorseUserWrap(endorse_by_others[1]['user']);
      }case 3:{
        return endorseUserWrap(endorse_by_others[0]['user'])+', '+endorseUserWrap(endorse_by_others[1]['user'])+' & '+endorseUserWrap(endorse_by_others[2]['user']);
      } default: {
        return endorseUserWrap(endorse_by_others[0]['user'])+', '+endorseUserWrap(endorse_by_others[1]['user'])+' & '+ endorseUsersList(endorse_by_others.slice(2));
      }
    }
  }
});

Handlebars.registerHelper("counter", function (index){
  return index + 1;
});

Handlebars.registerHelper('compareValue', function(string, options) {
    if(!string){
      return options.inverse(this);
    }
    var found = string.match("youtube.com");
    if(found){
      return options.fn(this);
    }
    return options.inverse(this);
}) 

Handlebars.registerHelper('checkPostVisibility', function(string, options) {
    var array = string.split(',');
    var found = array.indexOf("All");
    if(found != -1){
      return options.fn(this);
    }
    return options.inverse(this);
}) 

Handlebars.registerHelper('addLink', function(string,limit, options) {
   var description = linkify(string);
   if(limit){
     var text = htmlSubstring(description, limit);
     return text.replace(/(<br( \/)?>\s*)*$/,'');
   }
   return description;
}) 

Handlebars.registerHelper('dateFormat', function(date_string, format, options) {
  return stringDateFormat(date_string, format);
})

Handlebars.registerHelper('postDescriptionTextCheck', function(string, limit, options) {
  if(string.length>limit){
    return options.fn(this);
  }
  return options.inverse(this);
})

Handlebars.registerHelper('limitText', function(string, limit, options) {
  return limitText(string, limit);
})

Handlebars.registerHelper('isSingleImage', function(images, options) {
  if(images.length > 1) return false;
  return 'single-image';
})

Handlebars.registerHelper('indexInfo', function(object, index, options) {
  return object[index];
})

Handlebars.registerHelper('compare', function(first_value, second_value, options) {
  if(first_value === second_value) {
    return options.fn(this);
  }
  return options.inverse(this);
})

Handlebars.registerHelper('count', function(first_value, second_value, options) {
  if(first_value > second_value) {
    return options.fn(this);
  }
  return options.inverse(this);
})

Handlebars.registerHelper('ifCond', function (first_value, operator, second_value, options) {
    switch (operator) {
        case '==':
            return (first_value == second_value) ? options.fn(this) : options.inverse(this);
        case '===':
            return (first_value === second_value) ? options.fn(this) : options.inverse(this);
        case '!=':
            return (first_value != second_value) ? options.fn(this) : options.inverse(this);
        case '!==':
            return (first_value !== second_value) ? options.fn(this) : options.inverse(this);
        case '<':
            return (first_value < second_value) ? options.fn(this) : options.inverse(this);
        case '<=':
            return (first_value <= second_value) ? options.fn(this) : options.inverse(this);
        case '>':
            return (first_value > second_value) ? options.fn(this) : options.inverse(this);
        case '>=':
            return (first_value >= second_value) ? options.fn(this) : options.inverse(this);
        case '&&':
            return (first_value && second_value) ? options.fn(this) : options.inverse(this);
        case '||':
            return (first_value || second_value) ? options.fn(this) : options.inverse(this);
        default:
            return options.inverse(this);
    }
});

Handlebars.registerHelper('getPostUsers', function(string) {
  if(space_users.length == 0){
    return 'Restricted';
  }
  var users_array = string.split(',');
  return getUsersListHtml(users_array);
});

Handlebars.registerHelper('commentLimit', function(loop_index, comment_length, limit, options) {
  if(loop_index < ((comment_length+1)-limit)) {
    return options.fn(this);
  }
  return options.inverse(this);
});

Handlebars.registerHelper('math', function (first_value, operator, second_value, options) {

  switch (operator) {
    case '-':
      return first_value - second_value;
    case '+':
      return first_value + second_value;
    case '/':
      return (first_value / second_value).toFixed(2);
    default:
      return options.inverse(this);
  }
});

Handlebars.registerHelper('minus',function(number, decreased_by, context){
  return number-decreased_by;
});

Handlebars.registerHelper('removePreviewedDocument',function(documents, file, context){
  $(documents).each(function(index){
    if(file.length && this.id == file[0]['id']) {
      documents.splice(index, 1);
      return true;
    }
  });
  this['documents'] = documents;
});

Handlebars.registerHelper('escape', function(variable) {
  return variable.replace(/(['"])/g, '\\$1');
});

Handlebars.registerHelper('toJson', function(variable) {
  return JSON.stringify(variable);
});

Handlebars.registerHelper('calculateRAGColor', function(date_string) {
  var now = moment(new Date());
  var end = moment(date_string);
  if(!end.isValid() || !date_string){
    var days = 0;
  } else {
    var duration = moment.duration(now.diff(end));
    var days = duration.asDays();
  }

  if(days >0 && days <=7 ) return 'green';
  else if(days >7 && days <=14) return 'yellow';
  else return 'red';
});

Handlebars.registerHelper('checkUserIsPostOwnerOrAdmin', function(is_admin, logged_in_user, post_owner, options) {
  if(is_admin || (logged_in_user == post_owner) ){
    return options.fn(this);
  }
  return options.inverse(this);
});

Handlebars.registerHelper('userStatusByInvitationCode',function(invitation_code){
    var status = 'Pending';
    if(invitation_code > 0) status = 'Active';
    else if(invitation_code < 0) status = 'Cancelled';

    return status;
});

Handlebars.registerHelper('isProfileImageExist', function (profile_image){
  if(profile_image){
    return profile_image;
  }
  return baseurl+'/images/dummy-avatar-img.svg';
});

Handlebars.registerHelper('isMobileDevice', function(options) {
  if(isMobileDevice()){
    return options.fn(this);
  }
  return options.inverse(this);
});