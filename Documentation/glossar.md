# Glossar

## Terms of use case and model instances 

- **call**
  <br>Invitation to submit an application or proposal.
  In der Regel zeitlich limitiert. Der Call-Datensatz ist das oberste Element der Datenstruktur und steht somit synonym für das Formular.
  Ein Formular besteht auf Seiten, Gruppen (auch wiederholt und in zwei Ebenen verschachtelt).
- **proposal**
  <br>Proposal record - person-bound, has various states that regulate access in the frontend and backend.
- **page** (proposal~)
  <br>Page in the proposal. Two different types of pages are implemented:
  - _Default_
    <br>Form page with introduction and help texts, and groups.
  - _Overview_
    <br>Preview page with the submission (submit) function.
- **group** (proposal~)
  <br>Contains a title, introductory and, if applicable, help text. can be nested and also repeatable (or set to repeat by default), table display...
- **item** (proposal~)
  <br>Question/topic with prompts for answering/selecting or uploading.
  <br>Consists of question/topic, introduction and help text.
  <br>There are several different types (selects, text box, text area...).
- **answer**
  <br>Answer dataset
- **option**
  <br>For selections, checkboxes and radio buttons
- **validation**
  <br>Definition of limit values, mandatory fields...