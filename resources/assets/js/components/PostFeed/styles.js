export const styles = {
  mainContainer: {
    background: ({ theme }) => theme.white_color,
    borderRadius: 10,
    border: ({ theme, single }) => (single ? 0 : `1px solid ${theme.dark_white}`),
    position: 'relative',
    '@media (max-width: 767px)': {
      borderRadius: 0,
      border: 'none !important',
    },
  },
  pinFeedContainer: {
    boxShadow: ({ theme }) => theme.shadow,
  },
  container: {
    padding: 15,
  },
  topContainer: {
    display: 'flex',
  },
  midTextContainer: {
    marginLeft: 16,
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'center',
    width: '89%',
    paddingRight: '16px',
  },
  nameContainer: {
    marginBottom: 9,
    display: 'flex',
    justifyContent: 'space-between',
    '@media (max-width: 767px)': {
      marginBottom: 1,
    },
  },
  name: {
    lineHeight: '19px',
    color: ({ theme }) => theme.basic_color,
    margin: 0,
    '@media (max-width: 767px)': {
      width: '100%',
    },
  },
  bottomName: {
    fontSize: 12,
    color: ({ theme }) => theme.light_gray,
    lineHeight: '16px',
    display: 'flex',
  },
  lockIcon: {
    width: 10,
    height: 13,
    marginRight: 7,
  },
  date: {
    marginRight: 17,
  },
  bottomContainer: {
    margin: '13px 0px 0px 2px',
    color: ({ theme }) => theme.basic_color,
  },
  subject: {
    lineHeight: '21px',
    marginBottom: 14,
  },
  description: {
    fontSize: 14,
    fontWeight: 'normal',
    lineHeight: '22px',
    margin: 0,
    wordBreak: 'break-word',
    wordWrap: 'break-word',
  },
  likeContainer: {
    display: 'flex',
    padding: '14px 17px',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderTop: ({ theme }) => `1px solid ${theme.dark_white}`,
  },
  likeButton: {
    cursor: 'pointer',
  },
  otherMembers: {
    margin: 0,
    cursor: 'pointer',
    fontSize: 14,
    color: ({ theme }) => theme.primary_color,
    '@media (max-width: 767px)': {
      fontSize: 12,
    },
  },
  onlyFlex: {
    display: 'flex',
    alignItems: 'center',
    minHeight: 24,
  },
  onlyFlexInner: {
    display: 'flex',
    marginLeft: '16px',
    '@media (max-width: 767px)': {
      marginLeft: '10px',
    },
  },
  likeInner: {
    marginLeft: 11,
    display: 'flex',
    '@media (max-width: 767px)': {
      marginLeft: 8,
    },
  },
  viewsText: {
    margin: '0 0 0 10px',
    fontSize: 14,
    color: ({ theme }) => theme.primary_color,
    cursor: 'pointer',
  },
  commentsText: {
    margin: '0 0 0 10px',
    fontSize: 14,
    color: ({ theme }) => theme.primary_color,
    '@media (max-width: 767px)': {
      fontSize: 12,
    },
  },
  usefulText: {
    margin: 0,
    fontSize: 14,
    color: ({ theme }) => theme.basic_color,
    '@media (max-width: 767px)': {
      fontSize: 12,
    },
  },
  commentContainer: {
    borderTop: ({ theme }) => `1px solid ${theme.dark_white}`,
    padding: 17,
  },
  addCommentContainer: {
    display: 'flex',
    position: 'relative',
    '@media (max-width: 767px)': {
      alignItems: 'flex-start',
    },
  },
  commentAvatar: {
    marginTop: 5,
    '@media (max-width: 767px)': {
      marginTop: 3,
    },
  },
  postfeedDropdown: {
    cursor: 'pointer',
    padding: 15,
    position: 'absolute',
    right: -15,
    '&:after': {
      display: 'none',
    },
    '@media (max-width: 767px)': {
      padding: '5px 15px',
      right: 15,
    },
  },
  attachmentContainer: {
    padding: '0 17px',
  },
  imageContainer: {
    padding: '3px 0 2px 0',
  },
  post_image: {
    height: 'auto',
    borderRadius: 4,
    maxWidth: '100%',
    width: 'auto',
    cursor: 'pointer',
  },
  post_image_load: {
    height: 300,
    background: ({ theme }) => theme.light_gray,
    borderRadius: 4,
  },
  postLink: {
    height: 0,
    position: 'absolute',
    zIndex: -1,
  },
  pinContainer: {
    position: 'absolute',
    top: 0,
    right: 78,
    background: ({ theme }) => theme.basic_color,
    color: '#fff',
    display: 'flex',
    borderBottomLeftRadius: 6,
    borderBottomRightRadius: 6,
    padding: '8px 15px 10px 13px',
    alignItems: 'center',
    '@media (max-width: 767px)': {
      padding: 6,
      right: 65,
    },
  },
  pinIcon: {
    width: 14,
    marginRight: 8,
    '@media (max-width: 767px)': {
      marginRight: 0,
      width: 12,
    },
  },
  pinText: {
    margin: 0,
    fontSize: 12,
    lineHeight: '14px',
    '@media (max-width: 767px)': {
      display: 'none',
    },
  },
  parentDropdown: {
    width: 123,
    '@media (max-width: 767px)': {
      position: 'static',
      width: 'auto',
    },
  },
  viewIcon: {
    height: 16,
    width: 16,
  },
  viewItem: {
    fontSize: 16,
    lineHeight: '19px',
    width: 190,
    padding: '11px 12px',
    textAlign: 'left',
    borderBottom: ({ theme }) => `1px solid ${theme.dark_navy}`,
    '&:last-child': {
      border: 'none',
    },
  },
  usersModal: {
    maxWidth: 316,
  },
  allCommentContainer: {
    display: 'flex',
    alignItems: 'flex-start',
    marginBottom: '18px',
  },
  commentDetails: {
    position: 'relative',
    width: '95%',
    marginLeft: '12px',
  },
  commentTextContainer: {
    fontSize: '14px',
    color: ({ theme }) => theme.basic_color,
    lineHeight: '16px',
    background: ({ theme }) => theme.ghost_white,
    borderRadius: '16px',
    width: 'auto',
    display: 'inline-flex',
    flexDirection: 'column',
  },
  commentText: {
    color: ({ theme }) => theme.basic_color,
    margin: 0,
    padding: '10px 12px 9px',
  },
  userName: {
    fontWeight: '500',
    color: ({ theme }) => theme.primary_color,
    cursor: 'pointer',
  },
  sharedFileName: {
    fontWeight: '500',
    color: ({ theme }) => theme.primary_color,
    margin: 0,
    padding: '9px 12px 10px',
    borderTop: ({ theme }) => `1px solid ${theme.dark_white}`,
    cursor: 'pointer',
    '@media (max-width: 767px)': {
      wordBreak: 'break-word',
    },
  },
  commentInfoContainer: {
    display: 'flex',
    padding: '8px 18px 0',
    color: ({ theme }) => theme.light_gray,
    '@media (max-width: 767px)': {
      padding: '8px 10px 0',
    },
    '& p': {
      fontSize: '12px',
      lineHeight: '14px',
      margin: 0,
      paddingRight: '20px',
      position: 'relative',
      '@media (max-width: 767px)': {
        fontSize: '11px',
      },
    },
  },
  userIcon: {
    maxWidth: '36px',
    width: '100%',
    cursor: 'pointer',
  },
  tagItemHover: {
    background: 'red',
  },
  seeMoreCommentsWrap: {
    color: ({ theme }) => theme.primary_color,
    fontSize: '14px',
    lineHeight: '16px',
    marginBottom: '20px',
    cursor: 'pointer',
    display: 'inline-flex',
  },
  commentDescription: {
    wordBreak: 'break-word',
  },
  pointer: {
    cursor: 'pointer',
  },
};
