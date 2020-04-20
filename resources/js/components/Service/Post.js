
import axios from 'axios'
import moment from 'moment'
import { api } from './Facebook'

/**
 * Extract post type from link
 * 
 * @param {string} link 
 * 
 * @returns {string}
 */
function getPostType(link) {
    if (link.search('videos') != -1)
        return 'video'
    return 'photo'
}

/**
 * Add installed fanpage to backend database
 *
  * @param {object} page  
 * 
 * @return {Promise}
 */
function addPagePost(page_id, data) {

    let formData  = new FormData();
    Object.keys(data).forEach(key => {
        formData.append(key, data[key])
    })

    return axios.post(`/api/page/${page_id}/post`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(resp => {
        return resp.data
    }).catch((resp) => {
        return Promise.reject(resp.response);
    });
}

/**
 * Update post data
 * 
 * @param {Integer} post_id 
 * @param {object} data 
 * 
 * @returns {Promise}
 */
function updatePagePost(post_id, data) {

    let formData  = new FormData();
    Object.keys(data).forEach(key => {
        if (data[key] != null)
            formData.append(key, data[key])
    })

    return axios.post(`/api/post/${post_id}`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(resp => {
        return resp.data
    }).catch((resp) => {
        return Promise.reject(resp.response);
    });
}

/**
 * Get page post from server
 * 
 * @param {Integer} page_id 
 * @param {Array} data 
 * 
 * @returns {Promise}
 */
function getPagePost(page_id, data) {
    return axios.get(`/api/page/${page_id}/post`, data)
        .then(resp => {
            return resp.data
        }).catch((resp) => {
            return Promise.reject(resp.response);
        });
}

/**
 * Delete post on server
 * 
 * @param {Integer} post_id 
 * 
 * @returns {Promise}
 */
function deletePost(post_id) {
    return axios.delete(`/api/post/${post_id}`,
        {},{
    }).then(resp => {
        return resp.data;
    }).catch((resp) => {
        return Promise.reject(resp.response);
    });
}

/**
 * Format post object to prevent null warning formik
 * 
 * @param {object} page
 * @param {object} post
 * 
 * @returns {object} 
 */
function formatPostValue(page, post=null) {
    const defaultValues = {
        post_type : "photo",
        asset_mode : "url",
        message : "",
        media_url: "",
        save_file: false,
        post_mode: "schedule",
        comment : page.post_reply_tmpl ? page.post_reply_tmpl : "" ,
        reply_message : page.message_reply_tmpl ? page.message_reply_tmpl : "",
        target_url: "",
        video_title: "",
        link: "",
        date: "",
        time_hour: 0,
        time_minute: 0
    }

    if (!post)
        return defaultValues

    let f_post = {}
    Object.keys(defaultValues).forEach(key => {
        f_post[key] = post[key] ? post[key] : defaultValues[key]
    })
    
    if (post.scheduled_at_tz) {
        const scheduled_at = moment(post.scheduled_at_tz)
        f_post.time_hour = scheduled_at.hour()
        f_post.time_minute = scheduled_at.minute()
        f_post.date = scheduled_at.format("YYYY-MM-DD")
    }

    if (post.type_text == "link")
        f_post.post_type = "link"
    else if (post.type_text == "video")
        f_post.post_type = "video"

    if (post.comment)
        f_post.comment = post.comment.message

    if (post.reply)
        f_post.reply = post.reply.message
    
    return f_post
}

/**
 * Extract values that satisfy control value
 * 
 * @param {object} values 
 */
function extractSubmitValue(values)
{
    let keys = ['post_type', 'message', 'post_mode', 'comment', 'reply_message', 'target_url']

    if (values.post_mode === 'schedule')
        keys = keys.concat(['date', 'time_hour', 'time_minute'])

    if (values.post_type === 'link')
        keys.push('link')
    else {
        keys.push('asset_mode')
        if (values.asset_mode === 'url')
            keys = keys.concat(['media_url', 'save_file'])
        else
            keys.push('post_file')

        if (values.post_type === 'video')
            keys.push('video_title')
    }

    let ret = {}
    keys.forEach(key => {
        ret[key] = values[key]
    })

    return ret
}

function getFbPostData(url, access_token) {
    const type = getFbPostTypeFromUrl(url)
    if (!type)
        return Promise.resolve(null);

    const fbEndPoint = getFbPostApiEndPoint(url)
    switch (type) {
        case 'post': 
            return api(fbEndPoint, {
                    fields : 'name,from,images',
                    access_token : access_token
                })
                .then(resp => {
                    if (resp.error !== undefined)
                        return null

                    return {
                        message: resp.name,
                        image : resp.images[0].source,
                        from : (resp.from != undefined) ? resp.from.name : ''
                    }
                })
        
        case 'comment':
            return api(fbEndPoint, {
                    fields : 'from,message,attachment',
                    access_token : access_token
                })
                .then(resp => {
                    if (resp.error !== undefined)
                        return null
                    
                    return {
                        message : resp.message,
                        image : resp.attachment.media.image.src,
                        from : resp.from.name
                    }
                })
        
        case 'video':
            return api(fbEndPoint, {
                    fields : 'from,description',
                    access_token : access_token
                })
                .then(resp => {
                    if (resp.error !== undefined)
                        return null
                    
                    return {
                        message : resp.description,
                        image : '',
                        from : ''
                    }
                })
                
        default:
            Promise.resolve(null);
    }
}

function getFbPostApiEndPoint(url) {
    const type = getFbPostTypeFromUrl(url)

    if (!type)
        return null
    
    let regex
    switch (type) {
        case 'post':
            regex = /fbid=([0-9]+)/
            return `/${url.match(regex)[1]}`

        case 'comment':
            regex   = /[0-9]+\/([0-9]+)/
            if (url.indexOf('videos') != -1)
                regex = /videos\/([0-9]+)/
            const c_regex = /comment_id=([0-9]+)/;
            const id      = url.match(regex)[1];
            const comment_id = url.match(c_regex)[1];
            return `/${id}_${comment_id}`

        case 'video':
            regex = /videos\/([0-9]+)/;
            return `/${url.match(regex)[1]}`

        default:
            return null
    }
}

function getFbPostTypeFromUrl(url) {
    if (url.search('facebook.com') == -1)
        return null;

    if (url.search('comment_id') != -1)
        return 'comment';

    if (url.search('photo.php') != -1)
        return 'post';

    if (url.search('videos') != -1)
        return 'video';

    return null;
}

export { getFbPostApiEndPoint, getFbPostTypeFromUrl, getFbPostData , extractSubmitValue, deletePost, getPagePost, getPostType, addPagePost, formatPostValue, updatePagePost }