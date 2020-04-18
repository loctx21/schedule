import React from 'react';
import { render, cleanup, fireEvent, waitFor, act, waitForElementToBeRemoved } from '@testing-library/react'
import '@testing-library/jest-dom/extend-expect'

import EditPage from '../Edit'

import axios from 'axios'
jest.mock('axios')

describe("Edit page form works correctly", () => {
    
    let page, timezones
    beforeEach(() => {
        page = {
            id: 1,
            name : "Test page name",
            conv_index : 0,
            def_fb_album_id : "",
            schedule_time: "",
            message_reply_tmpl : "",
            post_reply_tmpl : ""
        }

        timezones = [
            {
                zone: 'America/Chicago',
                diff_from_GMT : 'America/Chicago'
            },
            { 
                zone: 'America/Houston',
                diff_from_GMT : 'America/Houstong'
            }
        ]
    })
    afterEach(cleanup)

    it("display right information", () => {
    
        const { queryByText, getByText, getByLabelText } = render(
            <EditPage 
                page={page}
                timezones={timezones}
            />
        )

        expect(getByText('Index message')).toBeTruthy()
        expect(getByLabelText('Enable')).toBeTruthy()
        expect(getByLabelText('Disable')).toBeTruthy()
        expect(getByLabelText('Time schedule')).toBeTruthy()
        expect(getByLabelText('FB default album id')).toBeTruthy()
        expect(getByLabelText('Schedule time')).toBeTruthy()
        expect(getByLabelText('Message reply template')).toBeTruthy()
        expect(getByLabelText('Comment post reply template')).toBeTruthy()
        expect(getByText('Save')).toBeTruthy()
    })

    it("Show error with invalid input", async () => {
        const { queryByText, getByText, getByLabelText } = render(
            <EditPage 
                page={page}
                timezones={timezones}
            />
        )
        
        await act(async () => {
            fireEvent.change(getByLabelText('FB default album id'), {target : {value:"asd"}})
            // fireEvent.focusOut(getByLabelText('FB default album id'))
            //Formik bug still call form submit when typeerror happen on test environment
        })

        await act(async () => {
            fireEvent.click(getByText('Save'))
        })

        expect(getByText('Default album id must be a number')).toBeTruthy()
    })

    it("Show error with invalid input", async () => {
        const { queryByText, debug, getByText, getByLabelText } = render(
            <EditPage 
                page={page}
                timezones={timezones}
            />
        )
        
        axios.put.mockResolvedValueOnce({
            data : {}
        })

        let data = {
            conv_index : 0,
            timezone: 'America/Chicago',
            def_fb_album_id : "123456",
            schedule_time: "7:00,11:30",
            message_reply_tmpl : "test message {{link}}",
            post_reply_tmpl : "test post reply  {{link}}"
        }

        await act(async () => {
            fireEvent.change(getByLabelText('Time schedule'), {target : {value: data['timezone']}})
        })
        await act(async () => {
            fireEvent.change(getByLabelText('FB default album id'), {target : {value: data['def_fb_album_id']}})
            
            fireEvent.change(getByLabelText('Schedule time'), {target : {value: data['schedule_time']}})
            fireEvent.change(getByLabelText('Message reply template'), {target : {value: data['message_reply_tmpl']}})
            fireEvent.change(getByLabelText('Comment post reply template'), {target : {value: data['post_reply_tmpl']}})
        })
        
        await act(async () => {
            fireEvent.click(getByText('Save'))
        })

        let fData = Object.assign({}, page, data);
        
        await waitFor(() => expect(axios.put).toHaveBeenCalledWith(`/api/page/${page.id}`, fData, {
            headers: {
                'Content-Type': 'application/json'
            }
        }))

        // expect(getByText("Page's data saved successfully!")).toBeTruthy();
    })

})