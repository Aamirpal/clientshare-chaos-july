export const styles = {
  container: {
    padding: 17,
  },
  message: {
    fontSize: 14,
    color: ({ theme }) => theme.light_gray,
  },
  categoryContainer: {
    display: 'flex',
    flexWrap: 'wrap',
    marginTop: 16,
  },
  category_popup: {
    maxWidth: 588,
  },
  singleTileContainer: {
    padding: '16px 14px',
    borderRadius: 10,
    width: '32%',
    display: ({ theme }) => theme.flex,
    alignItems: 'flex-start',
    justifyContent: 'flex-start',
    marginBottom: 10,
    marginRight: 10,
    minHeight: 64,
    cursor: 'pointer',
    boxSizing: 'border-box',
    border: ({ theme }) => `2px solid ${theme.dusky_gray}`,
    color: ({ theme }) => theme.light_gray,
    background: ({ theme }) => theme.dusky_gray,
    '&:hover': {
      background: ({ theme }) => theme.white_color,
      border: ({ theme }) => `2px solid ${theme.primary_color}`,
      color: ({ theme }) => theme.basic_color,
    },
    '&:nth-child(3n-3)': {
      marginRight: 0,
      '@media (max-width: 767px)': {
        marginRight: 8,
      },
    },
    '&:nth-child(2n-2)': {
      '@media (max-width: 767px)': {
        marginRight: 0,
      },
    },
    '@media (max-width: 767px)': {
      width: '48%',
      marginBottom: 8,
      marginRight: 8,
    },
  },
  title: {
    fontSize: 14,
    margin: 0,
  },
  icon: {
    height: 14,
    width: 14,
    marginRight: 9,
  },
  catIcon: {
    width: 16,
    marginRight: 9,
  },
  iconGroup: {
    marginLeft: 6,
  },
  focus: {
    background: ({ theme }) => theme.white_color,
    border: ({ theme }) => `2px solid ${theme.white_color}`,
    boxShadow: '8px 8px 14px rgba(190, 197, 214, 0.2), -8px -8px 14px rgba(190, 197, 214, 0.2)',
  },
  activeCategory: {
    background: ({ theme }) => theme.white_color,
    border: ({ theme }) => `2px solid ${theme.white_color}`,
    boxShadow: '8px 8px 14px rgba(190, 197, 214, 0.2), -8px -8px 14px rgba(190, 197, 214, 0.2)',
  },
  lockIcon: {
    background: 'url(../images/lock_icon.svg) no-repeat center',
    marginLeft: 6,
    width: 12,
    height: 14,
  },
  globeIcon: {
    background: 'url(../images/globe.svg) no-repeat center',
    marginLeft: 6,
    width: 14,
    height: 14,
  },
  groupTile: {
    maxWidth: '132px',
    minWidth: '132px',
    padding: '10px',
    borderRadius: '10px',
    display: 'flex !important',
    flexDirection: 'column',
    justifyContent: 'space-between',
    minHeight: '72px',
    position: 'relative',
    marginRight: '8px',
    marginBottom: '8px',
    border: ({ theme }) => `2px solid ${theme.light_green}`,
    background: ({ theme }) => theme.light_green,
    color: ({ theme }) => theme.primary_color,
    cursor: 'pointer',
    '@media (max-width: 767px)': {
      minWidth: '48%',
      minHeight: '56px',
    },
    '&:nth-child(4n)': {
      margin: 0,
      marginBottom: 8,
    },
    '&:nth-child(2n-2)': {
      '@media (max-width: 767px)': {
        marginRight: 0,
      },
    },
    '&:hover': {
      background: ({ theme }) => theme.primary_color,
      border: ({ theme }) => `2px solid ${theme.primary_color}`,
      '& h5': {
        color: ({ theme }) => theme.white_color,
      },
      '& p': {
        color: ({ theme }) => theme.white_color,
      },
    },
  },
  focusGroup: {
    background: ({ theme }) => theme.white_color,
    border: ({ theme }) => `2px solid ${theme.primary_color}`,
    boxShadow: '8px 8px 14px rgba(190, 197, 214, 0.2), -8px -8px 14px rgba(190, 197, 214, 0.2)',
    '& h5': {
      color: ({ theme }) => theme.basic_color,
    },
  },
  selectedGroup: {
    background: ({ theme }) => theme.white_color,
    border: ({ theme }) => `2px solid ${theme.primary_color}`,
    '& h5': {
      color: ({ theme }) => theme.basic_color,
    },
  },
  memberCount: () => ({
    fontWeight: 'normal',
    fontSize: ({ theme }) => theme.normal_font - 2,
    lineHeight: 'normal',
    textAlign: 'right',
    margin: 0,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
  }),
  memberCountMain: {
    '@media (max-width: 767px)': {
      fontSize: 12,
    },
  },
  groupsContainer: {
    display: ({ theme }) => theme.flex,
    flexWrap: 'wrap',
    marginTop: 16,
    width: '100%',
    position: 'relative',
  },
  groupHeading: ({ theme }) => ({
    fontWeight: 500,
    fontSize: theme.normal_font,
    lineHeight: 'normal',
    margin: 0,
    paddingRight: 8,
  }),
  addPostContainer: {
    padding: '15px 32px 17px 15px',
  },
  addPostPopup: {
    maxWidth: 735,
  },
  topPanel: {
    display: ({ theme }) => theme.flex,
  },
  inputContainer: {
    flexGrow: 1,
    padding: '0 22px',
    '@media (max-width: 767px)': {
      padding: 0,
    },

  },
  postDescription: {
    width: ({ theme }) => theme.full_width,
    border: ({ theme }) => theme.none_value,
    resize: ({ theme }) => theme.none_value,
    outline: ({ theme }) => theme.none_value,
    boxShadow: ({ theme }) => theme.none_value,
    fontSize: 14,
  },
  postInput: {
    width: ({ theme }) => theme.full_width,
    fontSize: 18,
    lineHeight: '21px',
    marginBottom: 10,
  },
  dateInput: {
    fontSize: 14,
    marginBottom: 10,
    lineHeight: '16px',
    border: ({ theme }) => theme.none_value,
    width: ({ theme }) => theme.full_width,
    outline: ({ theme }) => theme.none_value,
    '@media (max-width: 767px)': {
      fontSize: 18,
      marginBottom: 20,
    },
  },
  error: {
    color: ({ theme }) => theme.black_color,
  },
  imagesContainer: {
    display: ({ theme }) => theme.flex,
    flexWrap: 'wrap',
  },
  singleImage: {
    margin: '0 10px 5px 0',
    position: ({ theme }) => theme.relative,
    marginRight: 10,
  },
  attImage: {
    width: 131,
    height: 131,
    objectFit: 'cover',
    borderRadius: 4,
    objectPosition: ({ theme }) => theme.center,
  },
  imageDeleteIcon: {
    top: 10,
    right: 10,
    cursor: 'pointer',
    position: ({ theme }) => theme.absolute,
  },
  cancelIcon: {
    width: 10,
  },
};
