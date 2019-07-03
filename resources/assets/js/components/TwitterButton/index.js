import React, { useState } from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import withTheme from '../../utils/hoc/withTheme';
import Button from '../Button';
import Image from '../Image';
import TwitterModal from '../../modules/Twitter';
import { twitterIcon } from '../../images';

const styles = {
  button: {
    height: '100%',
    padding: 0,
    width: '100%',
    display: 'flex',
    justifyContent: 'center',
    borderBottom: '1px solid rgba(0, 0, 0, 0.4)',
    position: 'relative',
    transform: 'perspective(1px) translateZ(0)',
    borderRadius: 0,
  },
  icon: {
    display: 'flex',
  },
};

const TwitterButton = React.memo(({ classes }) => {
  const [isModal, setModal] = useState(false);
  const showTwitterModal = () => {
    setModal(true);
  };

  return (
    <div className="twitter-btn">
      <Button
        buttonProps={{
          variant: 'link',
          className: classes.button,
          onClick: () => showTwitterModal(),
        }}
      >
        <Image img={twitterIcon} extraClass={classnames(classes.icon, 'navbar-twitter-link')} size="small_image" position="center" round={false} />
      </Button>

      {isModal
        && (
        <TwitterModal
          modelProps={{
            show: isModal,
            onHide: () => setModal(false),
            className: 'twitter-popup md-popup',
          }}
          modalCancel={() => setModal(false)}
        />
        )}

    </div>
  );
});

TwitterButton.propTypes = {
  classes: PropTypes.object.isRequired,
};

export default withTheme(injectSheet(styles)(TwitterButton));
