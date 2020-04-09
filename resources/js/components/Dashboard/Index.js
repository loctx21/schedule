import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import { Facebook, Trash2 } from 'react-feather'
import { Button } from 'reactstrap'
import PageModal from './PageModal'

import { addManagedFanpage } from '../Service/Fanpage'

class DashboardIndex extends Component {
    constructor(props) {
        super(props)
        this.state = {
            page_modal: false,
            pages: props.pages
        }
    }
    render() {
        const { fb_logined, fb_login_url } = this.props
        const { pages } = this.state

        return (    
            <div className="container">
                <div className="text-center mb-3">
                    <a className="btn btn-default" href={fb_login_url}>
                        <Facebook /> 
                        {fb_logined ? 'Refresh Token' : 'Login to Facebook'}
                    </a>
                    {fb_logined &&
                    <a className="btn btn-default" href=""
                        onClick={() => this.setState({page_modal: true})}
                    >
                        <Facebook /> Add Fanpages
                    </a> }
                </div>

                { pages.length > 0 && 
                <div className="row justify-content-center">
                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">Installed Pages</div>
                            <div className="card-body">
                            <table className="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Page</th>
                                        <th scope="col">Facebook ID</th>
                                        <th scope="col">Timezone</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {pages.map(item => (
                                        <tr key={item.id}>
                                            <th scope="row">{item.name}</th>
                                            <td>{item.fb_id}</td>
                                            <td>{item.timezone_gmt}</td>
                                            <td>
                                                <a className="btn" href={`/page/${item.id}/edit`}>Edit</a>
                                                <Button color="danger">
                                                    <Trash2/> Delete
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                }

                { this.state.page_modal && 
                <PageModal 
                    onSubmit={this.handleAddFanpage}
                    onClosed={() => this.setState({page_modal: false})}
                /> 
                }
            </div>
        );
    }

    handleAddFanpage = (page) => {
        addManagedFanpage(page)
            .then(page => {
                let pages = this.state.pages.slice(0)
                pages.push(page)
                this.setState({pages})
            })
    }
}

DashboardIndex.propTypes = {
    pages: PropTypes.arrayOf(PropTypes.object).isRequired,
    fb_logined: PropTypes.bool.isRequired,
    fb_login_url: PropTypes.string.isRequired
}

export default DashboardIndex
if (document.getElementById('dashboard_index')) {
    ReactDOM.render(
        <DashboardIndex 
            fb_login_url={fb_login_url}
            fb_logined={fb_logined}
        />, document.getElementById('dashboard_index'));
}