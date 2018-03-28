# Api Design

### Description

This document attempts to create the first overview of the api for TenFour. It will be able to do the operation described below.

### Users

    * Login
    * Registration of users
    * Updating users profile
    * Retrieving all users
    * Retrieving user information

### Organizations

    * Creating an organization
    * Retrieving all organizations
    * Updating organization details
    * Retrieving organization information
        * Contains list of groups for the organization
        * Contains list of users in the organization
        * Contains list of check-ins for the organization

### Groups

    * Creating a group
    * Retrieving all groups
        * Takes into consideration the organization
    * Updating group details
    * Retrieving group information
        * Contains list of users

### Check-ins

    * Send out a check-in
    * List check-ins
        * Takes into consideration the organization

### Contacts

    * Creating a contact
    * Retrieving all contacts
        * Takes into consideration the organization
    * Updating contact details

### Api endpoints
[Endpoints](http://api.tenfour.local/docs/api)
