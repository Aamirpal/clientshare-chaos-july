import React from 'react';
import injectSheet from 'react-jss';
import cx from 'classnames';
import get from 'lodash/get';
import withTheme from '../../utils/hoc/withTheme';
import { Heading, RoundIcon } from '../index';
import { closeIcon } from '../../images';

const styles = {
  embedContainer: {
    border: ({ theme }) => `1px solid ${theme.dark_white}`,
    margin: ({ noImage }) => `${noImage ? '0 0 16.5px 0' : '16px 84px'}`,
    display: ({ theme }) => theme.flex,
    borderRadius: 4,
    borderTop: ({ theme, noImage }) => `${noImage ? '0' : '1'}px solid ${theme.dark_white}`,
    '@media (max-width: 767px)': {
      margin: ({ noImage }) => `${noImage ? '0 0 16.5px 0' : '16px 0'}`,
    },
  },
  embedImage: {
    height: 131,
    width: 131,
    display: 'inline-block',
  },
  embedRight: {
    padding: ({ noImage }) => `${noImage ? 17 : '15px 15px 0'}`,
    width: ({ noImage }) => `${noImage ? '100%' : '74%'}`,
    display: 'inline-block',
  },
  embedFaviconContainer: {
    display: ({ theme }) => theme.flex,
    justifyContent: 'space-between',
    marginBottom: 11,
  },
  embedFavicon: {
    height: 16,
  },
  embedTitle: {
    fontSize: 14,
    lineHeight: '16px',
    color: ({ theme }) => theme.basic_color,
    marginBottom: 6,
  },
  embedDescription: {
    marginBottom: 0,
  },
  embedRightTop: {
    display: 'flex',
    alignItems: 'center',
  },
  embedProvider: {
    fontSize: 13,
    letterSpacing: 0.2,
    color: ({ theme }) => theme.provider_gray,
    margin: '0 5px',
  },
  textLimit: {
    textOverflow: 'ellipsis',
    width: '100%',
    overflow: 'hidden',
    display: 'inline-block',
    whiteSpace: 'nowrap',
  },
};

const UrlPreview = React.memo(({
  classes, embedData, removeEmbed, noImage,
}) => (
  <div className={classes.embedContainer}>
    {!noImage
     && (
     <a href={embedData.full_url} target="_blank">
       <div style={{ background: `url('${embedData.favicon}') center / contain no-repeat`, backgroundSize: 'contain' }} className={classes.embedImage} />
     </a>
     )}
    <div className={classes.embedRight}>
      <div className={classes.embedFaviconContainer}>
        <a href={embedData.full_url} target="_blank" className={classes.embedRightTop}>
          <img src={get(embedData, 'api_response.favicon_url', '')} alt="favicon" className={classes.embedFavicon} />
          <Heading
            headingProps={{ className: classes.embedProvider }}
          >
            {get(embedData, 'api_response.provider_name', '')}
          </Heading>
        </a>
        {removeEmbed && (
        <RoundIcon
          icon={closeIcon}
          iconProps={{ className: classes.cancelIcon }}
          onClick={() => removeEmbed(embedData)}
        />
        )}
      </div>
      <a href={embedData.full_url} target="_blank">
        <Heading as="h4" headingProps={{ className: classes.embedTitle }}>{embedData.title}</Heading>
        <Heading
          headingProps={{
            className: cx(classes.embedTitle, classes.embedDescription, {
              [classes.textLimit]: noImage,
            }),
          }}
        >
          {get(embedData, 'api_response.description', '')}
        </Heading>
      </a>
    </div>
  </div>
));

UrlPreview.defaultProps = {
  removeEmbed: false,
  noImage: false,
};

export default withTheme(injectSheet(styles)(UrlPreview));
