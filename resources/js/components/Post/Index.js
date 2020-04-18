import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import { Plus } from 'react-feather'

import { getPagePost, deletePost } from '../Service/Post'

import UpdateControl from './UpdateControl'
import CreateControl from './CreateControl'
import PostList from './List'

class PostIndex extends Component {
    constructor(props) {
        super(props)
        this.state = {
            modal: false,
            posts : []
        }
    }
    render() {
        const { page } = this.props
        const { posts } = this.state
        
        return (    
            <div className="container">
                <div className="row">
                    <div className="col-6 mb-2">  
                        <h2><img className="fb-s-logo" src={`http://graph.facebook.com/${page.fb_id}/picture?type=square`}/> {page.name}</h2>
                    </div>
                    <div className="text-right col-6 mb-3">
                        <a className="btn btn-default" href="#"
                            onClick={() => this.setState({modal: true})}
                        >
                            <Plus /> 
                            Add post
                        </a>
                    </div>
                </div>

                <div className="row">
                    <div className="col-md-12">
                        <PostList 
                            posts={posts} 
                            onDelete={this.handleDelete}
                            onSelect={this.handleSelect}
                        />                            
                    </div>
                </div>

                {this.state.modal && 
                <CreateControl 
                    page={page}
                    onAdded={this.handleAddPost}
                /> 
                }  

                {this.state.selected_post_id &&
                <UpdateControl
                    page={page}
                    onUpdated={this.handleUpdatePost}
                    post={this.selected_post}
                />
                } 
            </div>
        );
    }

    get selected_post() {
        return this.state.posts.filter(post => post.id == this.state.selected_post_id)[0]
    }

    handleAddPost = (post) => {
        if (!post) {
            this.setState({modal : false})
            return;
        }
        
        let n_posts = this.state.posts.slice(0);
        n_posts.splice(0,0,post);
        
        this.setState({
            posts: n_posts
        })
    }

    handleDelete = (post_id) => {
        deletePost(post_id)
            .then(resp => {
                let n_posts = this.state.posts.filter(post => post.id != post_id)
                this.setState({posts : n_posts})
            })
    }

    handleSelect = (post_id) => {
        this.setState({
            selected_post_id : post_id
        })
    }

    handleUpdatePost = (post) => {
        if (!post) {
            this.setState({selected_post_id : null})
            return;
        }

        let n_posts = this.state.posts.slice(0);
        n_posts = n_posts.map(o_post => o_post.id == post.id ? post : o_post)
        this.setState({
            posts: n_posts
        })
    }

    componentDidMount() {
        getPagePost(this.props.page.id, {})
            .then(resp => {
                this.setState({posts : resp.data})
            })
    }
}

PostIndex.propTypes = {
    page: PropTypes.object.isRequired
}

export default PostIndex
if (document.getElementById('post_index')) {
    ReactDOM.render(
    <PostIndex 
        page={window.page}
    />, document.getElementById('post_index'));
}