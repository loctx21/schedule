import React, { Component } from 'react'
import { unmountComponentAtNode } from 'react-dom'
import PropTypes from 'prop-types'

import { Button, Modal, ModalHeader, ModalBody, ModalFooter} from 'reactstrap'

class Confirmation extends Component {
    constructor(props) {
        super(props)

        this.state = {
            modal : true
        }
    }

    render() {
        const { head_title, buttons, content } = this.props

        return (
            <Modal isOpen={this.state.modal} toggle={this.toggle} className=""
                   backdrop="static" onClosed={this.handleClose}>
                {head_title && 
                <ModalHeader toggle={this.toggle}>
                    {head_title}
                </ModalHeader>
                }
                <ModalBody>
                    <div className="row">
                        <div className="col-sm-12 text-left">
                            { content() }
                        </div>
                    </div>
                </ModalBody>
                <ModalFooter>
                    <div className="col-sm-12 text-right action-ctrl">
                        {buttons.map((button, i) => (
                            <Button key={i} color={button.color} onClick={() => this.handleClickButton(button)}>
                              {button.label}
                            </Button>
                        ))}
                    </div>
                </ModalFooter>
            </Modal>
        );
    }

    handleClickButton = button => {
        if (button.onClick)
            button.onClick();
        this.toggle();
    }

    toggle = (e) => {
        this.setState(state => ({
            modal: !state.modal
        }));
    }

    handleClose = () => {
        removeElementReconfirm(this.props.id_div);
    }
}

function removeElementReconfirm (id) {
    const target = document.getElementById(`react_confirm_alert_${id}`);
    unmountComponentAtNode(target);
    target.parentNode.removeChild(target);
}

Confirmation.propTypes = {
    head_title: PropTypes.string,
    content: PropTypes.func.isRequired,
    buttons: PropTypes.arrayOf(PropTypes.object),
    id_div: PropTypes.oneOfType([PropTypes.string, PropTypes.number])
}

export default Confirmation;
