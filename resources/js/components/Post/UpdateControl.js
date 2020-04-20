import React, { Component } from 'react'
import PropTypes from 'prop-types';

import { updatePagePost, formatPostValue, extractSubmitValue } from '../Service/Post'
import PostModal from './PostModal'

class UpdateControl extends Component {
    constructor(props) {
        super(props)
        this.state = {
            modal : true
        }
    }

    render() {
        const { page, post } = this.props

        return (
            <PostModal 
                onSubmit={this.handleSubmit}
                onClosed={this.handleClosed}
                values={formatPostValue(page, post)}
                page={page}
            />
        )
    }
    
    handleSubmit = (values) => {
        const { post } = this.props
        const ext_values = extractSubmitValue(values)

        return updatePagePost(post.id, ext_values)
            .then(resp => {
                this.props.onUpdated(resp);
            });
    }

    handleClosed = () => {
        this.setState({modal: false})
        this.props.onUpdated(null)
    }
}

UpdateControl.propTypes = {
   page: PropTypes.object.isRequired,
   post: PropTypes.object.isRequired,
   onUpdated: PropTypes.func.isRequired
}

export default UpdateControl