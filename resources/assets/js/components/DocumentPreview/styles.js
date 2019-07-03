export const styles = {
  mainHeader: {
    padding: 0,
  },
  modalHeader: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  smallHeader: {
    width: '95%',
  },
  largeHeader: {
    width: '97.2%',
  },
  smallModal: {
    minWidth: 587,
  },
  largeModal: {
    maxWidth: 1280,
    width: '94%',
    '@media (max-width: 767px)': {
      minWidth: '100%',
    },
  },
  titleContainer: {
    width: '50%',
  },
  fileName: {
    margin: 0,
    color: ({ theme }) => theme.basic_color,
    lineHeight: '21px',
    width: '100%',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
    display: 'inline-block',
    whiteSpace: 'nowrap',
  },
  rightButtons: {
    display: 'flex',
  },
  buttonContainer: {
    position: ({ theme }) => theme.relative,
    display: 'flex',
    padding: '0 20px 0 15px',
    '&:after': {
      position: ({ theme }) => theme.absolute,
      content: '""',
      background: ({ theme }) => theme.dark_white,
      width: 1,
      height: 54,
      top: -18,
      right: 0,
      bottom: 0,
    },
  },
  buttonStyle: {
    color: ({ theme }) => theme.primary_color,
    margin: '0 7px 0 0',
    lineHeight: '19px',
    cursor: 'pointer',
  },
  post_image_load: {
    height: 500,
    background: ({ theme }) => theme.light_gray,
    borderRadius: 4,
    '@media (max-width: 767px)': {
      height: 300,
    },
  },
  contentContainer: {
    padding: 17,
  },
  imgContainer: {
    height: 'auto',
    borderRadius: 8,
    width: 'auto',
    maxWidth: '100%',
  },
};
