# User and Usergroups

## Access restriction to proposal folders

```typo3_typoscript
# TSconfig in usergroup 5
oap.access.5 = 24,84
```

Explanation:

- oap.access.X - X -> id of usergroup for merge multiple usergroup settings
- value -> comma separeted list of PIDs for granted folder

If there is no entry in the groups (in combination of all related groups) or the BE user is an admin, there is no access control to the folders.

| No  | usergroup             | DBMounts       | BE-Modul                         | TSConfig             | Inherit Group                                                  |
|:----|-----------------------|----------------|----------------------------------|----------------------|----------------------------------------------------------------|
| 10  | oap_clerk_common      | FE-User        | FE-User, OAP Proposals           |                      |                                                                |
| 11  | oap_proposal_access_1 |                |                                  | oap.access.A = X1,X2 |                                                                |
| 12  | oap_proposal_access_2 |                |                                  | oap.access.B = X3    |                                                                |
| 13  | oap_proposal_access_3 |                |                                  | oap.access.C = X2,X4 |                                                                |
|     |                       |                |                                  |                      |                                                                |
| 20  | oap_editor            | OAP-Pool       | web_list, OAP Forms              |                      |                                                                |
|     |                       |                |                                  |                      |                                                                |
| 30  | oap_website_editor    | OAP-Startseite | web_layout, web_list, Filelist   |                      |                                                                |
|     |                       |                |                                  |                      |                                                                |
| 40  | oap_clerk 1           |                |                                  |                      | oap_clerk_common, oap_proposal_access_1                        |
| 41  | oap_clerk 2           |                |                                  |                      | oap_clerk_common, oap_proposal_access_2                        |
| 42  | oap_clear 3           |                |                                  |                      | oap_clear_common, oap_proposal_access_2, oap_proposal_access_3 |
|     |                       |                |                                  |                      |                                                                |
 
Description

Use only groups 40-42 for clerk user. You can combine them with 20 or/and 30.

Common access of clerk account are proposals and fe-user.

For certain proposal folder you need to give access bei TSConfig to these folders.

