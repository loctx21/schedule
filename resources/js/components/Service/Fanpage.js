import { getLoginStatus, api } from './Facebook'
import axios from 'axios'
/**
 * Get Facebook pages managed by User
 * 
 * @return Promise
 */
function getManagementFacebookPage() {
    return getLoginStatus()
        .then(resp => api('/me/accounts'))
        .then(resp => {
            if (resp && resp.error)
                Promise.reject(resp.error)
            return resp
        })
}

/**
 * Add installed fanpage to backend database
 *
  * @param object page  
 * 
 * @return Promise
 */
function addManagedFanpage(page) {
    return axios.post('/page', data, {
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(resp => {
        return resp.data
    }).catch((resp) => {
        return Promise.reject(resp.response);
    });
}

export {
    getManagementFacebookPage, addManagedFanpage
}