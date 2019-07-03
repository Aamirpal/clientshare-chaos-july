import React, { useState, useCallback } from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import take from 'lodash/take';
import get from 'lodash/get';
import classnames from 'classnames';
import {
  Icon, Heading, RoundIcon, DocumentPreview,
} from '../index';
import { closeIcon, ImageAttach, videoImg } from '../../images';
import { getType, getMbSize, downloadFile } from '../../utils/methods';
import withTheme from '../../utils/hoc/withTheme';
import { styles } from './styles';

const AttachmentTile = React.memo(({
  files, classes, onDeleteAttachment, isDelete, icon, showMore, isPreview, showIcon, extraClass,
}) => {
  const [isSeeMore, setSeeMore] = useState(files.length > 2 ? 'yes' : files.length);
  const [preview, showPreview] = useState(false);

  const seeMoreAttachments = () => {
    setSeeMore(files.length);
  };

  const getPreviewData = useCallback(
    (file) => {
      if (!isPreview) {
        return false;
      }
      const extension = get(file, 'metadata.extention', '').toLowerCase();
      if (getMbSize(10) > get(file, 'metadata.size') && (extension !== 'csv' && extension !== 'xls' && extension !== 'xlsx' && extension !== 'xlsm')) {
        return showPreview(file);
      }

      return downloadFile(get(file, 'metadata.url', ''), get(file, 'metadata.originalName', ''));
    },
    [],
  );


  const getIcon = ({ extention }) => {
    const checkType = getType(extention);
    switch (checkType) {
      case 'videos':
        return videoImg;
      case 'images':
        return ImageAttach;
      case 'files':
        return `/images/${extention}_attach.svg`;
      default:
        return icon;
    }
  };
  return (
    <div className={classnames(classes.filesContainer, 'files-container')}>
      {take(files, showMore && isSeeMore === 'yes' ? 2 : files.length).map(file => (
        <div key={file.id} className={classnames(classes.fileAttachment, extraClass)} onClick={() => getPreviewData(file)}>
          {showIcon && <Icon path={getIcon(file.metadata)} iconProps={{ className: classes.attachIcon }} />}
          <Heading as="h4" headingProps={{ className: classes.headingFileName }}>{file.metadata.originalName}</Heading>
          {isDelete && (
          <RoundIcon
            icon={closeIcon}
            iconProps={{ className: classes.cancelIcon }}
            onClick={() => onDeleteAttachment(file)}
          />
          )}
        </div>
      ))}
      {(isSeeMore === 'yes' && showMore) && <Heading as="h4" headingProps={{ className: classes.seeMore, onClick: seeMoreAttachments }}> See more attachments</Heading>}
      {(isPreview && preview)
      && (
      <DocumentPreview
        modelProps={{ show: !!preview, onHide: () => showPreview(false) }}
        file={preview}
      />
      )}

    </div>
  );
});

AttachmentTile.propTypes = {
  files: PropTypes.array.isRequired,
  classes: PropTypes.object.isRequired,
  onDeleteAttachment: PropTypes.func,
  isDelete: PropTypes.bool,
  icon: PropTypes.any,
  showMore: PropTypes.bool,
  isPreview: PropTypes.bool,
  showIcon: PropTypes.bool,
  extraClass: PropTypes.any,
};

AttachmentTile.defaultProps = {
  onDeleteAttachment: () => {},
  isDelete: false,
  icon: null,
  showMore: false,
  isPreview: false,
  showIcon: true,
  extraClass: '',
};

export default withTheme(injectSheet(styles)(AttachmentTile));
