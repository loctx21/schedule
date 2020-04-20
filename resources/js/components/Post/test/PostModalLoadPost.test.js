import React from 'react';
import { render, cleanup, fireEvent, waitFor, act } from '@testing-library/react'
import '@testing-library/jest-dom/extend-expect'

import PostModal from '../PostModal'

import { getFbPostData, getPostType } from '../../Service/Post'
jest.mock('../../Service/Post')

const { formatPostValue } = jest.requireActual('../../Service/Post')


let page = {
    access_token: 'access_token_string',
    schedule_option: [{h:7, m:0}, {h:19, m:30}]
}

it('display right filed based on target url', async () => {
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

    await act(async () => {
        fireEvent.change(getByPlaceholderText('Target Url'), {target : {value : "https://business.facebook.com/dean.flattley/videos/667024427041257/"}})
    })
    expect(queryByText('Load content')).toBeTruthy()

    getFbPostData.mockResolvedValueOnce({
        message : "test return message",
        image : '',
        from : ''
    })
    getPostType.mockReturnValue('video')

    await act(async () => {
        fireEvent.click(getByText('Load content'))
    })
    expect(getByLabelText('Video title')).toBeTruthy()
    expect(getByLabelText('Post text').value).toBe("test return message")
})
