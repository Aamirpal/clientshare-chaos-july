import React from 'react';
import ReactDOM from 'react-dom';
import MemberTile from './index';

it('renders without crashing', () => {
  const div = document.createElement('div');
  ReactDOM.render(<MemberTile member={{
    company: {
      company_name: 'Name',
    },
  }}
  />, div);
  ReactDOM.unmountComponentAtNode(div);
});
