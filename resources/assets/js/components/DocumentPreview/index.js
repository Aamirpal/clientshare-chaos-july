import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import get from 'lodash/get';
import cx from 'classnames';

import MediaQuery from 'react-responsive';
import {
  Modal, Heading, Icon, Image, Spinner,
} from '../index';
import withTheme from '../../utils/hoc/withTheme';
import { getType, downloadFile, getFileExtension } from '../../utils/methods';
import { getUrlPreviewApi, addView } from '../../api/app';
import { downloadIcon, expandIcon, minimizeIcon } from '../../images';
import { styles } from './styles';

const DocumentPreview = React.memo(({ modelProps, file, classes }) => {
  const [size, setSize] = useState('largeModal');
  const [docData, setDocData] = useState(null);
  const metadata = get(file, 'metadata', {});
  const fileExtension = get(file, 'metadata.extention', null) || getFileExtension(get(file, 'metadata.url', ''));
  const fileType = getType(fileExtension);

  useEffect(() => {
    if (fileType === 'files') {
      getUrlPreviewApi({ url: metadata.url, extension: fileExtension }).then(({ data }) => {
        setDocData(fileExtension.toLowerCase() === 'pdf' ? get(data, 'pdf', null) : get(data, 'doc', null));
      // Implementation
      }).catch(() => false);
    }
    addView(get(file, 'post_id', null)).catch(() => false);
  }, file);

  const toggleSize = () => setSize(previous => (previous === 'smallModal' ? 'largeModal' : 'smallModal'));

  const renderImage = () => (
    <Image position="center" img={get(file, 'post_file_url', null) || get(file, 'file_url', null)} round={false} size="auto" loadingClass={classes.post_image_load} extraClass={classes.imgContainer} />
  );

  const renderVideo = () => (
    // eslint-disable-next-line jsx-a11y/media-has-caption
    <video width="100%" controls>
      <source src={get(file, 'post_file_url', null) || get(file, 'file_url', null)} type={metadata.mimeType} />
                  Your browser does not support the video tag.
    </video>
  );

  const renderViewer = () => (
    // eslint-disable-next-line jsx-a11y/iframe-has-title
    <iframe src={docData} width="100%" height="670" />
  );
  return (
    <Modal
      modelProps={{ ...modelProps, dialogClassName: classes[[size]], className: 'document-preview-popup' }}
      headerText={(
        <div className={cx(classes.modalHeader, {
          [classes.smallHeader]: size === 'smallModal',
          [classes.largeHeader]: size === 'largeModal',
        })}
        >
          <div className={classes.titleContainer}>
            <Heading as="h3" headingProps={{ className: classes.fileName }}>{metadata.originalName}</Heading>
          </div>
          <MediaQuery query="(min-device-width: 767px)">
            <div className={classes.rightButtons}>
              <div className={classes.buttonContainer} onClick={() => downloadFile(get(file, 'metadata.url', ''), get(file, 'metadata.originalName', ''))}>
                <Heading as="h4" headingProps={{ className: classes.buttonStyle }}>Download</Heading>
                <Icon path={downloadIcon} />
              </div>
              <div className={classes.buttonContainer} onClick={toggleSize}>
                <Heading as="h4" headingProps={{ className: classes.buttonStyle }}>
                  {size === 'largeModal' ? 'Minimise' : 'Expand'}
                </Heading>
                <Icon path={size === 'largeModal' ? minimizeIcon : expandIcon} />
              </div>
            </div>
          </MediaQuery>
        </div>
)}
      customHeader
      headerClass={classes.mainHeader}
    >
      <div>
        <div className={classes.contentContainer}>

          <MediaQuery query="(max-device-width: 767px)">
            <div className={cx(classes.buttonContainer, 'file-download')} onClick={() => downloadFile(get(file, 'metadata.url', ''), get(file, 'metadata.originalName', ''))}>
              <Heading as="h4" headingProps={{ className: classes.buttonStyle }}>Download</Heading>
              <Icon path={downloadIcon} />
            </div>
          </MediaQuery>

          {fileType === 'images' && renderImage()}
          {fileType === 'videos' && renderVideo()}
          {(fileType === 'files' && docData) && renderViewer()}
        </div>
        {(fileType === 'files' && !docData) && <Spinner />}
      </div>
    </Modal>
  );
});

DocumentPreview.propTypes = {
  modelProps: PropTypes.object,
};

DocumentPreview.defaultProps = {
  modelProps: {},
};
export default withTheme(injectSheet(styles)(DocumentPreview));
