import React from 'react'
import { render, cleanup, fireEvent, waitFor, act } from '@testing-library/react'

import * as facebook from '../Facebook'

it("render facebook script on document",async () =>  {
    const { debug, container } = render(
        <div>
            <script></script>
        </div>
    )

    facebook.getScript()
    
    expect(container.querySelector('#facebook-jssdk')).toBeTruthy
    expect(container.querySelector('#facebook-jssdk').src).toBe("https://connect.facebook.net/en_US/sdk.js")
})

it("call get Login status and resolve the value",async () =>  {
    facebook.lib.getScript = jest.fn()
    facebook.lib.getScript.mockResolvedValue({
        getLoginStatus : (cbFunc) => {
            cbFunc(true)
        }
    })
    
    const res = await facebook.getLoginStatus()
    expect(res).toBe(true)
})

it("call FB api function with right parameters",async () =>  {
    const apiHandler = jest.fn()
    apiHandler.mockImplementation((...params) => {
        params[params.length - 1](true)
    })

    facebook.lib.getScript = jest.fn()
    facebook.lib.getScript.mockResolvedValue({
        api : apiHandler
    })
    
    const res = await facebook.api('url', { test : 1})
    
    expect(apiHandler).toHaveBeenCalledTimes(1)
    expect(apiHandler).toHaveBeenCalledWith('url', {test: 1}, expect.any(Function))
    expect(res).toBe(true)
})

