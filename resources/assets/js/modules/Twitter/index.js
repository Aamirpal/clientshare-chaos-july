import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import { TwitterTimelineEmbed } from 'react-twitter-embed';
import { setTimeout } from 'timers';
import withTheme from '../../utils/hoc/withTheme';
import Modal from '../../components/Modal';
import Icon from '../../components/Icon';
import { Button, Spinner } from '../../components';
import ButtonGroup from '../../components/ButtonGroup';
import { infoIcon, AddIcon, EditGrayIcon } from '../../images';
import AddTwitterInput from './AddTwitterInput';
import { styles } from './styles';
import { saveTwitterHandler, getTwitterHandler } from '../../api/app';
import { globalConstants } from '../../utils/constants';

const { isAdmin, shareName } = globalConstants;

const TwitterModal = React.memo(({ classes, modelProps, modalCancel }) => {
  const [isEdit, setIsEdit] = useState(false);
  const [twitterTabFeed, setTwitterTabFeed] = useState({
    name: '',
    loading: false,
  });
  const [activeTwitterHandle, setActiveTwitterHandle] = useState(null);
  const [twitterHandler, setTwitterHandler] = useState(null);
  const [twitterInputArray, setTwitterInputArray] = useState([{
    value: '',
    error: '',
  }]);

  const addTwitterInputField = () => {
    if (twitterInputArray.length < 3) {
      setTwitterInputArray(previous => [
        ...previous,
        {
          value: '',
          error: '',
        },
      ]);
    }
  };

  const deleteTwitterInputField = (index) => {
    if (twitterInputArray.length > 1) {
      twitterInputArray.splice(index, 1);
      setTwitterInputArray([
        ...twitterInputArray,
      ]);
    }
  };
  const changeValue = (index, event) => {
    twitterInputArray[index].value = event.target.value;
    twitterInputArray[index].error = '';
    setTwitterInputArray([
      ...twitterInputArray,
    ]);
  };

  const editTwitterInputField = () => {
    const twitterHandlers = twitterHandler.map(handler => ({
      value: handler,
      error: '',
    }));

    setTwitterInputArray([
      ...twitterHandlers,
    ]);
    setTwitterHandler([]);
    setIsEdit(true);
  };

  const getTwitterHandlerApi = () => {
    getTwitterHandler().then(({ data: { twitter_handles } }) => {
      setTwitterHandler(twitter_handles);
      setTwitterTabFeed({
        name: twitter_handles[0].split('@')[1],
        loading: false,
      });
      setActiveTwitterHandle(0);
    }).catch(() => false);
  };

  useEffect(() => {
    getTwitterHandlerApi();
  }, []);

  const showTwitterFeed = (twitter_handles) => {
    const tagName = twitter_handles.name.split('@')[1];
    setTwitterTabFeed(prev => ({
      ...prev,
      loading: true,
    }));

    setTimeout(() => {
      setTwitterTabFeed(prev => ({
        ...prev,
        name: tagName,
        loading: false,
      }));
    }, 500);
    setActiveTwitterHandle(twitter_handles.id);
  };


  const addTwitterHandlerApi = () => {
    const twitterHandlers = twitterInputArray.map(item => item.value);
    const twitter_handles = twitterHandlers;
    saveTwitterHandler(twitter_handles).then(() => {
      getTwitterHandlerApi();
      window.location.reload();
    }).catch((error) => {
      const abc = twitterInputArray.map(item => ({
        ...item,
        error: (!item.value || item.value.split('@').length < 2) && error.message.validation_messages,
      }));
      setTwitterInputArray([
        ...abc,
      ]);
    });
  };

  const createTwitterButtons = () => twitterHandler.map((handler, index) => ({
    name: handler,
    data: [],
    id: index,
  }));

  const renderModal = () => (
    twitterHandler.length
      ? (
        <div className="twitter-feed-wrap">
          <div className="twitter-handle-tabs">
            <ButtonGroup buttons={createTwitterButtons()} onClick={showTwitterFeed} active={activeTwitterHandle} />
            {isAdmin
              ? (
                <span className="edit-icon" onClick={editTwitterInputField}>
                  <Icon path={EditGrayIcon} />
                </span>
              )
              : (' ')
       }
          </div>
          <div className="twitter-feed-inner">
            {!twitterTabFeed.loading ? (
              <TwitterTimelineEmbed
                sourceType="profile"
                screenName={twitterTabFeed.name}
                options={{ height: 400 }}
                noScrollbar
                noHeader
                noFooter
              />
            ) : (<Spinner />)}
          </div>

        </div>
      )
      : (
        <div className="add-twitter-wrap">
          {isEdit ? (
            <div className="edit-twitter-info">
              <p className={classes.twitterDescription}>
         Manage the feeds that are displayed on the
                {' '}
                {shareName}
                {' '}
         Client Share.
              </p>
              <div className={classnames(classes.twitterAlert, 'd-flex align-items-center')}>
                <span>
                  <Icon path={infoIcon} />
                </span>
                <p className="mb-0">You can have a maximum of 3.</p>
              </div>
            </div>
          ) : (
            <div className="add-twitter-info">
              <p className={classes.twitterDescription}>
   Pull in company specific updates from Twitter straight to your share.
   All you need is the Twitter handle e.g. @myclientshare
              </p>
              <div className={classnames(classes.twitterAlert, 'd-flex align-items-center')}>
                <span>
                  <Icon path={infoIcon} />
                </span>
                <p className="mb-0">The feeds you add will be accessible by all users in the same share. </p>
              </div>
            </div>
          )}


          <div className={classes.twitterForm}>
            {twitterInputArray.map((item, index) => (
              <AddTwitterInput
                inputProps={{ onChange: e => changeValue(index, e), value: item.value }}
                labelText={index + 1}
                cancelInput={() => deleteTwitterInputField(index)}
                showApiError={item.error}
              />
            ))}
            {twitterInputArray.length < 3
       && (
       <span className={classes.addFeedBtn} onClick={addTwitterInputField}>
         <Icon path={AddIcon} />
       Add feed
       </span>
       )}

          </div>
          <div className={classes.btnWrap}>
            <Button
              buttonProps={{
                variant: 'secondary',
                className: classes.cancelBtn,
                onClick: () => modalCancel(),
              }}
            >
 Cancel
            </Button>

            <Button
              buttonProps={{
                variant: 'primary',
                className: classes.saveBtn,
                onClick: () => addTwitterHandlerApi(),
              }}
            >
 Save
            </Button>
          </div>
        </div>
      )
  );
  return (
    <div>

      <Modal
        headerText="Twitter feed"
        modelProps={modelProps}
      >
        <div className={classes.modalContainer}>
          {twitterHandler ? (renderModal()) : (<Spinner />)}
        </div>

      </Modal>

    </div>
  );
});

TwitterModal.propTypes = {
  classes: PropTypes.object.isRequired,
  modelProps: PropTypes.object.isRequired,
  modalCancel: PropTypes.func.isRequired,
};

export default withTheme(injectSheet(styles)(TwitterModal));
