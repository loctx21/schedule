import React from 'react';
import { render, fireEvent, waitFor, act, waitForElementToBeRemoved } from '@testing-library/react'
import '@testing-library/jest-dom/extend-expect'

import DashboardIndex from '../Index'

it("display login to facebook option", () => {

    let fb_logined = false;
    let fb_login_url = 'acb'

    const { queryByText } = render(
        <DashboardIndex 
            fb_logined={fb_logined} 
            fb_login_url={fb_login_url}
            pages={[]}
        />
    )

    expect(queryByText('Login to Facebook')).toBeTruthy()
    expect(queryByText('Add Fanpages')).toBeFalsy()
})

import { getManagementFacebookPage, addManagedFanpage } from '../../Service/Fanpage'
jest.mock('../../Service/Fanpage')
it("handle add page", async () => {

    let fb_logined = true
    let fb_login_url = 'acb'

    const { getByText, queryByText, getByTestId, debug } = render(
        <DashboardIndex 
            fb_logined={fb_logined} 
            fb_login_url={fb_login_url}
            pages={[]}
        />
    )

    expect(queryByText('Login to Facebook')).toBeFalsy()
    expect(queryByText('Refresh Token')).toBeTruthy()
    expect(queryByText('Add Fanpages')).toBeTruthy()

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

    fireEvent.click(getByText('Add Fanpages'));

    addManagedFanpage.mockResolvedValueOnce({
        id : 1,
        name : "Test fanpage",
        access_token : "RANDOMSTRING",
        fb_id : "107990999551143",
        access_token: "RANDOMSTRING"
    })
    await waitFor(() => queryByText('Choose page to intergrate'))
    await waitFor(() => expect(getManagementFacebookPage).toHaveBeenCalledTimes(1))
    await act(async () => {
        fireEvent.change(getByTestId('page_select'), {target : {value:"107990999551143"}})
    })
    await act(async () => {
        fireEvent.click(getByText('Add'))
    })
    await waitForElementToBeRemoved(queryByText('Choose page to intergrate'))
    
    expect(addManagedFanpage).toBeCalledTimes(1)
    expect(queryByText('Test fanpage')).toBeTruthy()
})