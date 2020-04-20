import '@testing-library/jest-dom/extend-expect'

import { formatPostValue, extractSubmitValue, getFbPostData, getFbPostTypeFromUrl, getFbPostApiEndPoint } from '../Post'
import { api } from '../Facebook'
jest.mock('../Facebook')

it("format none post values correctly", () => {
    const page = {
        post_reply_tmpl: "page template rely",
        reply_message: "page reply template"
    }

    const post = 
    {
        id: 12,
        message: "This is the message",
        user_id: 2,
        page_id: 2,
        fb_id: null,
        fb_post_id: null,
        fb_album_id: null,
        status: 0,
        media_url: "https://schedule.lc/storage/page/2/photo/photo-1534067783941-51c9c23ecefd",
        link: null,
        type: 1,
        video_title: null,
        scheduled_at: "2020-04-18 12:00:00",
        published_at: null,
        target_url: null,
        created_at: "2020-04-17 00:17:45",
        updated_at: "2020-04-17 00:17:45",
        scheduled_at_tz: "2020-04-18 07:00:00",
        status_text: "not publish",
        type_text: "photo",
        fb_post_link: null,
        comment: {id: 12, message: "test comment {{link}}", status: 0, user_id: 2, post_id: 12, published_at: null},
        reply: null
    }

    const f_post = formatPostValue(page, post)

    expect(f_post.asset_mode).toBe("url")
    expect(f_post.post_type).toBe("photo")
    expect(f_post.time_hour).toBe(7)
    expect(f_post.time_minute).toBe(0)
    expect(f_post.date).toBe("2020-04-18")
    expect(f_post.comment).toBe(post.comment.message)
})

it("Extract post photo value properly", () => {
    const values = {
        post_type : "photo",
        asset_mode : "url",
        message : "message",
        media_url: "https://images.unsplash.com/photo-1534067783941-51c9c23ecefd?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&w=1000&q=80",
        save_file: true,
        post_mode: "schedule",
        comment : "test comment" ,
        reply_message : "test message reply",
        target_url: "",
        video_title: "",
        link: "",
        date: "2020-04-10",
        time_hour: 7,
        time_minute: 0
    }

    const ext_values = extractSubmitValue(values)

    expect(ext_values).toEqual({
        post_type : "photo",
        asset_mode : "url",
        message : "message",
        media_url: "https://images.unsplash.com/photo-1534067783941-51c9c23ecefd?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&w=1000&q=80",
        save_file: true,
        post_mode: "schedule",
        comment : "test comment" ,
        reply_message : "test message reply",
        target_url: "",
        date: "2020-04-10",
        time_hour: 7,
        time_minute: 0
    })
})

it("Load User Facebook post content", async () => {
    const url = "https://www.facebook.com/photo.php?fbid=1660764599908961&set=o.257408390963565&type=3"
    const access_token = 'access_token_string'
    const ret_data = {
        name : 'Loc Nguyen',
        images : [{
            source : "https://images.unsplash.com/photo-1529736576495-1ed4a29ca7e1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80"
        }],
        from : undefined
    }

    api.mockResolvedValueOnce(ret_data)

    let ret
    await getFbPostData(url, access_token)
        .then(resp => ret = resp)
    
    expect(api).toHaveBeenCalledWith(`/1660764599908961`, {
        fields : 'name,from,images',
        access_token : access_token
    })
    
    expect(ret).toMatchObject({
        message : ret_data.name,
        image : ret_data.images[0].source,
        from : ''
    })
})

it("Load User Facebook comment content", async () => {
    const url = "https://www.facebook.com/257408390963565/photos/a.1159410334086795/2334815889879561/?type=3&av=257408390963565&eav=AfYX36t2-AVVF7eKhqSnDn8GZ5-IF43ZdHPMd-w1FMxcS5b91v77RIgvDggxFARObY0&comment_id=3430631320298007"
    const access_token = 'access_token_string'
    const ret_data = {
        message : 'Message',
        attachment : {
            media : {
                image : {
                    src : "https://images.unsplash.com/photo-1529736576495-1ed4a29ca7e1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80"
                }
            }
        },
        from : {
            name : 'Loc Nguyen'
        }
    }

    api.mockResolvedValueOnce(ret_data)

    let ret
    await getFbPostData(url, access_token)
        .then(resp => ret = resp)
    
    expect(api).toHaveBeenCalledWith(`/2334815889879561_3430631320298007`, {
        fields : 'from,message,attachment',
        access_token : access_token
    })
    expect(ret).toMatchObject({
        message : ret_data.message,
        image : ret_data.attachment.media.image.src,
        from : ret_data.from.name
    })
})

it("Load User Facebook video content", async () => {
    const url = "https://www.facebook.com/nguyen.75470/videos/346271122775943/"
    const access_token = 'access_token_string'
    const ret_data = {
        description : 'Message'
    }

    api.mockResolvedValueOnce(ret_data)

    let ret
    await getFbPostData(url, access_token)
        .then(resp => ret = resp)
    
    expect(api).toHaveBeenCalledWith(`/346271122775943`, {
        fields : 'from,description',
        access_token : access_token
    })
    expect(ret).toMatchObject({
        message : ret_data.description,
        image : '',
        from : ''
    })
})

it("Get right Fb post apit endpoint", () => {
    const retPhotoPostFbEndPoint = getFbPostApiEndPoint("https://www.facebook.com/photo.php?fbid=1660764599908961&set=o.257408390963565&type=3")
    expect(retPhotoPostFbEndPoint).toBe("/1660764599908961")

    const retCommentPostFbEndPoint = getFbPostApiEndPoint("https://www.facebook.com/257408390963565/photos/a.1159410334086795/2334815889879561/?type=3&av=257408390963565&eav=AfYX36t2-AVVF7eKhqSnDn8GZ5-IF43ZdHPMd-w1FMxcS5b91v77RIgvDggxFARObY0&comment_id=3430631320298007")
    expect(retCommentPostFbEndPoint).toBe("/2334815889879561_3430631320298007")

    const retVideoPostFbEndPoint = getFbPostApiEndPoint("https://www.facebook.com/nguyen.75470/videos/346271122775943/")
    expect(retVideoPostFbEndPoint).toBe("/346271122775943")

    const retNoneFbPostFbEndPoint = getFbPostApiEndPoint("https://www.abc.com/nguyen.75470/videos/346271122775943/")
    expect(retNoneFbPostFbEndPoint).toBe(null)
})

it("Get right Fb post type", () => {
    const retPhotoPost = getFbPostTypeFromUrl("https://www.facebook.com/photo.php?fbid=1660764599908961&set=o.257408390963565&type=3")
    expect(retPhotoPost).toBe("post")

    const retCommentPost = getFbPostTypeFromUrl("https://www.facebook.com/257408390963565/photos/a.1159410334086795/2334815889879561/?type=3&av=257408390963565&eav=AfYX36t2-AVVF7eKhqSnDn8GZ5-IF43ZdHPMd-w1FMxcS5b91v77RIgvDggxFARObY0&comment_id=3430631320298007")
    expect(retCommentPost).toBe("comment")

    const retVideoPost = getFbPostTypeFromUrl("https://www.facebook.com/nguyen.75470/videos/346271122775943/")
    expect(retVideoPost).toBe("video")

    const retNoneFbPost = getFbPostTypeFromUrl("https://www.abc.com/nguyen.75470/videos/346271122775943/")
    expect(retNoneFbPost).toBe(null)
})