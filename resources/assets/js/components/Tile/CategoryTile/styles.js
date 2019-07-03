export default {
  tileContainer: {
    background: ({ theme }) => theme.dusky_gray,
    borderRadius: 10,
    padding: '15px 19px',
    width: ({ theme }) => theme.full_width,
    marginBottom: 12,
    position: ({ theme }) => theme.relative,
    minHeight: 110,
    cursor: 'pointer',
    '&:focus': {
      outline: ({ theme }) => theme.none_value,
    },
  },
  topHeading: ({ theme }) => ({
    fontStyle: 'normal',
    fontWeight: 500,
    fontSize: theme.medium_font,
    lineHeight: 'normal',
    margin: '0 0 15px',
    paddingRight: '16px',
    display: theme.flex,
    alignItems: theme.center,
    justifyContent: 'flex-start',
    color: theme.basic_olor,
  }),
  categoryName: {
    width: 'calc(100% - 22px)',
  },
  subHeading: {
    fontWeight: 'normal',
    fontSize: 16,
    lineHeight: 'normal',
    color: ({ theme }) => theme.basic_color,
    margin: '0 0 2px',
  },
  highlightText: {
    color: ({ theme }) => theme.primary_color,
    marginLeft: 6,
  },
  icon: {
    marginRight: 8,
    width: 22,
  },
  bottomText: ({ theme }) => ({
    fontSize: theme.normal_font,
    lineHeight: '22px',
    color: theme.light_gray,
    margin: theme.zero_value,
  }),
  bottomEmptyCategoryText: ({ theme }) => ({
    fontSize: theme.normal_font,
    lineHeight: '17px',
    color: theme.light_gray,
    margin: theme.zero_value,
  }),
  notificationText: ({ theme }) => ({
    background: theme.alert_color,
    width: 30,
    height: 30,
    textAlign: theme.center,
    fontWeight: 500,
    fontSize: theme.normal_font,
    borderRadius: 50,
    display: theme.flex,
    color: theme.white_color,
    alignItems: theme.center,
    justifyContent: theme.center,
    position: theme.absolute,
    right: 14,
    top: 14,
  }),
  active: {
    background: ({ theme }) => theme.white_color,
    boxShadow: ({ theme }) => theme.shadow,
    '&:after': {
      content: '""',
      position: ({ theme }) => theme.absolute,
      background: ({ theme }) => theme.primary_color,
      width: 10,
      height: 'calc(100% - 18px)',
      display: 'block',
      left: '100%',
      top: 9,
      borderRadius: '0 10px 10px 0',
    },
  },
  '@media (max-width: 1440px)': {
    tileContainer: {
      padding: '17px 15px 14px 15px',
      minHeight: 102,
    },
    topHeading: {
      paddingRight: '22px',
      margin: '0 0 12px',
    },
    bottomText: {
      lineHeight: '18px',
    },
    icon: {
      marginRight: 6,
    },
    notificationText: {
      width: 24,
      height: 24,
      right: 8,
      top: 16,
    },
    active: {
      '&:after': {
        width: 6,
      },
    },
  },
  '@media (max-width: 1152px)': {
    tileContainer: {
      padding: '14px 12px 9px 12px',
      minHeight: 90,
      marginBottom: '8px',
    },
    topHeading: ({ theme }) => ({
      fontSize: theme.medium_font - 2,
      paddingRight: '20px',
      margin: '0 0 11px',
    }),
    subHeading: {
      fontSize: 14,
      margin: '0 0 4px',
    },
    bottomText: ({ theme }) => ({
      fontSize: theme.normal_font - 2,
      lineHeight: '14px',
    }),
    bottomEmptyCategoryText: ({ theme }) => ({
      fontSize: theme.normal_font - 2,
      lineHeight: '14px',
    }),
    notificationText: ({ theme }) => ({
      width: 22,
      height: 22,
      right: 6,
      fontSize: theme.normal_font - 2,
    }),
  },
  '@media (max-height: 1000px)': {
    tileContainer: {
      minHeight: 94,
      padding: '14px 12px 14px',
      marginBottom: '10px',
    },
    notificationText: ({ theme }) => ({
      right: 7,
      top: 10,
      width: 22,
      height: 22,
      fontSize: theme.normal_font - 2,
    }),
    topHeading: ({ theme }) => ({
      lineHeight: '19px',
      fontSize: theme.sixteen_font,
      margin: '0 0 12px',
    }),
    subHeading: ({ theme }) => ({
      margin: '0 0 3px',
      lineHeight: '16px',
      fontSize: theme.normal_font,
    }),
    bottomText: ({ theme }) => ({
      lineHeight: '16px',
      fontSize: theme.normal_font,
    }),
    bottomEmptyCategoryText: ({ theme }) => ({
      fontSize: theme.normal_font,
      lineHeight: '16px',
    }),
    icon: {
      width: '19px',
    },
  },
  '@media (max-width: 991px)': {
    tileContainer: {
      padding: '12px',
      minHeight: 80,
    },
    topHeading: ({ theme }) => ({
      fontSize: theme.normal_font,
      paddingRight: '16px',
    }),
    subHeading: {
      fontSize: 12,
    },
    bottomText: ({ theme }) => ({
      fontSize: theme.normal_font - 2,
    }),
    notificationText: ({ theme }) => ({
      width: 18,
      height: 18,
      fontSize: theme.normal_font - 2,
    }),
    icon: {
      width: '16px',
    },
  },
  '@media (max-height: 899px)': {
    tileContainer: {
      minHeight: 78,
    },
    topHeading: {
      margin: '0 0 11px',
    },
    bottomText: {
      display: ({ theme }) => theme.none,
    },
  },
};
