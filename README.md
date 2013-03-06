# Google Calendar Scheduling for OpenVBX

Route calls via Google Calendar scheduling.

## Installation

[Download][1] the plugin and extract to /plugins

[1]: https://github.com/chadsmith/OpenVBX-Plugin-GoogleCalendar/archives/master

## Usage

### Route calls based on Free/Busy status

Create a Google Calendar and mark events as available or busy.

The calendar will be checked when a call is received and the call be routed based on your availability.

1. Add the Availability applet to your Call flow
2. Enter the URL for the calendar's ICAL file
3. Drop an applet for if the user is available
4. Drop an applet for if the user is busy

### Route calls to a scheduled number or user

Create a Google Calendar for your "on-call" employees and enter a phone number or OpenVBX user email address in the event's Where field.

The calendar will be checked when a call is received and the call will be routed to whomever is on call.

1. Add the Dial Schedule applet to your Call flow
2. Enter the URL for the calendar's ICAL file
3. (Optional) Select the Caller ID to call with
4. (Optional) Select whether to announce the caller before connecting
3. (Optional) Drop an applet for if nobody answers
4. (Optional) Drop an applet for if nobody is scheduled