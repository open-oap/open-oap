# Backend Site Management

## General comment
Named constants "constant": are set in the template constants.
See also [constants](../configuration/constants.md).

Names "TSconfig" are set in tsconfig
See also [tsconfig](../configuration/tsconfig.md).

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
      <br>constant: dashboardPageId
        > Plugin: oap dashboard (Use plugin options to edit text of status mails)
        - Proposals
          <br>constant: proposalsPageId
            > Plugin: oap proposals
        - Proposal Form
          <br>constant: formPageId
            > Plugin: oap form
        - Notifications
          <br>constant: notificationsPageId
            > Plugin: oap notifications

    - Profile **[ACCESS (extended to subpages): Frontend Group APPLICANT]**
        > Plugin: oap applicant
        - Master data edit **[ACCESS: Frontend Group APPLICANT]**
          <br>constant: masterdataEditPageId
            > Plugin: oap applicantform

    - **SysFolder**: Metanavigation
        - Contact **[ACCESS: Frontend Group APPLICANT]**
        - Imprint **[ACCESS: Frontend Group APPLICANT]**
        - Data protection **[ACCESS: Frontend Group APPLICANT]**

    - **SysFolder**: OAP-Pool
        - **SysFolder**: Calls
            <br>(*record type:* Call )
            <br>constant: callPoolId
        - **SysFolder**: Pages
            <br>(*record type:* Form Page )
            <br>TSconfig: tx_openoap_domain_model_call
        - **SysFolder**: Groups
            <br>(*record type:* Form Group & Group Title )
            <br>TSconfig: tx_openoap_domain_model_formpage, tx_openoap_domain_model_formgroup
        - **SysFolder**: Items
            <br>(*record type:* Form Item )
            <br>constant: itemsPoolId, TSconfig: tx_openoap_domain_model_call, tx_openoap_domain_model_formgroup
        - **SysFolder**: Options
            <br>(*record type:* Item Option )
            <br>TSconfig: tx_openoap_domain_model_formitem
        - **SysFolder**: Validators
            <br>(*record type:* Item Validator )
            <br>TSconfig: tx_openoap_domain_model_itemvalidator
        - **SysFolder**: Modificators
            <br>(*record type:* Form Modificator )
            <br>Tsconfig: tx_openoap_domain_model_formmodificator

    - **SysFolder** : OAP-Proposaldata
        - **SysFolder**: Comments
            <br>(*record type:* Comment )
            <br>constant: commentsPoolId, TSconfig: tx_openoap_domain_model_proposal, tx_openoap_domain_model_answer
        - **SysFolder**: Answers
            <br>(*record type:* Answer )
            <br>constant: answersPoolId, TSconfig: tx_openoap_domain_model_proposal
        - **SysFolder**: Proposals
            <br>(*record type:* Proposal )
            <br>constant: proposalPoolId, TSconfig: tx_openoap_domain_model_call
       - **SysFolder**: FE-User
            <br>( Frontend Group )
            <br>( Frontend User )
            <br>TSconfig: tx_openoap_domain_model_proposal

    - **SysFolder**: System
        - Logout - You are logged out. Please close tab or window!
        - Login
            > Plugin: Login Form
        - 404
            > Text

- **SysFolder**: OAP - Global Pool
    - **SysFolder**: Options
        <br>(*record type:* Item Option )
    - **SysFolder**: Validators
        <br>(*record type:* Item Validator )
