import React, { Component } from 'react'
import PropTypes from 'prop-types'

import { Button } from 'reactstrap'
import { Video } from 'react-feather'

import moment from 'moment'
import { confirmAlert } from '../Confirm/ConfirmWrapper'

class PostList extends Component {
    constructor(props) {
        super(props)
    }
    render() {
        const postGroups = this.postGroups
        
        return (    
            <table className="table post-list">
                <thead className="thead-light">
                    <tr>
                        <th scope="col">Date Scheduled</th>
                        <th scope="col">Scheduled Time</th>
                        <th scope="col">Message</th>
                        <th scope="col">Status</th>
                        <th scope="col">Image/Link</th>
                        <th scope="col">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    {postGroups.map(group => {
                        return group.map((post,i) => (
                            <tr key={post.id}>
                                {
                                    i == 0 && 
                                    <td rowSpan={group.length} className="agenda">
                                        <div className="dayofmonth">{moment(post.scheduled_at_tz).format("DD")}</div>
                                        <div className="dayofweek">{moment(post.scheduled_at_tz).format("ddd")}</div>
                                        <div className="shortdate text-muted">{moment(post.scheduled_at_tz).format("MMM YYYY")}</div>
                                    </td>
                                }
                                <td>
                                    {post.scheduled_at ? moment(post.scheduled_at_tz).format("HH:mm") : ''}
                                </td>
                                <td>{post.message}</td>
                                <td>
                                    {post.status_text == 'published' ? 
                                    <a target="_blank" href={post.fb_post_link}>
                                        {post.status_text}
                                    </a>
                                    : post.status_text
                                    }
                                </td>
                                <td>
                                    {post.type_text == 'photo' && 
                                        <img src={post.media_url} />}
                                    {post.type_text == 'video' && 
                                        <Video />}
                                    {post.type_text == 'link' && 
                                    <a href={post.link}>{post.link}</a>
                                    }
                                </td>
                                <td>
                                    {post.status_text == "not publish" &&
                                    <React.Fragment>
                                        <a href="#"
                                            onClick={() => { this.props.onSelect(post.id)}}
                                        >
                                            Edit
                                        </a>&nbsp;
                                        <Button color="danger"
                                            onClick={() => this.handlDeleteClick(post)}
                                        >
                                            Delete
                                        </Button>
                                    </React.Fragment>}
                                </td>
                            </tr>
                        ))
                    })}
                </tbody>
            </table>
        );
    }

    get postGroups() 
    {
        const { posts } = this.props
        let dateDict = {}

        posts.forEach(post => {
            const date = moment(post.scheduled_at_tz).format("Y-M-D")
            if (!dateDict.hasOwnProperty(date))
                dateDict[date] = []
            dateDict[date].push(post)
        })

        return Object.values(dateDict)
    }

    handlDeleteClick = (post) => {
        confirmAlert({
            content: () => ("Delete cannot be undone. Do you want to continute?"),
            buttons: [
                {
                    label: "No",
                    color: "secondary"
                },
                {
                    label: "Yes",
                    color: "primary",
                    onClick: () => this.props.onDelete(post.id)
                }
            ]
        })
    }
}

PostList.propTypes = {
    posts: PropTypes.arrayOf(PropTypes.object).isRequired,
    onDelete: PropTypes.func.isRequired,
    onSelect: PropTypes.func.isRequired
}

export default PostList
