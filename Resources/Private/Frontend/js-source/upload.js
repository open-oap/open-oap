
(function (window, OAP) {

    'use strict';

    const document = window.document;
    const labels = OAP.labels || {};
    const ajaxUrl = {
        base: '/index.php?',
        taskInfo: 'oap-ajax-task=info',
        taskUpload: 'oap-ajax-task=upload',
    };
    const classes = {
        removeItemButton: 'form__upload-btn-remove-file',
        downloadItemButton: 'form__upload-btn-download-file',
    };
    const selector = {
        uploadElement: '[data-oap-upload]',
        addButton: '.form__add-file-button',
        uploadButton: '.form__upload-file-button',
        clearStageButton: '.form__upload-file-clear-button',
        removeItemButton: '.' + classes.removeItemButton,
        uploadList: '.form__upload-list',
        uploadValue: '.form__upload-value',
        fileList: '.form__upload-fileslist',
        uploadMsg: '.form__upload-msgbox',
        preUpload: '.form__pre-upload',
        fileElement: '[type="file"]',
        form: '[name="proposal"]',
        fileListItem: '.form__upload-filesitem',
        fileListStaged: '.form__upload-staged',
        fileListStagedLabel: '.form__upload-staged-label',
        errorList: '.error-message',
    };
    const dataSet = {
        upload: 'oapUpload',
        savedFiles: 'oapSavedfiles',
        propertyKey: 'oapPropertykey',
        proposal: 'oapProposal',
        uploadResult: 'oapUploadvalidation',
    };
    const template = {
        listStart: '<ul class="form__upload-fileslist">',
        listEnd: '</ul>',
        listItemStart: '<li class="form__upload-filesitem">',
        listItemEnd: '</li>',
        messageOkStart: '<span class="form__upload-message form__upload-message--ok">',
        messageKoStart: '<span class="form__upload-message form__upload-message--error">',
        messageEnd: '</span>',
        listBtnDelete:
            '<svg class="form__icon form__icon--delete" width="18" height="18" focusable="false" aria-hidden="true">' +
            '<use xlink:href="/typo3conf/ext/open_oap/Resources/Public/Icons/sprite.svg#icon-delete" x="0" y="0"></use>' +
            '</svg>',
        listBtnDownload:
            '<svg class="form__icon form__icon--download" width="18" height="18" focusable="false" aria-hidden="true">' +
            '<use xlink:href="/typo3conf/ext/open_oap/Resources/Public/Icons/sprite.svg#icon-download" x="0" y="0"></use>' +
            '</svg>',
        errorSVG:
            '<svg class="form__icon form__icon--error" width="18" height="18" focusable="false" aria-hidden="true">' +
            '<use xlink:href="/typo3conf/ext/open_oap/Resources/Public/Icons/sprite.svg#icon-error" x="0" y="0"></use>' +
            '</svg>',
        stagedFile:
            '<svg class="form__icon form__icon--uploaded" width="18" height="18" focusable="false" aria-hidden="true">' +
            '<use xlink:href="/typo3conf/ext/open_oap/Resources/Public/Icons/sprite.svg#icon-file-uploaded" x="0" y="0"></use>' +
            '</svg>'
    };

    /**
     *
     * @param bytes
     * @param decimals
     * @returns {string}
     */
    const formatBytes = function (bytes, decimals = 0) {
        if (bytes === 0) {
            return '0 Bytes';
        }

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    };

    /**
     *
     * @param files
     * @param i
     * @param removeFileItemFromList
     * @returns {HTMLButtonElement}
     */
    const createRemoveButton = function (files, i, removeFileItemFromList) {
        let removeButton = document.createElement('button');

        removeButton.setAttribute('value', files[i].uid);
        removeButton.setAttribute('class', classes.removeItemButton);
        removeButton.setAttribute('type', 'button');
        removeButton.setAttribute('title', 'Remove file');
        removeButton.innerHTML = template.listBtnDelete;
        removeButton.addEventListener('click', function () {
            removeFileItemFromList(removeButton, removeButton.value);
        });
        return removeButton;
    };

    /**
     *
     * @param files
     * @param i
     * @returns {HTMLAnchorElement}
     */
    const createDownloadLink = function (files, i) {
        let downloadLink = document.createElement('a');

        downloadLink.setAttribute('href', files[i].url);
        downloadLink.setAttribute('target', '_blank');
        downloadLink.setAttribute('class', classes.downloadItemButton);
        downloadLink.innerHTML = template.listBtnDownload;
        return downloadLink;
    };

    /**
     *
     */
    const initialize = function () {

        /*
         * let xhttp = new XMLHttpRequest();
         *
         * xhttp.open('GET', ajaxUrl.base + ajaxUrl.taskInfo, true);
         * xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
         * xhttp.onreadystatechange = function () {
         *     if (this.readyState === 4 && this.status === 200) {
         *         let response = this.responseText;
         *
         *         console.log(response);
         *     }
         * };
         * xhttp.send();
         */


        const getCurrentFilesCount = function (uploadValue) {
            let currentFilesN = uploadValue.value.split(',').length;

            if (uploadValue.value === '') {
                currentFilesN = 0;
            }
            return currentFilesN;
        };

        document.querySelectorAll(selector.uploadElement).forEach(function (uploadElement) {
            const uploadOptions = uploadElement.dataset[dataSet.upload];
            const maxFiles = parseInt(JSON.parse(uploadOptions).maxFiles);
            const maxSize = parseInt(JSON.parse(uploadOptions).maxSize);

            // const propertyKey = uploadElement.dataset[dataSet.propertyKey];

            const fileElement = uploadElement.querySelector(selector.fileElement);
            const uploadList = uploadElement.querySelector(selector.uploadList);
            const errorList = uploadElement.querySelector(selector.errorList);
            const preUpload = uploadElement.querySelector(selector.preUpload);
            const uploadMsg = uploadElement.querySelector(selector.uploadMsg);
            // const fileList = uploadElement.querySelector(selector.fileList);
            const fileListStaged = uploadElement.querySelector(selector.fileListStaged);
            const fileListStagedLabel = uploadElement.querySelector(selector.fileListStagedLabel);
            // const removeItemButton = uploadElement.querySelector(selector.removeItemButton);
            const uploadValue = uploadElement.querySelector(selector.uploadValue);
            const addButton = uploadElement.querySelector(selector.addButton);
            const uploadButton = uploadElement.querySelector(selector.uploadButton);

            let data = new FormData();
            let messages = [];

            /**
             *
             */
            const checkMaxFiles = function () {
                let currentFilesN = getCurrentFilesCount(uploadValue);

                if (addButton.hasAttribute('disabled')) {
                    addButton.removeAttribute('disabled');
                }

                if ((fileElement.files.length + currentFilesN) > maxFiles) {

                    uploadValue.dataset[dataSet.uploadResult] = 'max';
                    uploadValue.setAttribute('aria-invalid', 'true');
                    messages.push({
                        'text': template.messageKoStart + labels.JSMSG_UPLOAD_ERROR_TOO_MANY_FILES + template.messageEnd,
                        'error': 1
                    });
                }
                if ((fileElement.files.length + currentFilesN) > maxFiles) {
                    addButton.setAttribute('disabled', 'disabled');
                }

            };

            /**
             *
             */
            const outputMessages = function () {
                uploadMsg.innerHTML = '';

                if (uploadButton.hasAttribute('disabled')) {
                    uploadButton.removeAttribute('disabled');
                }

                if (messages.length > 0) {
                    let output = '<ul>';

                    for (let i = 0; i < messages.length; i++) {
                        output += '<li>' + messages[i].text + '</li>';
                        if (messages[i].error === 1 && !uploadButton.hasAttribute('disabled')) {
                            uploadButton.setAttribute('disabled', 'disabled');
                        }
                    }
                    output += '</ul>';
                    uploadMsg.innerHTML = output;
                    messages.length = 0;
                }
            };

            const removeFileItemFromList = function (thisRemoveButton) {
                messages.length = 0;

                // get value from fileElement
                let values = uploadValue.value.split(',');

                // remove certain element - placed in button element (value)
                for (let i = 0; i < values.length; i++) {
                    if (values[i] === thisRemoveButton.value) {
                        values.splice(i, 1);
                        i = i - 1;
                    }
                }
                // remove id from value
                uploadValue.value = values.join(',');

                if (values.length < maxFiles) {
                    addButton.style.display = 'block';
                }

                // remove list item
                thisRemoveButton.closest(selector.fileListItem).remove();

                // message
                messages.push({
                    'text': template.messageOkStart + labels.JSMSG_UPLOAD_FILE_REMOVED + template.messageEnd,
                    'error': 0
                });
                outputMessages();
            };

            const clearUploadStage = function () {
                // clear fileList in FileElement to avoid uploading again via save
                fileElement.value = '';
                preUpload.style.display = 'none';
                if (uploadList) {
                    uploadList.innerHTML = '';
                }
                messages.length = 0;
                if (errorList) {
                    errorList.innerHTML = '';
                }
                uploadValue.removeAttribute('aria-invalid');
                uploadValue.dataset[dataSet.uploadResult] = '';

                checkMaxFiles();
                outputMessages();

            };

            // remove file id from answer value
            uploadElement.querySelectorAll(selector.removeItemButton).forEach(function (removeButton) {

                removeButton.addEventListener('click', function () {
                    removeFileItemFromList(removeButton, uploadValue);
                    checkMaxFiles();
                });
            });

            // binding button to remove staged files
            uploadElement.querySelector(selector.clearStageButton).addEventListener('click', function () {
                clearUploadStage();
            });

            // start file select dialogue
            addButton.addEventListener('click', function () {
                fileElement.click();
            }, false);

            // changes from file select
            fileElement.addEventListener('change', function () {

                let listHtml = template.listStart;
                let icon = '';

                uploadMsg.innerHTML = '';

                for (let i = 0, files = fileElement.files; i < fileElement.files.length; i++) {
                    // add to data memory for form transmit
                    data.append('file-' + [...data.keys()].length, files[i], files[i].name);

                    if (files[i].size > maxSize) {
                        icon = template.messageKoStart + template.errorSVG + template.messageEnd;
                        fileElement.dataset[dataSet.uploadResult] = 'size';
                        if (!uploadValue.hasAttribute('aria-invalid')) {
                            uploadValue.setAttribute('aria-invalid', 'true');
                        }
                        messages.push({
                            'text': template.messageKoStart + template.errorSVG + ' ' + labels.JSMSG_UPLOAD_ERROR_FILE_SIZE.replace('%1', formatBytes(maxSize)) + template.messageEnd,
                            'error': 1
                        });
                        uploadButton.setAttribute('disabled', 'disabled');
                    }
                    // show in frontend as a list
                    listHtml += template.listItemStart + icon + ' ' + files[i].name + ' (' + formatBytes(files[i].size) + ') ' + template.listItemEnd;
                }

                listHtml += template.listEnd;
                uploadList.innerHTML = listHtml;
                preUpload.style.display = 'block';

                checkMaxFiles();
                outputMessages();
            });

            // upload starts with click on upload button
            uploadButton.addEventListener('click', function () {

                let totalfiles = fileElement.files.length;

                uploadMsg.innerHTML = '';

                if (totalfiles > 0) {

                    if (!uploadButton.hasAttribute('disabled')) {
                        uploadButton.setAttribute('disabled', 'disabled');
                    }

                    let form = uploadElement.closest(selector.form);
                    let proposalId =  form.dataset[dataSet.proposal];
                    let answerId =  uploadElement.dataset[dataSet.propertyKey];
                    let formData = new FormData();

                    // Read selected files
                    for (let index = 0; index < totalfiles; index++) {
                        formData.append('files[]', fileElement.files[index]);
                    }

                    let xhttp = new XMLHttpRequest();
                    let url = ajaxUrl.base + ajaxUrl.taskUpload + '&proposal=' + proposalId + '&answer=' + answerId;

                    // Set POST method and ajax file path
                    xhttp.open('POST', url, true);

                    // call on request changes state
                    xhttp.onreadystatechange = function () {
                        if (this.readyState === 4 && this.status === 200) {

                            let response = JSON.parse(this.responseText);

                            clearUploadStage();

                            for (let i = 0, files = response.data.files; i < response.data.files.length; i++) {
                                let listItem = document.createElement('li');

                                listItem.setAttribute('class', 'form__upload-filesitem');
                                let removeButton = createRemoveButton(files, i, removeFileItemFromList);
                                let downloadLink = createDownloadLink(files, i);

                                listItem.innerHTML = template.stagedFile + ' ' + files[i].name + ' (' + formatBytes(files[i].size) + ')';
                                listItem.append(removeButton);
                                listItem.append(downloadLink);
                                fileListStaged.style.display = 'block';
                                fileListStagedLabel.style.display = 'block';
                                fileListStaged.appendChild(listItem);
                            }

                            messages.push({
                                'text': template.messageOkStart + labels.JSMSG_UPLOAD_UPLOADED + template.messageEnd,
                                'error': 0
                            });

                            let valuesOld = uploadValue.value.split(',');

                            if (uploadValue.value === '') {
                                valuesOld.length = 0;
                            }
                            let valuesNew = [...valuesOld, ...response.data.ids];

                            if (valuesNew.length >= maxFiles) {
                                // hide add button
                                uploadValue.setAttribute('aria-invalid', 'false');
                                addButton.style.display = 'none';
                                messages.push({
                                    'text': labels.JSMSG_UPLOAD_MAX_FILES_REACHED,
                                    'error': 0
                                });
                            }
                            let value = valuesNew.join(',');

                            uploadValue.setAttribute('value', value);

                            outputMessages();
                        }
                        if (uploadButton.hasAttribute('disabled')) {
                            uploadButton.removeAttribute('disabled');
                        }

                    };

                    // Send request with data
                    xhttp.send(formData);

                }
            });

            // initialize this upload element
            checkMaxFiles();


        });
    };

    initialize();

}(this, this.OAP || {}));
