'use strict';

/**
 * Get Promise with FB instance.
 * https://gist.github.com/recca0120/c4fa99a358460e8189ab04907e8e96e4
 * 
 * @return {Promise}
 */
function getScript() {
    return new Promise((resolve) => {
        if (window.FB)
            resolve(window.FB)

        const id = 'facebook-jssdk'
        if (document.getElementById(id))
            return

        window.fbAsyncInit = function() {
            FB.init({
                appId            : window.app_id,
                autoLogAppEvents : true,
                xfbml            : true,
                version          : 'v6.0'
            });
        };
        
        const js = document.createElement('script')
        js.id = id
        js.src = 'https://connect.facebook.net/en_US/sdk.js'
        js.addEventListener('load', () => {
            resolve(window.FB)
        })

        const fjs = document.querySelectorAll('script')[0]
        fjs.parentNode.insertBefore(js, fjs)
    })
}


/**
 * Promise get user's facebook login status
 * 
 * @return {Promise}
 */
function getLoginStatus() {
    return new Promise(async (resolve) => {
<<<<<<< HEAD
        const FB = await lib.getScript()
=======
        const FB = await getScript()
>>>>>>> 502eb4b7d2758db2d2197c356defc6f8ee4cafea

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
 * @return {Promise}
 */
function api(...params) {
    return new Promise(async (resolve) => {
<<<<<<< HEAD
        const FB = await lib.getScript()
=======
        const FB = await getScript()
>>>>>>> 502eb4b7d2758db2d2197c356defc6f8ee4cafea

        const callback = (resp) => {
            resolve(resp)
        }

        if (params.length > 3)
            params = params.slice(0,3)

        params.push(callback)

        FB.api(...params)
    })
}

//Trick to overwrite getScript in test
const lib = {
    getScript
}

export {
    getScript, getLoginStatus, api, lib
}