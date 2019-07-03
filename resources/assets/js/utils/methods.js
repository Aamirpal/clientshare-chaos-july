import uuid from 'uuid';
import _take from 'lodash/take';
import { API_URL } from './constants';

const createMemberTabData = ({ buyer, seller }) => [{
  name: 'All',
  data: [],
  id: null,
}, {
  name: seller.company_name,
  data: seller,
  id: seller.id,
}, {
  name: buyer.company_name,
  data: buyer,
  id: buyer.id,
}];

const subString = (text, from, to) => {
  const res = text.substring(from, to);
  return res;
};

const getWindowHeight = pixel => window.innerHeight * pixel;

const setItem = (key, value) => localStorage.setItem(key, value);

const getItem = key => localStorage.getItem(key);

const isIEBrower = () => !!window.MSInputMethodContext && !!document.documentMode;

function xmlToJson(xml) {
  // Create the return object
  let obj = {}; let i; let j; let attribute; let item; let old;

  if (xml.nodeType === 1) { // element
    // do attributes
    if (xml.attributes.length > 0) {
      obj['@attributes'] = {};
      for (j = 0; j < xml.attributes.length; j += 1) {
        attribute = xml.attributes.item(j);
        obj['@attributes'][attribute.nodeName] = attribute.nodeValue;
      }
    }
  } else if (xml.nodeType === 3) { // text
    obj = xml.nodeValue;
  }

  // do children
  if (xml.hasChildNodes()) {
    for (i = 0; i < xml.childNodes.length; i += 1) {
      item = xml.childNodes.item(i);
      const { nodeName } = item;
      if ((obj[nodeName]) === undefined) {
        obj[nodeName] = xmlToJson(item);
      } else {
        if ((obj[nodeName].push) === undefined) {
          old = obj[nodeName];
          obj[nodeName] = [];
          obj[nodeName].push(old);
        }
        obj[nodeName].push(xmlToJson(item));
      }
    }
  }
  return obj;
}

const updateMemberData = (res, memberList, groupId) => {
  const { data: { group_members } } = res;

  const data = Object.keys(memberList).reduce((acc, id) => {
    acc[id] = {
      ...memberList[id],
      show: false,
    };
    return acc;
  }, {});

  return {
    ...data,
    [groupId]: {
      data: group_members,
      show: true,
    },
  };
};

const toggleMemberData = (groupId, memberList, type) => ({
  memberList: {
    ...memberList,
    [groupId]: {
      ...memberList[[groupId]],
      show: type,
    },
  },
});

const getType = (type) => {
  if (typeof type === 'string') {
    switch (type.toLowerCase()) {
      case 'jpeg':
      case 'jpg':
      case 'png':
        return 'images';
      case 'ppt':
      case 'pptx':
      case 'docx':
      case 'pdf':
      case 'doc':
      case 'xls':
      case 'xlsx':
      case 'csv':
        return 'files';
      case 'mov':
      case 'MOV':
      case 'mp4':
      case 'MP4':
        return 'videos';
      default:
        return null;
    }
  }
  return null;
};

const getFileExtension = fileName => fileName.split('.').pop();

const convertAttachmentDataItem = attachment => ({
  originalName: attachment.file.name,
  s3_name: attachment.PostResponse.Key['#text'],
  size: attachment.file.size,
  url: attachment.PostResponse.Location['#text'],
  extention: getFileExtension(attachment.file.name),
  mimeType: attachment.file.type,
}
);

const convertAttachmentData = attachments => Object.keys(attachments).map((key) => {
  const attachment = attachments[key];
  return convertAttachmentDataItem(attachment);
});

const seperateFiles = files => Object.keys(files).reduce((acc, file) => {
  const updatedFile = {
    ...files[file],
    id: file,
    metadata: files[file].file !== undefined ? convertAttachmentDataItem(files[file]) : {},
  };
  acc[[updatedFile.type]].push(updatedFile);
  return acc;
}, {
  images: [],
  videos: [],
  files: [],
  loaders: [],
});

const getPostIdfromUrl = () => {
  const url = window.location.pathname;
  const params = url.split('/');
  if (params.length > 3) {
    return params[3];
  }
  return null;
};

const urlify = (text) => {
  const urlRegex = /(((http|https|ftp|ftps)\:\/\/)|(www.))[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,3}(\S*)?/ig;
  return text.replace(urlRegex, url => `<a target="_blank" href="${url}">${url}</a>`);
};

const tagify = (text, users, openUserPopup) => {
  const urlRegex = /(@(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})+)/g;
  return text.replace(urlRegex, (tag) => {
    const tagSplit = tag.split('@')[1];

    if (tagSplit === '00000000-0000-0000-0000-000000000000') {
      return '<a href="#">@All</a>';
    }
    if (users[tagSplit]) {
      return `<a href="#" onClick=${openUserPopup(users[tagSplit].user.id)}>@${users[tagSplit].user.fullname}</a>`;
    }
    return tag;
  });
};

const isOS = () => {
  const ua = navigator.userAgent.toLowerCase();
  if (ua.indexOf('safari') !== -1) {
    if (ua.indexOf('chrome') > -1) {
      return false;// Chrome
    }
    return true;
  }
  return false;
};

const copyLink = (text) => {
  const textField = document.getElementById('copy_post_link_ios');
  textField.innerHTML = text;
  if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
    const range = document.createRange();
    range.selectNodeContents(textField);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
    textField.setSelectionRange(0, 999999);
  } else {
    textField.select();
  }

  document.execCommand('copy');
};

const getMbSize = size => size * 1024 * 1024;

const downloadFile = (url, filename) => {
  window.location = `${API_URL}/download-file?url=${url}&file_name=${filename}`;
};

const sortComments = (comments, seeMore) => {
  if (seeMore) {
    return comments;
  }
  const check = [
    ...comments,
  ];
  return _take(check.reverse(), 2).reverse();
};

const createEditAttachmentFormat = (files, type) => files.reduce((acc, file) => {
  const id = uuid.v4();
  acc[id] = {
    PostResponse: {
      Key: {
        '#text': file.s3_file_path,
      },
      Location: {
        '#text': file.metadata.url,
      },
    },
    file: {
      name: file.metadata.originalName,
      path: file.metadata.originalName,
      type: file.metadata.mimeType,
      size: file.metadata.size,
    },
    type,
    exact: file.post_file_url,
    attachmentID: file.id,
  };
  return acc;
}, {});

const createEditAttachmentFormatWithType = files => files.reduce((acc, file) => {
  const id = uuid.v4();
  acc[id] = {
    PostResponse: {
      Key: {
        '#text': file.metadata.s3_name,
      },
      Location: {
        '#text': file.metadata.url,
      },
    },
    file: {
      name: file.metadata.originalName,
      path: file.metadata.originalName,
      type: file.metadata.mimeType,
      size: file.metadata.size,
    },
    type: getType(file.metadata.extention),
    exact: file.file_url,
    attachmentID: file.id,
  };
  return acc;
}, {});

const convertIdtoNameString = (editComment, updateUsers) => {
  const pattern = /(@(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})+)/g;
  const seperateComments = editComment.split(' ');
  return seperateComments.map((tags) => {
    const matchId = tags.match(pattern);
    if (matchId) {
      const tag = matchId[0].split('@');
      if (updateUsers[tag[1]]) {
        return tags.replace(tag[1], `${updateUsers[tag[1]].user.fullname}`);
      }
    }

    return tags;
  }).join(' ').trimStart();
};

const findKey = (data, found, key) => data.find(item => item[[key]] === found);
const getAllUrlParams = (url) => {
  let queryString = url ? url.split('?')[1] : window.location.search.slice(1);
  const obj = {};

  if (queryString) {
    queryString = queryString.split('#')[0];

    const arr = queryString.split('&');

    for (let i = 0; i < arr.length; i += 1) {
      const a = arr[i].split('=');
      let paramName = a[0];
      let paramValue = typeof (a[1]) === 'undefined' ? true : a[1];

      paramName = paramName.toLowerCase();
      if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase();

      if (paramName.match(/\[(\d+)?\]$/)) {
        const key = paramName.replace(/\[(\d+)?\]/, '');
        if (!obj[key]) obj[key] = [];

        if (paramName.match(/\[\d+\]$/)) {
          const index = /\[(\d+)\]/.exec(paramName)[1];
          obj[key][index] = paramValue;
        } else {
          obj[key].push(paramValue);
        }
      } else if (!obj[paramName]) {
        obj[paramName] = paramValue;
      } else if (obj[paramName] && typeof obj[paramName] === 'string') {
        obj[paramName] = [obj[paramName]];
        obj[paramName].push(paramValue);
      } else {
        obj[paramName].push(paramValue);
      }
    }
  }

  return obj;
};

export {
  createMemberTabData,
  subString,
  getWindowHeight,
  setItem,
  getItem,
  isIEBrower,
  xmlToJson,
  getType,
  seperateFiles,
  convertAttachmentData,
  getFileExtension,
  updateMemberData,
  toggleMemberData,
  getPostIdfromUrl,
  urlify,
  copyLink,
  getMbSize,
  downloadFile,
  createEditAttachmentFormat,
  createEditAttachmentFormatWithType,
  tagify,
  sortComments,
  convertIdtoNameString,
  isOS,
  findKey,
  getAllUrlParams,
};
