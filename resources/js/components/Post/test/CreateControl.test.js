import React from 'react';
import { render, cleanup, fireEvent, waitFor, act } from '@testing-library/react'
import '@testing-library/jest-dom/extend-expect'

import axiosMock from 'axios'
jest.mock('axios')

import CreateCotrol from '../CreateControl'

import PostModalMock from '../PostModal'
import { Form } from 'reactstrap';

jest.mock('../PostModal', () => {
    const data = {
        message : "test message"
    }
    return jest.fn(props => (
        <button onClick={() => props.onSubmit(data)}>
            Add post
        </button>
    ));
})


it("call create page post api", async () => {
    let page = {
        id : 1,
        name : "test",
        schedule_option: [{h:7, m:0}, {h:19, m:30}]
    }

    const handler = jest.fn()
    const { getByText } = render(
        <CreateCotrol 
            page={page}
            onAdded={handler}
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
        id: 1,
        message : "test message"
    }))

    let formData = new FormData()
    expect(axiosMock.post).toHaveBeenCalledWith(`/api/page/${page.id}/post`, 
        expect.any(FormData), {headers:{'Content-Type' : 'multipart/form-data'}})
})
