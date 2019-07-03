export const styles = {
  reviewListContainer: {
    margin: '12px 0px 10px 0px',
  },
  mainContainer: {
    background: ({ theme }) => theme.white_color,
    borderRadius: 10,
    border: ({ theme, single }) => (single ? 0 : `1px solid ${theme.dark_white}`),
    position: 'relative',
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
  },
  name: {
    lineHeight: '19px',
    color: ({ theme }) => theme.basic_color,
    margin: 0,
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
  },
  likeContainer: {
    display: 'flex',
    padding: '14px 17px',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderTop: ({ theme }) => `1px solid ${theme.dark_white}`,
  },
  onlyFlex: {
    display: 'flex',
  },
  likeText: {
    margin: '0 0 0 11px',
    fontSize: 14,
    color: ({ theme }) => theme.primary_color,
  },
  viewsText: {
    margin: '0 0 0 10px',
    fontSize: 14,
    color: ({ theme }) => theme.primary_color,
  },
  usefulText: {
    margin: 0,
    fontSize: 14,
    color: ({ theme }) => theme.basic_color,
  },
  commentContainer: {
    display: 'flex',
    padding: 17,
    alignItems: 'center',
    borderTop: ({ theme }) => `1px solid ${theme.dark_white}`,
  },
  inputContainer: {
    position: 'relative',
    width: '95%',
  },
  commentInput: {
    marginLeft: 12,
    width: '100%',
    fontSize: 14,
    lineHeight: '22px',
    color: ({ theme }) => theme.basic_color,
    background: ({ theme }) => theme.ghost_white,
    padding: '7px 120px 7px 16px',
    borderRadius: 18,
    minHeight: '40px',
    border: '0px',
    display: 'flex',
    alignItems: 'center',
  },
  button: {
    color: ({ theme }) => theme.light_gray,
    fontSize: 14,
    lineHeight: '22px',
    padding: '10px 16px',
    background: 'transparent',
    fontWeight: 'normal',
    height: 'auto',
    '&:hover': {
      background: 'transparent',
    },
  },
  buttonContainer: {
    position: 'absolute',
    right: 0,
    bottom: 0,
  },
  postfeedDropdown: {
    cursor: 'pointer',
    padding: 15,
    position: 'absolute',
    right: -15,
    '&:after': {
      display: 'none',
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
  },
  pinIcon: {
    width: 14,
    marginRight: 8,
  },
  pinText: {
    margin: 0,
    fontSize: 12,
    lineHeight: '14px',
  },
  parentDropdown: {
    width: 123,
  },
  viewIcon: {
    height: 16,
    width: 16,
  },

};
