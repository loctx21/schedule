import React from 'react';
import { render, fireEvent, waitFor, waitForElementToBeRemoved, act } from '@testing-library/react'
import '@testing-library/jest-dom/extend-expect'

import PageModal from '../PageModal'

import { getManagementFacebookPage } from '../../Service/Fanpage'
jest.mock('../../Service/Fanpage')
getManagementFacebookPage.mockResolvedValueOnce({
    data : [{
        access_token: "RANDOMSTRING",
        category: "Community",
        category_list: [
            {
                id: 2612,
                name: "Community"
            }
        ],
        name : "Test fanpage",
        id: "107990999551143",
        tasks: []
    }]
})
it("help user select page to integrate", async () => {
    const submitHandler = jest.fn()
    const pageCloseHandler = jest.fn()
    
    const { debug, getByText, queryByText, getByTestId } = render(
        <PageModal 
            onSubmit={submitHandler}
            onClosed={pageCloseHandler}
        />
    )

    expect(queryByText('Choose page to intergrate')).toBeTruthy()
    expect(queryByText('Add')).toBeTruthy()
    
    await waitFor(() => expect(getManagementFacebookPage).toHaveBeenCalledTimes(1))
    expect(queryByText("Test fanpage")).toBeTruthy()
    await waitFor(() => {
        fireEvent.click(getByText('Add'))
    })
    
    expect(queryByText('Required')).toBeTruthy()
    await act(async () => {
        fireEvent.change(getByTestId('page_select'), {target : {value:"107990999551143"}})
    })
    
    await act(async () => {
        fireEvent.click(getByText('Add'))
    })
    expect(submitHandler).toBeCalledTimes(1)
    await waitForElementToBeRemoved(queryByText('Choose page to intergrate'))
})
