import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types';

import { Col, Button, FormGroup, Label, Row } from 'reactstrap'
import * as Yup from 'yup'
import { Formik, Form, Field, ErrorMessage } from 'formik'

import { updatePage } from '../Service/Fanpage'

class EditPage extends Component {
    constructor(props) {
        super(props)
        this.state = {
            message : null
        }
    }

    render() {
        const { page } = this.props
        const { message } = this.state
    
        return (
            <div className="row justify-content-center">
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">{page.name}</div>
                        <div className="card-body">
                            <Formik
                                    validationSchema={PageSchema}
                                    onSubmit={this.handleSubmit}
                                    initialValues={page}
                                >
                                {({ errors, touched, isSubmitting }) => (
                                    <Form>
                                        <Row>
                                            <Col sm={12}>
                                                { this.renderIndexMessageField() } 
                                                { this.renderTimezoneField() }
                                                { this.renderFbDefaultAlbumField() } 
                                                { this.renderScheduleTimeField() }
                                                { this.renderMessageReplyTemplateField() }   
                                                { this.renderCommentPostTemplateField() }
                                            </Col>
                                        </Row>
                                        <Row>
                                            <Col sm={12} className="action-ctrl text-right">
                                                { message &&
                                                <div className="alert alert-primary" role="alert">
                                                    {message}
                                                </div>
                                                }
                                                <Button type="submit" disabled={isSubmitting} color="primary">Save</Button>
                                            </Col>
                                        </Row>
                                    </Form>
                                )}
                            </Formik>
                        </div>
                    </div>
                </div>
            </div>
        )
    }

    renderIndexMessageField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="conv_index"
                >
                    Index message
                </Label>
                <Col sm={9}>
                    <Field name="conv_index">
                    {({ field, form, meta }) => (
                    <React.Fragment>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="conv_index_enable" 
                                checked={field.value == 1}
                                value={1}
                                name="conv_index"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="conv_index_enable">Enable</label>
                        </div>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="conv_index_disable" 
                                checked={field.value == 0}
                                value={0}
                                name="conv_index"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="conv_index_disable">Disable</label>
                        </div>
                    </React.Fragment>
                    )}
                    </Field>
                    <ErrorMessage name="conv_index" />
                </Col>
            </FormGroup> 
        );
    }

    renderTimezoneField() {
        const  { timezones } = this.props
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="timezone"
                >
                    Time schedule
                </Label>
                <Col sm={9}>
                    <Field as="select" name="timezone" id="timezone">
                        <option value="">Choose your timezone</option>
                    {
                        timezones.map(item => (
                            <option key={item.zone} value={item.zone}>{item.diff_from_GMT}</option>
                        ))
                    }
                    </Field>
                    <ErrorMessage name="timezone" />
                </Col>
            </FormGroup>  
        )
    }

    renderFbDefaultAlbumField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="def_fb_album_id"
                >
                    FB default album id
                </Label>
                <Col sm={9}>
                    <Field name="def_fb_album_id" id="def_fb_album_id" as="input" 
                        placeholder="Default album id"
                        className="form-control"
                    />
                    <ErrorMessage name="def_fb_album_id" />
                </Col>
            </FormGroup> 
        );
    }

    renderScheduleTimeField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="schedule_time"
                >
                    Schedule time
                </Label>
                <Col sm={9}>
                    <Field name="schedule_time" id="schedule_time" as="input" 
                        placeholder="Time schedule seprated by ','"
                        className="form-control"
                    />
                    <ErrorMessage name="schedule_time" />
                </Col>
            </FormGroup> 
        );
    }

    renderMessageReplyTemplateField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="message_reply_tmpl"
                >
                    Message reply template
                </Label>
                <Col sm={9}>
                    <Field name="message_reply_tmpl" id="message_reply_tmpl" as="textarea" 
                        placeholder="Your reply message template"
                        className="form-control"
                    />
                    <ErrorMessage name="message_reply_tmpl" />
                </Col>
            </FormGroup>   
        );
    }

    renderCommentPostTemplateField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="post_reply_tmpl"
                >
                    Comment post reply template
                </Label>
                <Col sm={9}>
                    <Field name="post_reply_tmpl" id="post_reply_tmpl" as="textarea" 
                        placeholder="Your reply comment post reply template"
                        className="form-control"
                    />
                    <ErrorMessage name="post_reply_tmpl" />
                </Col>
            </FormGroup>   
        );
    }

    handleSubmit = (values, {setSubmitting}) => {
        setSubmitting(true)
        updatePage(this.props.page.id, values)
            .then(resp => {
                setSubmitting(false)
                this.setState({
                    message : "Page's data saved successfully!"
                })
            });
    }
}
EditPage.propTypes = {
    page: PropTypes.object.isRequired,
    timezones: PropTypes.arrayOf(PropTypes.object).isRequired
}

const PageSchema = Yup.object().shape({
    conv_index: Yup.bool().required('Required'),
    def_fb_album_id: Yup.number().typeError('Default album id must be a number')
})

export default EditPage

if (document.getElementById('page_edit')) {
    ReactDOM.render(
        <EditPage 
            page={window.page}
            timezones={window.timezones}
        />, document.getElementById('page_edit')
    );
}