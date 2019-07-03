import React from 'react';
import ReactDOM from 'react-dom';
import DocumentPreview from './index';

describe('Post Feed Testing', () => {
  it('renders without crashing Post div', () => {
    const div = document.createElement('div');
    const comp = <DocumentPreview />;
    ReactDOM.render(<DocumentPreview />, div);
    ReactDOM.unmountComponentAtNode(div);
    expect(comp).toMatchSnapshot();
  });
});
