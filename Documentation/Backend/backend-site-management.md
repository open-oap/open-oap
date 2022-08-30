# Backend Site Management

## Site tree and relevant content

- Client Startpage
    > Text (Welcome Text)
    >
    > Container 50-50
    >> Plugin: Login Form
    >>
    >> Text (Registration Linkbox) **[ACCESS: Hide at login]**
    - Registration
        > Plugin: FE_Manager | View: Invitation
        - Login
            > Plugin: Login Form
        - Master data (Registration Step 3) **[ACCESS: Frontend Group APPLICANT]**
            > Plugin: oap applicantform
    
    - Dashboard **[ACCESS (extended to subpages): Frontend Group APPLICANT]**
        > Plugin: oap dashboard (Use plugin options to edit text of status mails)
        - Proposals
            > Plugin: oap proposals
        - Proposal Form
            > Plugin: oap form
        - Notifications
            > Plugin: oap notifications

    - Profile **[ACCESS (extended to subpages): Frontend Group APPLICANT]**
        > Plugin: oap applicant
        - Master data edit **[ACCESS: Frontend Group APPLICANT]**
            > Plugin: oap applicantform

    - **SysFolder**: Metanavigation
        - Contact **[ACCESS: Frontend Group APPLICANT]**
        - Imprint **[ACCESS: Frontend Group APPLICANT]**
        - Data protection **[ACCESS: Frontend Group APPLICANT]**

    - **SysFolder**: OAP-Pool
        - **SysFolder**: Calls
            <br>( *record type:* Call )
        - **SysFolder**: Pages
            <br>( *record type:* Form Page )
        - **SysFolder**: Groups
            <br>( *record type:* Form Group )
        - **SysFolder**: Items
            <br>( *record type:* Form Item )
        - **SysFolder**: Options
            <br>( *record type:* Item Option )
        - **SysFolder**: Validators
            <br>( *record type:* Item Validator )
        - **SysFolder**: LogicAtoms
            <br>( *record type:* Logic Atom )

    - **SysFolder** : OAP-Proposaldata
        - **SysFolder**: Comments
            <br>( *record type:* Comment )
        - **SysFolder**: Answers
            <br>( *record type:* Answer )
        - **SysFolder**: Proposals
            <br>( *record type:* Proposal )
        - **SysFolder**: FE-User
            <br>( Frontend Group )
            <br>( Frontend User )

    - **SysFolder**: System
        - Logout - You are logged out. Please close tab or window!
        - Login
            > Plugin: Login Form
        - 404
            > Text

- **SysFolder**: OAP - Global Pool
    - **SysFolder**: Options
        <br>( *record type:* Item Option )
    - **SysFolder**: Validators
        <br>( *record type:* Item Validator )
