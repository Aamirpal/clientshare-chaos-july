export const styles = {
  filesContainer: {
    marginTop: 8,
  },
  fileAttachment: {
    padding: '13px 9px 12px 13px',
    display: 'flex',
    alignItems: 'center',
    width: ({ isPreview }) => (isPreview ? 592 : 500),
    border: ({ theme }) => `1px solid ${theme.dark_white}`,
    borderRadius: 4,
    marginBottom: 8,
    cursor: ({ isPreview }) => `${isPreview && 'pointer'}`,
    '@media (max-width: 991px)': {
      width: '100%',
    },
    '@media (max-width: 767px)': {
      width: ({ isPreview }) => (isPreview ? '100%' : '100%'),
    },
  },
  headingFileName: {
    display: 'flex',
    flexGrow: 1,
    fontSize: 14,
    margin: '0 29px 0 12px',
    lineHeight: '16px',
    color: ({ theme }) => theme.basic_color,
    wordBreak: 'break-all',
  },
  cancelIcon: {
    width: 10,
  },
  attachIcon: {
    width: 17,
  },
  seeMore: {
    fontWeight: 'normal',
    lineHeight: '16px',
    fontSize: 14,
    color: ({ theme }) => theme.primary_color,
    paddingTop: 6.5,
    marginBottom: 12,
    cursor: 'pointer',
  },
};
