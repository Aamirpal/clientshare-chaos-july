import React from 'react';
import ReactDOM from 'react-dom';
import CategoryTile from './index';

it('renders without crashing', () => {
  const div = document.createElement('div');
  ReactDOM.render(<CategoryTile category={{ }} />, div);
  ReactDOM.unmountComponentAtNode(div);
});
