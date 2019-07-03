export const styles = {
  modalContainer: {
    padding: '14px 0 8px',
    position: 'relative',
    overflow: 'hidden',
    minHeight: '290px',
  },
  twitterDescription: {
    color: ({ theme }) => theme.basic_color,
    fontSize: '15px',
    lineHeight: '19px',
    paddingRight: 15,
    margin: 0,
  },
  twitterAlert: {
    borderRadius: 6,
    background: 'rgba(217, 247, 239, 0.6)',
    margin: '18 0',
    padding: '12px 13px',
    '& span': {
      display: 'flex',
      alignSelf: 'flex-start',
    },
    '& p': {
      color: ({ theme }) => theme.primary_color,
      fontSize: ({ theme }) => theme.normal_font,
      lineHeight: 'normal',
      marginLeft: 10,
    },
  },
  inputClose: {
    position: ({ theme }) => theme.absolute,
    right: 11,
    top: 18,
    cursor: 'pointer',
  },
  addFeedBtn: {
    background: 'transparent',
    padding: 0,
    height: 'auto',
    fontSize: ({ theme }) => theme.normal_font,
    lineHeight: '22px',
    color: ({ theme }) => theme.primary_color,
    fontWeight: '400',
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'flex-start',
    cursor: 'pointer',
    '& img': {
      marginRight: '8px',
    },
  },
  btnWrap: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginTop: '20px',
  },
  cancelBtn: {
    background: 'transparent',
    height: 'auto',
    padding: 0,
    marginRight: '12px',
    color: ({ theme }) => theme.light_gray,
    fontWeight: '500',
    '&:hover': {
      color: ({ theme }) => theme.light_gray,
      background: 'transparent',
    },
  },
  errorMessage: ({ theme }) => ({
    fontSize: '12px',
    lineHeight: '16px',
    color: theme.alert_color,
    marginTop: '5px',
  }),
};
