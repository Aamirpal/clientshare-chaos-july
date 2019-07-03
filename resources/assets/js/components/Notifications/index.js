import React, { useState, useEffect } from 'react';
import { Scrollbars } from 'react-custom-scrollbars';
import InfiniteScroll from 'react-infinite-scroller';
import { getNotifications } from '../../api/app';
import { globalConstants, API_URL } from '../../utils/constants';
import loadingIcon from '../../images/loader.svg';
import './style.css';

import Image from '../Image';

const { clientShareId, userId } = globalConstants;


const Notifications = () => {
  const [notificationResponse, setNotificationsResponse] = useState({
    notifications: [],
    notification_messages: {},
    offset: 0,
  });
  const { notifications, notification_messages } = notificationResponse;
  const [isLoad, setload] = useState(true);
  const [isApiCall, setAPICall] = useState(false);
  const getNotificationsData = () => {
    getNotifications(clientShareId, userId, notificationResponse.offset).then(({ data }) => {
      setNotificationsResponse({
        ...data,
        notifications: [
          ...notificationResponse.notifications,
          ...data.notifications,
        ],
      });
      setload(false);
      setAPICall(true);
    }).catch(() => {});
  };

  useEffect(() => {
    getNotificationsData();
    setInterval(() => {
      if (isApiCall && notifications.length) {
        setload(true);
      }
    }, 5000);
  }, {});


  return (
    <section>
      <ul>
        <li className="static-mobile">
          Notifications
        </li>
        <Scrollbars className="scrol-dropdown" style={{ height: notifications.length ? 323 : 0 }}>
          <InfiniteScroll
            pageStart={0}
            loadMore={getNotificationsData}
            hasMore={isLoad}
            loader={<div className="review-post-loader text-center" key={0}><img width="40" src={loadingIcon} alt="Loading..." /></div>}
            useWindow={false}
            threshold={10}
          >
            {!!notifications.length && notifications.map(notification => (
              <li className="dropdown-item" key={notification.id}>
                <a href={`${API_URL}/clientshare/${clientShareId}/${notification.post_id}`}>
                  <div className="notify-user-profile">
                    <span className="notify-user-profile-img">
                      <Image img={notification.profile_image} extraClass="notify-user-pic" size="medium" />
                    </span>
                  </div>
                  <div className="notify-user-detail">
                    <h6>{`${notification.first_name} ${notification.last_name}`}</h6>
                    <p>{notification_messages[notification.notification_type]}</p>
                  </div>
                </a>
              </li>
            ))}
          </InfiniteScroll>
        </Scrollbars>
        {Boolean(!notifications.length) && (
        <li className="no_notifications">
          <div className="notify-user-detail">
            <h6>No Notification Found</h6>
          </div>
        </li>
        )}
      </ul>
    </section>

  );
};

export default Notifications;
