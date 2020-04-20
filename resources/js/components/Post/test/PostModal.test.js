import React from 'react';
import { render, cleanup, fireEvent, waitFor, act } from '@testing-library/react'
import '@testing-library/jest-dom/extend-expect'

import PostModal from '../PostModal'

import { formatPostValue } from '../../Service/Post'


describe("Edit page form works correctly", () => {
    let page
    beforeEach(() => {
        page = {
            access_token: 'access_token_string',
            schedule_option: [{h:7, m:0}, {h:19, m:30}]
        }
    })
    afterEach(cleanup)

    it("display right information depend on user input", async () => {
        const submitHandler = jest.fn()
        const pageCloseHandler = jest.fn()

        const { queryByText, getByText, getByLabelText, getByPlaceholderText, getByTestId } = render(
            <PostModal 
                page={page}
                values={formatPostValue(page)}
                onSubmit={submitHandler}
                onClosed={pageCloseHandler}
            />
        )
        expect(getByLabelText('Photo')).toBeTruthy()
        expect(getByLabelText('Video')).toBeTruthy()
        expect(getByLabelText('Link')).toBeTruthy()
        expect(getByLabelText('Post text')).toBeTruthy()
        expect(getByLabelText('Url')).toBeTruthy()
        expect(getByLabelText('File')).toBeTruthy()
        expect(getByLabelText('Save to server')).toBeTruthy()
        expect(getByLabelText('Media Url')).toBeTruthy()
        
        expect(getByLabelText('Date schedule')).toBeTruthy()
        expect(getByText('Time schedule')).toBeTruthy()
        expect(getByLabelText('Comment')).toBeTruthy()
        expect(getByLabelText('Reply message')).toBeTruthy()
        expect(getByLabelText('Target Url')).toBeTruthy()
        expect(getByText('Add Post')).toBeTruthy()

        await act(async () => {
            fireEvent.click(getByLabelText('Video'))
        })
        expect(getByLabelText('Video title')).toBeTruthy()

        await act(async () => {
            fireEvent.click(getByLabelText('Link'))
        })
        expect(getByPlaceholderText('Link to share')).toBeTruthy()

        await act(async () => {
            fireEvent.click(getByText('07:00'))
        })
        expect(getByTestId('time_hour').value).toBe("7")
        expect(getByTestId('time_minute').value).toBe("0")

        await act(async () => {
            fireEvent.click(getByLabelText('Now'))
        })

        expect(queryByText('Date schedule')).toBeFalsy()
        expect(queryByText('Time schedule')).toBeFalsy()

    })

    it('return the right information for photo post now', async () => {
        const submitHandler = jest.fn()
        const pageCloseHandler = jest.fn()
        submitHandler.mockResolvedValue({});

        const { queryByText, getByText, getByLabelText, getByPlaceholderText, getByTestId } = render(
            <PostModal 
                page={page}
                values={formatPostValue(page)}
                onSubmit={submitHandler}
                onClosed={pageCloseHandler}
            />
        )

        const data = {
            post_type : "photo",
            asset_mode : "url",
            message : "Message to be published",
            media_url: "https://dujye7n3e5wjl.cloudfront.net/photographs/1080-tall/time-100-influential-photos-lunch-atop-skyscraper-19.jpg",
            save_file: true,
            date: "",
            link: "",
            video_title: "",
            post_mode: 'now',
            comment : "Comment test",
            reply_message : "Reply test",
            target_url: 'https://business.facebook.com/dean.flattley/videos/667024427041257/',
            time_hour: 0,
            time_minute: 0
        }

        await act(async () => {
            fireEvent.change(getByLabelText('Post text'), {target : {value: data.message}})
            fireEvent.click(getByLabelText('Save to server'));
            fireEvent.change(getByLabelText('Media Url'), {target : {value: data.media_url}})
            
            fireEvent.change(getByLabelText('Comment'), {target : {value: data.comment}})
            fireEvent.change(getByLabelText('Reply message'), {target : {value: data.reply_message}})
            fireEvent.change(getByLabelText('Target Url'), {target : {value: data.target_url}})

            fireEvent.click(getByLabelText('Now'))
        })
        await act(async () => {
            fireEvent.click(getByText('Add Post'))
        })
        await waitFor(() => expect(submitHandler).toHaveBeenCalledWith(data))

    })

    it('return the right information for photo schedule later', async () => {
        const submitHandler = jest.fn()
        const pageCloseHandler = jest.fn()
        submitHandler.mockResolvedValue({});
        
        const { queryByText, getByText, getByLabelText, getByTestId } = render(
            <PostModal 
                page={page}
                values={formatPostValue(page)}
                onSubmit={submitHandler}
                onClosed={pageCloseHandler}
            />
        )
            
        const data = {
            post_type : "photo",
            asset_mode : "url",
            message : "Message to be published",
            media_url: "https://dujye7n3e5wjl.cloudfront.net/photographs/1080-tall/time-100-influential-photos-lunch-atop-skyscraper-19.jpg",
            save_file: true,
            post_mode: 'schedule',
            comment : "Comment test",
            reply_message : "Reply test",
            target_url: '',
            date: '2020-04-16',
            time_hour: "7",
            time_minute: "0",
            video_title: "",
            link: ""
        }
        
        await act(async () => {
            fireEvent.change(getByLabelText('Post text'), {target : {value: data.message}})
            fireEvent.click(getByLabelText('Save to server'));
            fireEvent.change(getByLabelText('Media Url'), {target : {value: data.media_url}})
            
            fireEvent.change(getByLabelText('Comment'), {target : {value: data.comment}})
            fireEvent.change(getByLabelText('Reply message'), {target : {value: data.reply_message}})
            fireEvent.change(getByLabelText('Target Url'), {target : {value: data.target_url}})

            fireEvent.click(getByLabelText('Schedule'))            
            fireEvent.change(getByLabelText('Date schedule'), {target : {value: data.date}})
            fireEvent.change(getByTestId('time_hour'), {target : {value: data.time_hour}})
            fireEvent.change(getByTestId('time_minute'), {target : {value: data.time_minute}})
        })
        expect(getByLabelText('Date schedule').value).toBe(data.date    )
        await act(async () => {
            fireEvent.click(getByText('Add Post'))
        })
        await waitFor(() => expect(submitHandler).toHaveBeenCalledWith(data))

    })

    it('return the right information for photo upload post schedule', async () => {
        const submitHandler = jest.fn()
        const pageCloseHandler = jest.fn()
        submitHandler.mockResolvedValue({});

        const { queryByText, getByText, getByLabelText, getByTestId } = render(
            <PostModal 
                page={page}
                values={formatPostValue(page)}
                onSubmit={submitHandler}
                onClosed={pageCloseHandler}
            />
        )

        const data = {
            post_type : "photo",
            asset_mode : "file",
            message : "Message to be published",
            media_url: "",
            save_file: true,
            post_file: new File(['(⌐□_□)'], 'chucknorris.png', { type: 'image/png' }),
            post_mode: 'schedule',
            comment : "Comment test",
            reply_message : "Reply test",
            target_url: '',
            date: '2020-04-16',
            time_hour: "7",
            time_minute: "0",
            video_title: "",
            link: ""
        }

        await act(async () => {
            fireEvent.change(getByLabelText('Post text'), {target : {value: data.message}})
            fireEvent.click(getByLabelText('Save to server'));
            fireEvent.click(getByLabelText('File'))            
            
            fireEvent.change(getByLabelText('Comment'), {target : {value: data.comment}})
            fireEvent.change(getByLabelText('Reply message'), {target : {value: data.reply_message}})

            fireEvent.click(getByLabelText('Schedule'))            
            fireEvent.change(getByLabelText('Date schedule'), {target : {value: data.date}})
            fireEvent.change(getByTestId('time_hour'), {target : {value: data.time_hour}})
            fireEvent.change(getByTestId('time_minute'), {target : {value: data.time_minute}})
        })
        expect(getByLabelText('Media File')).toBeTruthy()
        act(() => {
            fireEvent.change(getByLabelText('Media File'), {target : {files: [data.post_file]}})
        })
        
        await act(async () => {
            fireEvent.click(getByText('Add Post'))
        })
        await waitFor(() => expect(submitHandler).toHaveBeenCalledWith(data))

    })

    

    it('return the right information for link schedule later', async () => {
        const submitHandler = jest.fn()
        const pageCloseHandler = jest.fn()
        submitHandler.mockResolvedValue({});
        
        const { getByText, getByLabelText, getByPlaceholderText, getByTestId } = render(
            <PostModal 
                page={page}
                values={formatPostValue(page)}
                onSubmit={submitHandler}
                onClosed={pageCloseHandler}
            />
        )
            
        const data = {
            post_type : "link",
            asset_mode : "url",
            message : "Message to be published",
            save_file: false,
            media_url: "",
            post_mode: 'schedule',
            comment : "Comment test",
            reply_message : "Reply test",
            target_url: '',
            date: '2020-04-16',
            time_hour: "7",
            time_minute: "0",
            video_title: "",
            link: "https://facebook.com"
        }
        
        act(() => {
            fireEvent.click(getByLabelText('Link'))
        })

        await act(async () => {    
            fireEvent.change(getByLabelText('Post text'), {target : {value: data.message}})
            fireEvent.change(getByPlaceholderText('Link to share'), {target : {value: data.link}})
            
            fireEvent.change(getByLabelText('Comment'), {target : {value: data.comment}})
            fireEvent.change(getByLabelText('Reply message'), {target : {value: data.reply_message}})
            fireEvent.change(getByLabelText('Target Url'), {target : {value: data.target_url}})

            fireEvent.click(getByLabelText('Schedule'))            
            fireEvent.change(getByLabelText('Date schedule'), {target : {value: data.date}})
            fireEvent.change(getByTestId('time_hour'), {target : {value: data.time_hour}})
            fireEvent.change(getByTestId('time_minute'), {target : {value: data.time_minute}})
        })
        expect(getByLabelText('Date schedule').value).toBe(data.date    )
        await act(async () => {
            fireEvent.click(getByText('Add Post'))
        })
        await waitFor(() => expect(submitHandler).toHaveBeenCalledWith(data))

    })
})