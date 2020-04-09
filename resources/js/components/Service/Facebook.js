'use strict';

/**
 * Get Promise with FB instance.
 * https://gist.github.com/recca0120/c4fa99a358460e8189ab04907e8e96e4
 * 
 * @return Promise
 */
function getScript() {
    return new Promise((resolve) => {
        if (window.FB)
            resolve(window.FB)

        const id = 'facebook-jssdk'
        if (document.getElementById(id))
            return

        const js = document.createElement('script')
        js.id = id
        js.src = 'https://connect.facebook.net/en_US/sdk.js'
        js.addEventListener('load', () => {
            Object.assign(this, {
            })

            resolve(window.FB)
        })

        const fjs = document.querySelectorAll('script')[0]
        fjs.parentNode.insertBefore(js, fjs)
    })
}

/**
 * Initialize Facebook app
 * 
 * @param {*} params 
 * 
 * @return Promise
 */
function init(params = {}) {
    return new Promise(async (resolve) => {
        const FB = await getScript()
        FB.init(params);

        resolve(FB)
    })
}


/**
 * Promise get user's facebook login status
 * 
 * @return Promise
 */
function getLoginStatus() {
    return new Promise(async (resolve) => {
        const FB = await getScript()

        FB.getLoginStatus((resp) => {
            resolve(resp)
        })
    })
}

/**
 * Execute Facebook api in promuse
 * 
 * @param  {...any} params 
 * 
 * @return Promise
 */
function api(...params) {
    return new Promise(async (resolve) => {
        const FB = await getScript()

        const callback = (resp) => {
            resolve(resp)
        }

        if (params.length > 3)
            params = params.slice(0,3)

        params.push(callback)

        FB.api(...params)
    })
}

export {
    init, getLoginStatus, api
}