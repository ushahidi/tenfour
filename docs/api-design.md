# Api Design

### Description

This document attempts to create the first overview of the api for RollCall. It will be able to do the operation described below.

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
        * Contains list of rollcalls for the organization

### Groups

    * Creating a group
    * Retrieving all groups
        * Takes into consideration the organization
    * Updating group details
    * Retrieving group information
        * Contains list of users

### RollCalls
    
    * Send out a rollcall
    * List rollcalls
        * Takes into consideration the organization

### Api endpoints
[Endpoints](http://api.rollcall.dev/docs/api)




