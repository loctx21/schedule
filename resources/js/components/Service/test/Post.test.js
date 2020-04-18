import '@testing-library/jest-dom/extend-expect'

import { formatPostValue, extractSubmitValue } from '../Post'

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

it("Extract post photo value properlu", () => {
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