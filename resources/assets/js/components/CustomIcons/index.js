import React from 'react';

const CustomIcons = ({
  classes,
  iconProps,
  icon,
  ...rest
}) => {
  switch (icon) {
    case 'globe':
      return (
        <svg {...rest} width="14" height="14" className={iconProps.className} viewBox="0 0 14 14" fill="transparent" xmlns="http://www.w3.org/2000/svg">
          <path d="M7.06117 7.487e-08C4.35576 -0.000408258 1.91067 1.66947 0.845824 4.2448L0.76155 4.27373L0.794223 4.37523C-0.607801 7.95591 1.05879 12.0355 4.5167 13.4873C5.32433 13.8264 6.18766 14.0005 7.05917 14C10.7926 14.0006 13.8196 10.867 13.8202 7.00105C13.8207 3.13507 10.7946 0.000554241 7.06117 7.487e-08ZM12.3636 10.5293L12.0165 10.1701V9.33333C12.0169 9.29722 12.0092 9.26153 11.994 9.22903L11.1152 7.4116V6.53333C11.1152 6.45534 11.0776 6.38248 11.0149 6.3392L10.3389 5.87253C10.2859 5.8359 10.2204 5.8242 10.1586 5.84033L9.33075 6.05453L7.94022 5.43667L7.74891 4.0488L8.27912 3.5H9.41728L9.80035 4.09617C9.83494 4.14978 9.88944 4.1862 9.95087 4.19673L11.3029 4.43007C11.3391 4.43637 11.3762 4.4333 11.411 4.4212L12.6562 3.9914C13.7006 6.06675 13.5889 8.56287 12.3636 10.5293ZM11.4342 2.29927L11.0678 2.5522L10.0464 2.34057L9.38371 2.11213C9.34315 2.09787 9.29949 2.09606 9.25797 2.107L8.42018 2.32377L8.05289 2.19707L8.32509 1.63333H9.08717C9.12212 1.63336 9.1566 1.62499 9.18789 1.60883L9.96755 1.20517C10.5045 1.49537 10.9984 1.86381 11.4342 2.29927ZM4.59109 0.9877L5.13189 1.36103C5.15848 1.37935 5.18851 1.3916 5.22 1.39697L6.31196 1.5855L6.20538 1.91567L5.63641 2.11237C5.58546 2.12993 5.54222 2.16574 5.51451 2.2134L4.86735 3.33013L3.82789 3.976L2.29562 4.20257C2.18456 4.21881 2.10197 4.31719 2.10183 4.43333V5.13333C2.10183 5.19522 2.12561 5.25455 2.16786 5.2983L2.5525 5.6966V6.09747L1.61511 5.4502L1.27508 4.3939C1.92202 2.86323 3.10775 1.64523 4.59109 0.9877ZM3.94957 8.638L2.93106 8.42683L2.5525 7.64493V7.0966L3.39209 6.2272L3.70283 6.87097C3.74099 6.95007 3.8191 7.00003 3.9045 7H5.3543L5.96472 8.0535C6.00545 8.1237 6.07874 8.16664 6.15783 8.16667H6.78426L6.62653 8.98497L5.77342 9.86837C5.73108 9.91209 5.70725 9.97141 5.70717 10.0333V10.6167L4.89597 11.2467C4.83924 11.2907 4.80583 11.3599 4.80583 11.4333V12.2761L4.52552 12.18L4.12983 11.1552V8.86667C4.12986 8.75577 4.05449 8.66017 3.94957 8.638ZM3.27221 12.2249C1.14763 10.5744 0.249142 7.72263 1.02496 5.09227L1.21222 5.67373C1.22799 5.72294 1.25914 5.76532 1.30077 5.79413L2.42406 6.5695L2.16786 6.83503C2.12561 6.87878 2.10183 6.93811 2.10183 7V7.7C2.1018 7.7362 2.10989 7.7719 2.12549 7.8043L2.57616 8.73763C2.60737 8.80209 2.66559 8.848 2.73389 8.862L3.68029 9.05777V11.2C3.68026 11.2296 3.6857 11.259 3.69629 11.2866L4.14696 12.4532C4.17163 12.517 4.22216 12.5662 4.28509 12.5879L4.96109 12.8212C4.98368 12.8291 5.00734 12.8332 5.03117 12.8333C5.15561 12.8333 5.2565 12.7289 5.2565 12.6V11.55L6.0677 10.92C6.12443 10.8759 6.15783 10.8068 6.15783 10.7333V10.1299L6.99314 9.26497C7.02455 9.23239 7.04596 9.19088 7.05466 9.14573L7.27999 7.97907C7.30439 7.85269 7.22524 7.72978 7.10319 7.70452C7.08869 7.70152 7.07396 7.7 7.05917 7.7H6.28537L5.67494 6.6465C5.63422 6.57629 5.56093 6.53336 5.48183 6.53333H4.04376L3.65663 5.72903C3.62421 5.66189 3.56258 5.61499 3.49101 5.60303C3.41947 5.59023 3.34643 5.61467 3.29565 5.66837L3.00317 5.97007V5.6C3.00317 5.53811 2.97939 5.47878 2.93714 5.43503L2.5525 5.03673V4.63563L3.9365 4.431C3.96621 4.42654 3.9948 4.41598 4.02055 4.39997L5.14721 3.69997C5.17882 3.68031 5.20529 3.65289 5.22428 3.62017L5.85746 2.52747L6.45437 2.3212C6.52178 2.2983 6.57467 2.24356 6.59678 2.17373L6.82212 1.47373C6.86116 1.35138 6.79702 1.2194 6.67886 1.17897C6.66796 1.17524 6.65678 1.17238 6.64546 1.1704L5.34123 0.945233L5.11206 0.787033C6.53006 0.309108 8.06587 0.368258 9.44567 0.953867L9.03399 1.16667H8.18583C8.10001 1.16617 8.0214 1.21622 7.98303 1.2957L7.53237 2.22903C7.47685 2.34436 7.52212 2.48448 7.63349 2.54196C7.64298 2.54686 7.65278 2.55106 7.66284 2.55453L8.33884 2.78787C8.3794 2.80213 8.42305 2.80394 8.46457 2.793L9.30236 2.57623L9.91617 2.78787C9.92496 2.79113 9.934 2.79364 9.94321 2.79533L11.0699 3.02867C11.1285 3.04103 11.1894 3.02861 11.2391 2.99413L11.752 2.63993C12.0044 2.93105 12.2312 3.24488 12.4298 3.5777L11.3211 3.96037L10.1208 3.75317L9.72418 3.13717C9.68261 3.07262 9.61276 3.03371 9.53784 3.03333H8.18583C8.12606 3.03333 8.06877 3.05795 8.02652 3.1017L7.35052 3.8017C7.30033 3.85365 7.2767 3.92706 7.28675 3.9998L7.51209 5.63313C7.52344 5.7139 7.5747 5.78264 7.64729 5.81443L9.22462 6.51443C9.2698 6.5347 9.32022 6.53887 9.36793 6.52633L10.1726 6.31797L10.6645 6.65817V7.46667C10.6641 7.50277 10.6718 7.53848 10.687 7.57097L11.5658 9.3884V10.2667C11.5658 10.3286 11.5896 10.3879 11.6319 10.4316L12.1033 10.92C10.0126 13.8055 6.05883 14.3898 3.27221 12.2249Z" fill="#2CCDA0" />
        </svg>
      );
    case 'lock':
      return (
        <svg {...rest} width="12" height="14" className={iconProps.className} viewBox="0 0 12 14" fill="transparent" xmlns="http://www.w3.org/2000/svg">
          <path d="M10.1888 5.21448V3.75926C10.1888 1.68648 8.3278 0 6.04062 0C3.75343 0 1.89247 1.68648 1.89247 3.75926V5.21448C1.01073 5.36641 0.336914 6.13485 0.336914 7.05963V12.1261C0.336914 13.1592 1.17769 14 2.2111 14H9.87014C10.9035 14 11.7443 13.1592 11.7443 12.1258V7.05937C11.7443 6.13485 11.0705 5.36641 10.1888 5.21448ZM2.41099 3.75926C2.41099 1.97219 4.03914 0.518519 6.04062 0.518519C8.0421 0.518519 9.67025 1.97219 9.67025 3.75926V5.18518H2.41099V3.75926ZM11.2258 12.1258C11.2258 12.8733 10.6176 13.4815 9.87014 13.4815H2.2111C1.46365 13.4815 0.855433 12.8733 0.855433 12.1258V7.05937C0.855433 6.31193 1.46365 5.7037 2.2111 5.7037H9.87014C10.6176 5.7037 11.2258 6.31193 11.2258 7.05937V12.1258Z" fill="#2CCDA0" />
          <path d="M6.04045 7.25781C5.46853 7.25781 5.00342 7.72292 5.00342 8.29485V9.8504C5.00342 10.4223 5.46853 10.8874 6.04045 10.8874C6.61238 10.8874 7.07749 10.4223 7.07749 9.8504V8.29485C7.07749 7.72292 6.61238 7.25781 6.04045 7.25781ZM6.55897 9.8504C6.55897 10.1364 6.32642 10.3689 6.04045 10.3689C5.75449 10.3689 5.52194 10.1364 5.52194 9.8504V8.29485C5.52194 8.00889 5.75449 7.77633 6.04045 7.77633C6.32642 7.77633 6.55897 8.00889 6.55897 8.29485V9.8504Z" fill="#2CCDA0" />
        </svg>
      );
    case 'addIconSmall':
      return (
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14.55 8C14.55 11.6175 11.6175 14.55 8 14.55C4.38253 14.55 1.45 11.6175 1.45 8C1.45 4.38253 4.38253 1.45 8 1.45C11.6175 1.45 14.55 4.38253 14.55 8ZM8 15.1C11.9212 15.1 15.1 11.9212 15.1 8C15.1 4.07878 11.9212 0.9 8 0.9C4.07878 0.9 0.9 4.07878 0.9 8C0.9 11.9212 4.07878 15.1 8 15.1Z" fill="#2CCDA0" stroke="#2CCDA0" strokeWidth="0.2" />
          <path d="M8 4V12" stroke="#2CCDA0" strokeWidth="0.8" strokeLinecap="round" strokeLinejoin="round" />
          <path d="M4 8H12" stroke="#2CCDA0" strokeWidth="0.8" strokeLinecap="round" strokeLinejoin="round" />
        </svg>

      );
    default:
  }
};

export default CustomIcons;
