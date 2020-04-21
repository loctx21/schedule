import React, { Component } from 'react'
import PropTypes from 'prop-types';

import { Modal, ModalHeader, ModalBody, ModalFooter, Col, Button, 
    FormGroup, Label, Row } from 'reactstrap'
import * as Yup from 'yup'
import { Formik, Form, Field, ErrorMessage } from 'formik';

import { getPostType, getFbPostData } from '../Service/Post'

class PostModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            modal : true,
            pages : []
        }
        
        this.toggle = this.toggle.bind(this)
    }

    render() {
        const { values } = this.props
        
        return (
            <Modal isOpen={this.state.modal} toggle={this.toggle} className=""
                onClosed={() => this.props.onClosed()} size={"lg"} backdrop="static">
                <ModalHeader toggle={this.toggle}>
                    Publish a post 
                </ModalHeader>
                <Formik
                    validationSchema={PostSchema}
                    onSubmit={this.handleSubmit}
                    initialValues={ values }
                >
                {({ errors, touched, values, setFieldValue, isSubmitting }) => (
                    <Form>
                        <ModalBody>
                            <Row>
                                <Col sm={7}>
                                    { this.renderPostTypeField() } 
                                    { values.post_type == 'video' && this.renderVideoTitleField() }
                                    { this.renderMessageField() }
                                    { !['link'].includes(values.post_type) && this.renderAssetChoice() } 
                                    { !['link'].includes(values.post_type) && this.renderAssetMain(values, setFieldValue) }
                                    { values.post_type == 'link' && this.renderLinkField() }
                                    { this.renderTimeChoice() }
                                    { this.renderDateSchedule(values) }   
                                    { this.renderTimeSchedule(values, setFieldValue) }
                                </Col>
                                <Col sm={5}>
                                    { this.renderCommentReplyTarget(values, setFieldValue)}
                                </Col>
                            </Row>
                        </ModalBody>
                        <ModalFooter>
                            <div className="text-right">
                                <Button type="submit" disabled={isSubmitting} color="primary">
                                    Add Post
                                </Button>
                            </div>
                        </ModalFooter>
                    </Form>
                )}
                </Formik>
            </Modal>
        )
    }

    renderPostTypeField() {    
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="post_type"
                >
                    Post type
                </Label>
                <Col sm={9}>
                    <Field name="post_type">
                    {({ field, form, meta }) => (
                    <React.Fragment>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="post_type_photo" 
                                checked={field.value === 'photo'}
                                value="photo"
                                name="post_type"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="post_type_photo">Photo</label>
                        </div>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="post_type_video" 
                                checked={field.value === 'video'}
                                value="video"
                                name="post_type"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="post_type_video">Video</label>
                        </div>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="post_type_link" 
                                defaultChecked={field.value === 'link'}
                                value="link"
                                name="post_type"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="post_type_link">Link</label>
                        </div>
                    </React.Fragment>
                    )}
                    </Field>
                    <ErrorMessage name="post_type" />
                </Col>
            </FormGroup> 
        );
    }

    renderVideoTitleField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="video_title"
                >
                    Video title
                </Label>
                <Col sm={9}>
                    <Field name="video_title" id="video_title" as="input" 
                        placeholder="Video title"
                        className="form-control"
                    />
                    <ErrorMessage name="video_title" />
                </Col>
            </FormGroup> 
        );
    }

    renderLinkField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="link"
                >
                    Link
                </Label>
                <Col sm={9}>
                    <Field name="link" id="link" as="input" 
                        placeholder="Link to share"
                        className="form-control"
                    />
                    <ErrorMessage name="link" />
                </Col>
            </FormGroup> 
        );
    }

    renderMessageField() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="message"
                >
                    Post text
                </Label>
                <Col sm={9}>
                    <Field name="message" id="message" as="textarea" placeholder="Your message"
                        className="form-control"
                    />
                    <ErrorMessage name="message" />
                </Col>
            </FormGroup>
        );
    }
    
    renderAssetChoice() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="asset_mode"
                >
                    Asset choice
                </Label>
                <Col sm={9}>
                    <Field name="asset_mode">
                    {({ field, form, meta }) => (
                    <React.Fragment>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="asset_mode_url" 
                                defaultChecked={field.value === 'url'}
                                value="url"
                                name="asset_mode"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="asset_mode_url">Url</label>
                        </div>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="asset_mode_file" 
                                defaultChecked={field.value === 'file'}
                                value="file"
                                name="asset_mode"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="asset_mode_file">File</label>
                        </div>
                    </React.Fragment>
                    )}
                    </Field>
                    <ErrorMessage name="asset_mode" />
                </Col>
            </FormGroup>  
        )
    }

    renderAssetMain(values, setFieldValue) {
        const { asset_mode} = values
       
        return (
            <FormGroup className="row">
                {asset_mode == "url" ?
                <React.Fragment>
                    <Label className="col-sm-3 control-label"
                        htmlFor="media_url"
                    >
                        Media Url
                    </Label>
                    <Col sm={6}>
                        <Field name="media_url" id="media_url" as="input" placeholder="Media Url"
                            className="form-control"
                        />
                        <ErrorMessage name="media_url" />
                    </Col>
                    <Col sm={3}>
                        <Field name="save_file">
                        {({ field, form, meta }) => (
                        <div className="form-check form-check-inline">
                            <input type="checkbox" {...field} 
                                id="save_file" 
                                checked={field.value == true}
                                value="true"
                                name="save_file"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="save_file">Save to server</label>
                        </div>
                        )}
                        </Field>
                    </Col>
                </React.Fragment> :
                <React.Fragment>
                    <Label className="col-sm-3 control-label"
                        htmlFor="post_file"
                    >
                        Media File
                    </Label>
                    <Col sm={9}>
                        <Field name="post_file" >
                        {({ field, form, meta }) => (
                        <div className="form-control">
                            <input type="file"
                                id="post_file" 
                                name="post_file"
                                onChange={(event) => {
                                    setFieldValue("post_file", event.currentTarget.files[0]);
                                }}
                            />
                        </div>
                        )}
                        </Field>
                        <ErrorMessage name="post_file" />
                    </Col>
                </React.Fragment>
                }       
            </FormGroup>  
        )
    }

    renderTimeChoice() {
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="post_mode"
                >
                    Time choice
                </Label>
                <Col sm={9}>
                    <Field name="post_mode">
                    {({ field, form, meta }) => (
                    <React.Fragment>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="post_mode_now" 
                                defaultChecked={field.value === 'now'}
                                value="now"
                                name="post_mode"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="post_mode_now">Now</label>
                        </div>
                        <div className="form-check form-check-inline">
                            <input type="radio" {...field} 
                                id="post_mode_schedule" 
                                defaultChecked={field.value === 'schedule'}
                                value="schedule"
                                name="post_mode"
                                className="form-check-input"
                            />
                            <label className="form-check-label" htmlFor="post_mode_schedule">Schedule</label>
                        </div>
                    </React.Fragment>
                    )}
                    </Field>
                    <ErrorMessage name="post_mode" />
                </Col>
            </FormGroup> 
        )
    }

    renderDateSchedule(values) {
        const { post_mode } = values;
        if (post_mode == 'now')
            return;
        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="date"
                >
                    Date schedule
                </Label>
                <Col sm={9}>
                    <Field name="date" type="date" id="date" as="input" placeholder="Date"
                        className="form-control"
                    />
                    <ErrorMessage name="date" />
                </Col>
            </FormGroup>  
        )
    }

    renderTimeSchedule(values, setFieldValue) {
        const { post_mode } = values;
        if (post_mode == 'now')
            return;
            
        const hours = [...Array(24).keys()]
        const minutes = [...Array(12).keys()].map(item => item * 5)

        return (
            <FormGroup className="row">
                <Label className="col-sm-3 control-label"
                    htmlFor="time_schedule"
                >
                    Time schedule
                </Label>
                <Col sm={9}>
                    <Field name="time_schedule">
                    {({ field, form, meta }) => (
                    <div className="form-check form-check-inline">
                        <Field as="select" name="time_hour" data-testid="time_hour">
                            {hours.map(item => (
                            <option key={item} value={item}>{item < 10 ? `0${item.toString()}` : item.toString()}</option>
                            ))}
                        </Field>
                         : 
                        <Field as="select" name="time_minute" data-testid="time_minute">
                            {minutes.map(item => (
                            <option key={item} value={item}>{item < 10 ? `0${item.toString()}` : item.toString()}</option>
                            ))}
                        </Field>
                        {
                            this.scheduleOption.map(item => (
                                <a key={`${item.h}_${item.m}`} className="time-config" href="#"
                                    onClick={() => {setFieldValue("time_hour", item.h); setFieldValue("time_minute", item.m);}}
                                >
                                    {item.h < 10 ? `0${item.h.toString()}` : item.h.toString()}
                                    :
                                    {item.m < 10 ? `0${item.m.toString()}` : item.m.toString()}
                                </a>
                            ))
                        }
                    </div>
                    )}
                    </Field>
                    <ErrorMessage name="date" />
                </Col>
            </FormGroup>  
        )
    }

    renderCommentReplyTarget(values, setValues) {
        return (
            <React.Fragment>
                <FormGroup className="row">
                    <Label className="col-sm-3 control-label"
                        htmlFor="comment"
                    >
                        Comment
                    </Label>
                    <Col sm={9}>
                        <Field name="comment" id="comment" as="textarea" 
                            placeholder="Comment"
                            className="form-control"
                        />
                        <ErrorMessage name="comment" />
                    </Col>
                </FormGroup> 
                <FormGroup className="row">
                    <Label className="col-sm-3 control-label"
                        htmlFor="reply_message"
                    >
                        Reply message
                    </Label>
                    <Col sm={9}>
                        <Field name="reply_message" id="reply_message" as="textarea" 
                            placeholder="Your reply message"
                            className="form-control"
                        />
                        <ErrorMessage name="reply_message" />
                    </Col>
                </FormGroup>   
                <FormGroup className="row">
                    <Label className="col-sm-3 control-label"
                        htmlFor="target_url"
                    >
                        Target Url
                    </Label>
                    <Col sm={9}>
                        <Field name="target_url" id="target_url" as="input" placeholder="Target Url"
                            className="form-control"
                        />
                        <ErrorMessage name="target_url" />
                    </Col>
                    { values.target_url &&
                    <Col sm={12} className="text-right">
                        <Button color="secondary" 
                            onClick={() => this.setPostTypeHandler(values.target_url, setValues)}>
                            Load content
                        </Button>
                    </Col>
                    }
                </FormGroup> 
            </React.Fragment>
        )
    }

    setPostTypeHandler = (link, setFieldValue) => {
        const type = getPostType(link)
        getFbPostData(link, this.props.page.access_token)
            .then(resp => {
                setFieldValue("message", resp.message)
                setFieldValue("post_type", getPostType(link))

                if (resp.image) {
                    setFieldValue("asset_mode", "url")
                    setFieldValue("media_url", resp.image)
                }
            })
    }

    get scheduleOption() {
        return this.props.page.schedule_option.map(item => ({
            h: parseInt(item.h),
            m: parseInt(item.m)
        }))
    }

    toggle = (e) => {
        this.setState({
            modal: false
        })
    }

    handleSubmit = (values, {setSubmitting}) => {
        setSubmitting(true)
        this.props.onSubmit(values)
            .then(resp => {
                this.toggle()
            }).finally(resp => {
                setSubmitting(false)
            });
    }
}

PostModal.propTypes = {
    page: PropTypes.object.isRequired,
    onSubmit: PropTypes.func.isRequired,
    onClosed: PropTypes.func.isRequired
}

const PostSchema = Yup.object().shape({
    message: Yup.string()
        .required('Required')
})

export default PostModal