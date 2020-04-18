import React from 'react';
import { render, cleanup, fireEvent, waitFor, act } from '@testing-library/react'
import '@testing-library/jest-dom/extend-expect'

import axiosMock from 'axios'
jest.mock('axios')

import UpdateCotrol from '../UpdateControl'

import PostModalMock from '../PostModal'

jest.mock('../PostModal', () => {
    const data = {
        id : 1,
        message : "test message"
    }
    return jest.fn(props => (
        <button onClick={() => props.onSubmit(data)}>
            Add post
        </button>
    ));
})


it("call update page post api", async () => {
    let page = {
        id : 1,
        name : "test",
        schedule_option: [{h:7, m:0}, {h:19, m:30}]
    }
    let post = {
        id : 1
    }

    const handler = jest.fn()
    const { getByText } = render(
        <UpdateCotrol 
            page={page}
            onUpdated={handler}
            post={post}
        />
    )

    axiosMock.post.mockResolvedValueOnce({
        data : {
            id : 1,
            message : "test message"
        }
    })
    
    act(() => {
        fireEvent.click(getByText('Add post'))
    })
    await waitFor(() => expect(handler).toHaveBeenCalledWith({
        id : 1,
        message : "test message"
    }))
    expect(axiosMock.post).toHaveBeenCalledWith(`/api/post/${post.id}`, expect.any(FormData), 
        {headers:{'Content-Type' : 'multipart/form-data'}})
})
