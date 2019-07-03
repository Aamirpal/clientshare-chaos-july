import React, { Component } from 'react';
import { getPosts } from '../api/app';

export default class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      data: [],
    };
  }

  componentDidMount() {
    getPosts({
      space_id: '74f6c375-c73f-44a8-8a4a-cf8fb4fdb3c6',
      offset: 0,
      limit: 10,
    }).then((res) => {
      this.setState({ data: res.data });
    }).catch(() => {
    });
  }

  render() {
    const { data } = this.state;
    return (
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-4  mt-3 mb-4">

            <div className="card-header ">categories</div>
            <div className="card text-center ">general</div>
            <div className="card text-center">general</div>
            <div className="card text-center">general</div>
            <div className="card text-center">general</div>
          </div>

          <div className="col-8  mt-3 mb-4">
            {' '}
            <div className="card-header">RECENT 10 POSTS</div>
            {data.map(post => (
              <div key={post.id} className="card text-center ">
                <p>{post.post_subject}</p>
              </div>

            ))}

          </div>
        </div>

      </div>
    );
  }
}
