import React from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';


import ErrorBoundary from '../components/ErrorBoundary';

import Feed from '../screens/Feed';
import CommunityMembers from '../screens/CommunityMembers';
import Notifications from '../components/Notifications';
import GlobalSearch from '../modules/GlobalSearch';
// import TwitterModal from '../modules/Twitter';
import TwitterButton from '../components/TwitterButton';
import '../utils/interceptor';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

if (document.getElementById('feed')) {
  ReactDOM.render(<ErrorBoundary><Feed /></ErrorBoundary>, document.getElementById('feed'));
}

if (document.getElementById('community_members')) {
  ReactDOM.render(<ErrorBoundary><CommunityMembers /></ErrorBoundary>, document.getElementById('community_members'));
}

if (document.getElementById('main-notification')) {
  ReactDOM.render(<Notifications />, document.getElementById('main-notification'));
}

if (document.getElementById('global_search')) {
  ReactDOM.render(<GlobalSearch />, document.getElementById('global_search'));
}

// document.getElementById('twitter_convert').addEventListener('click', () => {
//   ReactDOM.render(<TwitterModal />, document.getElementById('react_modal'));
// });

if (document.getElementById('twitter_convert')) {
  ReactDOM.render(<TwitterButton />, document.getElementById('twitter_convert'));
}
