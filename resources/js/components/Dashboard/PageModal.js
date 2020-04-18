import React, { Component } from 'react'
import PropTypes from 'prop-types';

import { Modal, ModalHeader, ModalBody, ModalFooter, Col, Button, 
    FormGroup, Label } from 'reactstrap'
import * as Yup from 'yup'
import { Formik, Form, Field, ErrorMessage } from 'formik';

import { getManagementFacebookPage } from '../Service/Fanpage'

const PageSchema = Yup.object().shape({
    page: Yup.string()
        .required('Required')
})

class PageModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            modal : true,
            pages : []
        }
    }

    render() {
        return (
            <Modal isOpen={this.state.modal} toggle={this.toggle} className=""
                onClosed={() => this.props.onClosed()} size="">
                <ModalHeader toggle={this.toggle}>
                    Choose page to intergrate 
                </ModalHeader>
                <Formik
                    validationSchema={PageSchema}
                    onSubmit={this.handleSubmit}
                    initialValues={{ page : ""}}
                >
                {({ errors, touched }) => (
                    <Form>
                        <ModalBody>
                            <Field as="select" 
                                name="page" 
                                data-testid="page_select"
                            >
                                <option value="">Choose page</option>
                                {this.state.pages.map(item => (
                                    <option key={item.id} value={item.id}>{item.name}</option>
                                ))}
                            </Field>
                            <ErrorMessage name="page" />
                        </ModalBody>
                        <ModalFooter>
                            <div className="text-right">
                                <Button type="submit" color="primary">
                                    Add
                                </Button>
                            </div>
                        </ModalFooter>
                    </Form>
                )}
                </Formik>
            </Modal>
        )
    }

    toggle = (e) => {
        this.setState({
            modal: false
        })
    }
    
    handleSubmit = (values, actions) => {
        const page = this.state.pages.filter(item => item.id == values.page)[0]
        this.props.onSubmit(page)
        
        this.toggle()
    }
    
    componentDidMount() {
        getManagementFacebookPage()
            .then(resp => {
                this.setState({
                    pages: resp.data
                })
            })
    }
}
PageModal.propTypes = {
   onSubmit: PropTypes.func.isRequired,
   onClosed: PropTypes.func.isRequired
}
export default PageModal