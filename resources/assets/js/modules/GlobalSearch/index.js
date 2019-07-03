import React, { useState, useEffect } from 'react';
import { Scrollbars } from 'react-custom-scrollbars';
import Highlighter from 'react-highlight-words';
import useDebouncedCallback from 'use-debounce/lib/callback';
import Dropdown from 'react-bootstrap/Dropdown';
import MediaQuery from 'react-responsive';
import { globalConstants, API_URL } from '../../utils/constants';

import { getSearchResult } from '../../api/app';
import Icon from '../../components/Icon';
import loadingIcon from '../../images/loader.svg';
import searchIcon from '../../images/search_icon.svg';
import closeIcon from '../../images/close_bg_icon.svg';
import './global_search.scss';

const { clientShareId } = globalConstants;

const initialState = {
  loading: false,
  posts: [],
};
let wrapperRef = null;
let isAPICall = true;

const GlobalSearch = () => {
  const [results, setResults] = useState(initialState);
  const [inputValue, setInputValue] = useState('');
  const [bodyClass, setbodyClass] = useState(false);

  const clearState = () => {
    setResults(initialState);
    setInputValue('');
    setbodyClass(false);
  };

  const closeInputs = () => {
    setResults(initialState);
    setInputValue('');
  };

  const handleClickOutside = (event) => {
    if (wrapperRef && !wrapperRef.contains(event.target)) {
      if (results.value !== '') {
        clearState();
        isAPICall = false;
      }
    }
  };

  useEffect(() => {
    document.addEventListener('mousedown', handleClickOutside);
  });

  const getSearchResultData = (value) => {
    if (value && isAPICall) {
      setResults({ ...results, loading: true });
      getSearchResult(value).then(({ data: { posts } }) => {
        if (isAPICall) {
          setResults({
            loading: false,
            posts,
          });
        } else {
          setResults(initialState);
        }
      }).catch(() => { });
    } else {
      setResults(initialState);
    }
  };

  const [getResultDebounce] = useDebouncedCallback(
    (value) => {
      getSearchResultData(value);
    }, 500, [],
  );

  const setWrapperRef = (node) => {
    wrapperRef = node;
  };

  const addBodyWrapper = () => {
    isAPICall = true;
    document.body.classList.add('body-scrollbar');
  };
  const addMobileWrapper = () => {
    setbodyClass(!bodyClass);
    if (!bodyClass) {
      isAPICall = true;
      document.body.classList.add('body-scrollbar');
    } else {
      isAPICall = false;
      clearState();
      document.body.classList.remove('body-scrollbar');
    }
  };

  const { posts, loading } = results;
  return (
    <div className="d-inline">
      <MediaQuery query="(max-device-width: 767px)">
        <div className="mobile-search" ref={setWrapperRef}>
          <Dropdown>
            <Dropdown.Toggle variant="success" id="dropdown-basic" className="search-mobile-icon" as="span">
              <span onClick={addMobileWrapper}>
                <Icon path={searchIcon} />
              </span>
            </Dropdown.Toggle>
            <Dropdown.Menu>
              <div className="search-mbl-box">
                <form className="mobile-search-form">
                  <input value={inputValue} onChange={({ target: { value } }) => { setInputValue(value); return getResultDebounce(value); }} autoComplete="off" spellCheck="false" className="form-control search-box" id="global-search-box" type="search" placeholder="Type Something..." />
                  <div onClick={closeInputs}>
                    <Icon path={closeIcon} />
                  </div>
                </form>
              </div>
            </Dropdown.Menu>
          </Dropdown>
          {inputValue && (
          <div className="search-row">
            <div className="scrol-dropdown">
              <ul className="search-dropdown">
                {loading && <div className="search-loader text-center"><img width="40" src={loadingIcon} alt="Loading..." /></div>}
                {(!loading && !posts.length) && <li><span className="no-result" onClick={clearState}>No result found!</span></li>}
                {posts.map(post => (
                  <li key={post.post_id}>
                    <a href={`${API_URL}/clientshare/${clientShareId}/${post.post_id}`}>
                      <h6>
                        <Highlighter
                          highlightClassName="YourHighlightClass"
                          searchWords={[inputValue]}
                          autoEscape
                          textToHighlight={post.post_subject}
                        />
                      </h6>
                      <p>
                        <Highlighter
                          highlightClassName="YourHighlightClass"
                          searchWords={[inputValue]}
                          autoEscape
                          textToHighlight={post.post_description}
                        />
                      </p>
                    </a>
                  </li>
                ))}

              </ul>
            </div>
          </div>
          )}
        </div>
      </MediaQuery>
      <MediaQuery query="(min-device-width: 767px)">
        <div ref={setWrapperRef}>
          <div className="search-desktop">
            <form>
              <input value={inputValue} onFocus={addBodyWrapper} onChange={({ target: { value } }) => { setInputValue(value); return getResultDebounce(value); }} autoComplete="off" spellCheck="false" className="form-control search-box" id="global-search-box" type="search" placeholder="Type Something..." />
            </form>
          </div>
          {inputValue && (
          <div className="search-row">
            <Scrollbars className="scrol-dropdown" style={{ height: 291 }}>
              <ul className="search-dropdown">
                {loading && <div className="search-loader text-center"><img width="40" src={loadingIcon} alt="Loading..." /></div>}
                {(!loading && !posts.length) && <li><span className="no-result">No result found!</span></li>}
                {posts.map(post => (
                  <li key={post.post_id}>
                    <a key={post.post_id} href={`${API_URL}/clientshare/${clientShareId}/${post.post_id}`}>
                      <h6>
                        <Highlighter
                          highlightClassName="YourHighlightClass"
                          searchWords={[inputValue]}
                          autoEscape
                          textToHighlight={post.post_subject}
                        />
                      </h6>
                      <p>
                        <Highlighter
                          highlightClassName="YourHighlightClass"
                          searchWords={[inputValue]}
                          autoEscape
                          textToHighlight={post.post_description}
                        />
                      </p>
                    </a>
                  </li>
                ))}

              </ul>
            </Scrollbars>
          </div>
          )}
        </div>
      </MediaQuery>
    </div>
  );
};

export default GlobalSearch;
