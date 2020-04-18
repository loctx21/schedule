import React, { Component } from 'react';
import { render } from 'react-dom';

import Confirmation from './Confirmation';

export default class ConfirmWrapper extends Component {
    render() {
        return (
            <Confirmation {...this.props}/>
        );
    }
};

function createElementReconfirm (properties) {
    let divTarget = document.getElementById(`react_confirm_alert_${properties.id_div}`)
    if (divTarget) {
        // Rerender - the mounted ReactConfirmAlert
        render(<ConfirmWrapper {...properties} />, divTarget)
    } else {
        // Mount the ReactConfirmAlert component
        document.body.children[0].classList.add('react_confirm_alert')
        divTarget = document.createElement('div')
        divTarget.id = `react_confirm_alert_${properties.id_div}`
        document.body.appendChild(divTarget)
        render(<ConfirmWrapper {...properties} />, divTarget)
    }
}


export function confirmAlert (properties) {
    properties.id_div = (new Date).getTime();
    createElementReconfirm(properties)
}
