import {
  pinIcon, deleteIcon, pencilIcon, copyIcon,
} from '../images';

export const dropdownItems = [{
  key: 'pin',
  name: 'Pin Post',
  icon: pinIcon,
  highlight: false,
}, {
  key: 'edit',
  name: 'Edit Post',
  icon: pencilIcon,
  highlight: false,
}, {
  key: 'delete',
  name: 'Delete Post',
  icon: deleteIcon,
  highlight: true,
}, {
  key: 'copy',
  name: 'Copy Post link',
  icon: copyIcon,
  highlight: false,
}];

export const dropdownItemsReview = [{
  key: 'edit',
  name: 'Edit review',
  icon: pencilIcon,
  highlight: false,
}, {
  key: 'delete',
  name: 'Delete review',
  icon: deleteIcon,
  highlight: true,
}];

export const conductedViaList = {
  0: 'F2F',
  1: 'Call',
  2: 'Video',
  3: 'DOC',
};
