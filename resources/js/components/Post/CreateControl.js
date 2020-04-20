import React, { Component } from 'react'
import PropTypes from 'prop-types';

import { addPagePost, formatPostValue, extractSubmitValue } from '../Service/Post'
import PostModal from './PostModal'

class CreateControl extends Component {
    constructor(props) {
        super(props)
        this.state = {
            modal : true
        }
    }

    render() {
        const { page } = this.props

        return (
            <PostModal 
                onSubmit={this.handleSubmit}
                onClosed={this.handleClosed}
                values={formatPostValue(page)}
                page={page}
            />
        )
    }

    handleSubmit = (values) => {
        const { page } = this.props
        const ext_values = extractSubmitValue(values)

        return addPagePost(page.id, ext_values)
            .then(resp => {
                this.props.onAdded(resp);
            });
    }

    handleClosed = () => {
        this.setState({modal: false})
        this.props.onAdded(null)
    }
}

CreateControl.propTypes = {
   page: PropTypes.object.isRequired,
   onAdded: PropTypes.func.isRequired
}

export default CreateControl