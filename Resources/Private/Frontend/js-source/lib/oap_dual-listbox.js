/*
 *  https://github.com/maykinmedia/dual-listbox
 */
    'use strict';

    const MAIN_BLOCK = 'dual-listbox';

    const CONTAINER_ELEMENT = 'dual-listbox__container';
    const AVAILABLE_ELEMENT = 'dual-listbox__available';
    const SELECTED_ELEMENT = 'dual-listbox__selected';
    const TITLE_ELEMENT = 'dual-listbox__title';
    const ITEM_ELEMENT = 'dual-listbox__item';
    const BUTTONS_ELEMENT = 'dual-listbox__buttons';
    const BUTTON_ELEMENT = 'dual-listbox__button';
    const SEARCH_ELEMENT = 'dual-listbox__search';

    const SELECTED_MODIFIER = 'dual-listbox__item--selected';

    /**
     * Dual select interface allowing the user to select items from a list of provided options.
     * @class
     */
    class DualListbox {
        constructor(selector, options = {}) {

            this.setDefaults();
            this.selected = [];
            this.available = [];

            if (DualListbox.isDomElement(selector)) {
                this.select = selector;
            } else {
                this.select = document.querySelector(selector);
            }

            this._initOptions(options);
            this._initReusableElements();
            this._splitOptions(this.select.options);
            if (options.options !== undefined) {
                this._splitOptions(options.options);
            }
            this._buildDualListbox(this.select.parentNode);
            this._addActions();

            this.redraw();
        }

        /**
         * Sets the default values that can be overwritten.
         */
        setDefaults() {
            this.addEvent = null; // TODO: Remove in favor of eventListener
            this.removeEvent = null; // TODO: Remove in favor of eventListener
            this.availableTitle = 'Available options';
            this.selectedTitle = 'Selected options';

            this.showAddButton = true;
            this.addButtonText = window.OAP.labels.JSLABEL_DUALLIST_ADD || 'add';

            this.showRemoveButton = true;
            this.removeButtonText = window.OAP.labels.JSLABEL_DUALLIST_REMOVE || 'remove';

            this.showAddAllButton = true;
            this.addAllButtonText = window.OAP.labels.JSLABEL_DUALLIST_ADD_ALL || 'add all';

            this.showRemoveAllButton = true;
            this.removeAllButtonText = window.OAP.labels.JSLABEL_DUALLIST_REMOVE_ALL || 'remove all';

            this.searchPlaceholder = window.OAP.labels.JSLABEL_DUALLIST_SEARCH || 'search';
        }

        /**
         * Add eventListener to the dualListbox element.
         *
         * @param {String} eventName
         * @param {function} callback
         */
        addEventListener(eventName, callback) {
            this.dualListbox.addEventListener(eventName, callback);
        }

        /**
         * Add the listItem to the selected list.
         *
         * @param {NodeElement} listItem
         */
        addSelected(listItem) {
            if (this._isAvailableDisabled()) {
                return;
            }

            let index = this.available.indexOf(listItem);
            let errorMessage = this.dualListbox.nextSibling.nextSibling;

            if (index > -1) {
                this.available.splice(index, 1);
                this.selected.push(listItem);
                this._selectOption(listItem.dataset.id);
                this.redraw();
                errorMessage.style.display = 'none';

                setTimeout(() => {
                    let event = document.createEvent("HTMLEvents");
                    event.initEvent("added", false, true);
                    event.addedElement = listItem;
                    this.dualListbox.dispatchEvent(event);
                }, 0);
            }
        }

        /**
         * Redraws the Dual listbox content
         */
        redraw() {
            this.updateAvailableListbox();
            this.updateSelectedListbox();
        }

        /**
         * Removes the listItem from the selected list.
         *
         * @param {NodeElement} listItem
         */
        removeSelected(listItem) {
            let index = this.selected.indexOf(listItem);
            let errorMessage = this.dualListbox.nextSibling.nextSibling;

            if (index > -1) {
                this.selected.splice(index, 1);
                this.available.push(listItem);
                this._deselectOption(listItem.dataset.id);
                this.redraw();
                errorMessage.style.display = 'block';

                setTimeout(() => {
                    let event = document.createEvent("HTMLEvents");
                    event.initEvent("removed", false, true);
                    event.removedElement = listItem;
                    this.dualListbox.dispatchEvent(event);
                }, 0);
            }
            // TB20220427 - Problems with empty selection lists - the form element is then not empty
            if (this.selected.length === 0) {
                this.select.value = '';
            }
        }

        /**
         * Filters the listboxes with the given searchString.
         *
         * @param {Object} searchString
         * @param dualListbox
         */
        searchLists(searchString, dualListbox) {
            let items = dualListbox.querySelectorAll(`.${ITEM_ELEMENT}`);
            let lowerCaseSearchString = searchString.toLowerCase();

            for (let i = 0; i < items.length; i++) {
                let item = items[i];
                if (item.textContent.toLowerCase().indexOf(lowerCaseSearchString) === -1) {
                    item.style.display = 'none';
                } else {
                    item.style.display = 'list-item';
                }
            }
        }

        /**
         * Update the elements in the available listbox;
         */
        updateAvailableListbox() {
            this._updateListbox(this.availableList, this.available);
        }

        /**
         * Update the elements in the selected listbox;
         */
        updateSelectedListbox() {
            this._updateListbox(this.selectedList, this.selected);
        }

        //
        //
        // PRIVATE FUNCTIONS
        //
        //

        /**
         * Action to set all listItems to selected.
         */
        _actionAllSelected(event) {
            event.preventDefault();

            if (this._isAvailableDisabled()) {
                return;
            }

            let selected = this.available.filter((item) => item.style.display !== "none");
            selected.forEach((item) => this.addSelected(item));
        }

        /**
         * Update the elements in the listbox;
         */
        _updateListbox(list, elements) {
            while (list.firstChild) {
                list.removeChild(list.firstChild);
            }

            for (let i = 0; i < elements.length; i++) {
                let listItem = elements[i];
                list.appendChild(listItem);
            }
        }

        /**
         * Action to set one listItem to selected.
         */
        _actionItemSelected(event) {
            event.preventDefault();

            if (this._isAvailableDisabled()) {
                return;
            }

            let selected = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);
            if (selected) {
                this.addSelected(selected);
            }
        }

        /**
         * Action to set all listItems to available.
         */
        _actionAllDeselected(event) {
            event.preventDefault();

            let deselected = this.selected.filter((item) => item.style.display !== "none");
            deselected.forEach((item) => this.removeSelected(item));
        }

        /**
         * Action to set one listItem to available.
         */
        _actionItemDeselected(event) {
            event.preventDefault();

            let selected = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);
            if (selected) {
                this.removeSelected(selected);
            }
        }

        /**
         * Action when double clicked on a listItem.
         */
        _actionItemDoubleClick(listItem, event = null) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (this.selected.indexOf(listItem) > -1) {
                this.removeSelected(listItem);
            } else {
                if (this._isAvailableDisabled()) {
                    return;
                }

                this.addSelected(listItem);
            }
        }

        /**
         * Action when single clicked on a listItem.
         */
        _actionItemClick(listItem, dualListbox, event = null) {
            if (event) {
                event.preventDefault();
            }

            let items = dualListbox.querySelectorAll(`.${ITEM_ELEMENT}`);

            for (let i = 0; i < items.length; i++) {
                let value = items[i];
                if (value !== listItem) {
                    value.classList.remove(SELECTED_MODIFIER);
                    value.setAttribute('aria-selected', 'false');
                }
            }

            if (listItem.classList.contains(SELECTED_MODIFIER)) {
                listItem.classList.remove(SELECTED_MODIFIER);
                listItem.setAttribute('aria-selected', 'false');
            } else {
                listItem.classList.add(SELECTED_MODIFIER);
                listItem.setAttribute('aria-selected', 'true');

                // Update activedescendant on corresponding listbox
                const list = listItem.parentElement;

                if (list && (list === this.availableList || list === this.selectedList)) {
                    list.setAttribute('aria-activedescendant', listItem.id);
                }
            }
        }

        /**
         * @Private
         * Adds the needed actions to the elements.
         */
        _addActions() {
            this._addButtonActions();
            this._addSearchActions();
            this._addKeyboardActions();
        }

        /**
         * Adds the actions to the buttons that are created.
         */
        _addButtonActions() {
            this.add_all_button.addEventListener('click', (event) => this._actionAllSelected(event));
            this.add_button.addEventListener('click', (event) => this._actionItemSelected(event));
            this.remove_button.addEventListener('click', (event) => this._actionItemDeselected(event));
            this.remove_all_button.addEventListener('click', (event) => this._actionAllDeselected(event));
        }

        /**
         * Adds the click items to the listItem.
         *
         * @param {Object} listItem
         */
        _addClickActions(listItem) {
            listItem.addEventListener('dblclick', (event) => this._actionItemDoubleClick(listItem, event));
            listItem.addEventListener('click', (event) => this._actionItemClick(listItem, this.dualListbox, event));
            return listItem;
        }

        /**
         * @Private
         * Adds the actions to the search input.
         */
        _addSearchActions() {
            this.search_left.addEventListener('change', (event) => this.searchLists(event.target.value, this.availableList));
            this.search_left.addEventListener('keyup', (event) => this.searchLists(event.target.value, this.availableList));
            this.search_left.addEventListener('keydown', (event) => this._handleSearchKeydown(event, this.availableList));
            this.search_right.addEventListener('change', (event) => this.searchLists(event.target.value, this.selectedList));
            this.search_right.addEventListener('keyup', (event) => this.searchLists(event.target.value, this.selectedList));
            this.search_right.addEventListener('keydown', (event) => this._handleSearchKeydown(event, this.selectedList));
        }

        /**
         * @Private
         * Keyboard interactions for accessibility.
         */
        _addKeyboardActions() {
            // Focus handlers: if no active option exists in the focused list, select first visible
            const onFocusList = (list) => {
                // When the underlying select is disabled, do not alter active option via keyboard focus
                if (this.select && this.select.disabled) {
                    return;
                }

                const current = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);

                if (!current || current.parentElement !== list || current.style.display === 'none') {
                    const first = this._getFirstVisibleItem(list);

                    if (first) {
                        this._setActiveOptionGlobal(first);
                    }
                }

                // Set activedescendant on the focused list to the active item if present
                const active = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);

                if (active && active.parentElement === list) {
                    list.setAttribute('aria-activedescendant', active.id);
                }
            };

            this.availableList.addEventListener('focus', () => onFocusList(this.availableList));
            this.selectedList.addEventListener('focus', () => onFocusList(this.selectedList));

            // Keydown handlers
            this.availableList.addEventListener('keydown', (e) => this._handleListKeydown(e, 'available'));
            this.selectedList.addEventListener('keydown', (e) => this._handleListKeydown(e, 'selected'));
        }

        _handleSearchKeydown(event, dualListBox) {
            // ArrowDown in the search field should focus the listbox below
            if (event.key === 'ArrowDown') {
                event.preventDefault();

                if (dualListBox && typeof dualListBox.focus === 'function') {
                    dualListBox.focus();
                }
            }

            // Enter/NumpadEnter in the search field must not submit the form; focus the listbox if it has visible items
            else if (event.key === 'Enter' || event.key === 'NumpadEnter') {
                event.preventDefault();

                const hasItems = dualListBox && this._getVisibleItems(dualListBox).length > 0;

                if (hasItems && typeof dualListBox.focus === 'function') {
                    dualListBox.focus();
                }
            }
        }

        _handleListKeydown(event, which) {
            // Block all keyboard interactions when the component is disabled
            if (this.select && this.select.disabled) {
                return;
            }

            const list = which === 'available' ? this.availableList : this.selectedList;
            const otherList = which === 'available' ? this.selectedList : this.availableList;
            const key = event.key;
            const prevent = () => { event.preventDefault(); event.stopPropagation(); };

            if (['ArrowDown','ArrowUp','Home','End','PageDown','PageUp'].includes(key)) {
                prevent();

                // ArrowUp in the listbox should focus the search field if the first visible item is already selected
                if (key === 'ArrowUp') {
                    const visible = this._getVisibleItems(list);
                    const current = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);
                    const inThisList = current && current.parentElement === list;
                    const index = inThisList ? visible.indexOf(current) : -1;

                    if (index === 0) {
                        const searchInput = which === 'available' ? this.search_left : this.search_right;

                        if (searchInput && typeof searchInput.focus === 'function') {
                            searchInput.focus();
                        }

                        return;
                    }
                }

                this._moveActive(list, key);

                // reflect active item on the focused listbox
                const active = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);

                if (active && active.parentElement === list) {
                    list.setAttribute('aria-activedescendant', active.id);
                }
            }

            else if (key === 'Enter' || key === ' ') {
                prevent();

                const active = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);

                if (!active) {
                    return;
                }

                if (which === 'available' && active.parentElement === list) {
                    if (this._isAvailableDisabled()) {
                        return;
                    }

                    // Determine the next visible item relative to the current active one
                    const visibleBefore = this._getVisibleItems(list);
                    let nextIndex = visibleBefore.indexOf(active);

                    this.addSelected(active);

                    // After transfer, select the next visible item (or previous if at end)
                    const visibleAfter = this._getVisibleItems(list);
                    let next = null;

                    if (visibleAfter.length) {
                        if (nextIndex === -1) {
                            nextIndex = 0;
                        }

                        if (nextIndex >= visibleAfter.length) {
                            nextIndex = visibleAfter.length - 1;
                        }

                        next = visibleAfter[nextIndex];
                    }

                    if (next) {
                        this._setActiveOptionGlobal(next);
                        list.setAttribute('aria-activedescendant', next.id);
                    }
                }
                else if (which === 'selected' && active.parentElement === list) {
                    // Determine the next visible item relative to the current active one
                    const visibleBefore = this._getVisibleItems(list);
                    let nextIndex = visibleBefore.indexOf(active);

                    this.removeSelected(active);

                    // After transfer, select the next visible item (or previous if at end)
                    const visibleAfter = this._getVisibleItems(list);
                    let next = null;

                    if (visibleAfter.length) {
                        if (nextIndex === -1) {
                            nextIndex = 0;
                        }

                        if (nextIndex >= visibleAfter.length) {
                            nextIndex = visibleAfter.length - 1;
                        }

                        next = visibleAfter[nextIndex];
                    }

                    if (next) {
                        this._setActiveOptionGlobal(next);
                        list.setAttribute('aria-activedescendant', next.id);
                    }
                }
            }

            // ArrowRight/ArrowLeft should only switch focus to the opposite listbox (no transfer)
            else if (key === 'ArrowRight' && which === 'available') {
                prevent();
                otherList.focus();
            }

            else if (key === 'ArrowLeft' && which === 'selected') {
                prevent();

                if (this._isAvailableDisabled()) {
                    return;
                }

                otherList.focus();
            }

            // Typing in a list should focus the respective search field and trigger filtering
            else {
                // Ignore composing and modifier combinations
                if (event.isComposing || event.ctrlKey || event.metaKey || event.altKey) {
                    return;
                }

                const searchInput = which === 'available' ? this.search_left : this.search_right;

                // Handle backspace (remove the last character)
                if (key === 'Backspace') {
                    prevent();

                    searchInput.value = (searchInput.value || '').slice(0, -1);
                }

                // Append typed character
                else if (typeof key === 'string' && key.length === 1) {
                    prevent();

                    searchInput.value = (searchInput.value || '') + key;
                }
                else {
                    // Do not interfere with other keys
                    return;
                }

                // Focus search field, place caret at the end, and trigger search
                try { searchInput.focus(); } catch (e) {}
                try {
                    const len = searchInput.value.length;

                    if (typeof searchInput.setSelectionRange === 'function') {
                        searchInput.setSelectionRange(len, len);
                    }
                } catch (e) {}

                const targetList = which === 'available' ? this.availableList : this.selectedList;
                this.searchLists(searchInput.value, targetList);
            }
        }

        _getVisibleItems(list) {
            const items = list.querySelectorAll(`.${ITEM_ELEMENT}`);
            const visible = [];

            for (let i = 0; i < items.length; i++) {
                const it = items[i];

                if (it.style.display !== 'none') {
                    visible.push(it);
                }
            }

            return visible;
        }

        _getFirstVisibleItem(list) {
            const visible = this._getVisibleItems(list);

            return visible.length ? visible[0] : null;
        }

        _moveActive(list, key) {
            const visible = this._getVisibleItems(list);

            if (!visible.length) {
                return;
            }

            const current = this.dualListbox.querySelector(`.${SELECTED_MODIFIER}`);
            let index = current && current.parentElement === list ? visible.indexOf(current) : -1;

            if (key === 'Home' || key === 'PageUp') {
                index = 0;
            }
            else if (key === 'End' || key === 'PageDown') {
                index = visible.length - 1;
            }
            else if (key === 'ArrowDown') {
                index = Math.min(index + 1, visible.length - 1);

                if (index === -1) {
                    index = 0;
                }
            }
            else if (key === 'ArrowUp') {
                index = Math.max(index - 1, 0);

                if (index === -1) {
                    index = 0;
                }
            }

            const target = visible[index];

            if (target) {
                this._setActiveOptionGlobal(target);

                // ensure in view
                if (typeof target.scrollIntoView === 'function') {
                    target.scrollIntoView({ block: 'nearest' });
                }
            }
        }

        _setActiveOptionGlobal(item) {
            const items = this.dualListbox.querySelectorAll(`.${ITEM_ELEMENT}`);

            for (let i = 0; i < items.length; i++) {
                const it = items[i];

                if (it === item) {
                    it.classList.add(SELECTED_MODIFIER);
                    it.setAttribute('aria-selected', 'true');
                }
                else {
                    it.classList.remove(SELECTED_MODIFIER);
                    it.setAttribute('aria-selected', 'false');
                }
            }
        }

        _isAvailableDisabled() {
            if (!this.availableList) {
                return false;
            }

            return this.availableList.getAttribute('aria-disabled') === 'true';
        }

        /**
         * @Private
         * Builds the Dual listbox and makes it visible to the user.
         */
        _buildDualListbox(container) {
            this.select.style.display = 'none';

            this.dualListBoxContainer.appendChild(this._createList(this.search_left, this.availableListTitle, this.availableList));
            this.dualListBoxContainer.appendChild(this.buttons);
            this.dualListBoxContainer.appendChild(this._createList(this.search_right, this.selectedListTitle, this.selectedList));

            this.dualListbox.appendChild(this.dualListBoxContainer);

            container.insertBefore(this.dualListbox, this.select);
        }

        /**
         * Creates list with the header.
         */
        _createList(search, header, list) {
            let result = document.createElement('div');
            result.appendChild(search);
            result.appendChild(header);
            result.appendChild(list);
            return result;
        }

        /**
         * Creates the buttons to add/remove the selected item.
         */
        _createButtons() {
            this.buttons = document.createElement('div');
            this.buttons.classList.add(BUTTONS_ELEMENT);

            this.add_all_button = document.createElement('button');
            this.add_all_button.innerHTML = this.addAllButtonText;

            this.add_button = document.createElement('button');
            this.add_button.innerHTML = this.addButtonText;

            this.remove_button = document.createElement('button');
            this.remove_button.innerHTML = this.removeButtonText;

            this.remove_all_button = document.createElement('button');
            this.remove_all_button.innerHTML = this.removeAllButtonText;

            const options = {
                showAddAllButton: this.add_all_button,
                showAddButton: this.add_button,
                showRemoveButton: this.remove_button,
                showRemoveAllButton: this.remove_all_button,
            };

            for (let optionName in options) {
                if(optionName) {
                    const option = this[optionName];
                    const button = options[optionName];

                    button.setAttribute('type', 'button');
                    button.classList.add(BUTTON_ELEMENT);

                    if (option) {
                        this.buttons.appendChild(button);
                    }
                }
            }
        }

        /**
         * @Private
         * Creates the listItem out of the option.
         */
        _createListItem(option) {
            let listItem = document.createElement('li');

            listItem.classList.add(ITEM_ELEMENT);
            listItem.innerHTML = option.text.replace(/\|/g,'<br>');
            listItem.dataset.id = option.value;
            listItem.setAttribute('role', 'option');
            listItem.setAttribute('aria-selected', 'false');
            listItem.setAttribute('tabindex', '-1');
            listItem.id = this.select.id + '-option-' + String(option.value).replace(/\s+/g, '_');

            this._addClickActions(listItem);

            return listItem;
        }

        /**
         * @Private
         * Creates the search input.
         */
        _createSearchLeft() {
            this.search_left = document.createElement('input');
            this.search_left.id = this.select.id + '-search-available';
            this.search_left.classList.add(SEARCH_ELEMENT);
            this.search_left.placeholder = this.searchPlaceholder;
            this.search_left.setAttribute('aria-label', this.searchPlaceholder + (this.availableTitle ? ': ' + this.availableTitle : ''));
            this.search_left.setAttribute('aria-controls', this.select.id + '-available-list');
        }

        /**
         * @Private
         * Creates the search input.
         */
        _createSearchRight() {
            this.search_right = document.createElement('input');
            this.search_right.id = this.select.id + '-search-selected';
            this.search_right.classList.add(SEARCH_ELEMENT);
            this.search_right.placeholder = this.searchPlaceholder;
            this.search_right.setAttribute('aria-label', this.searchPlaceholder + (this.selectedTitle ? ': ' + this.selectedTitle : ''));
            this.search_right.setAttribute('aria-controls', this.select.id + '-selected-list');
        }

        /**
         * @Private
         * Deselects the option with the matching value
         *
         * @param {Object} value
         */
        _deselectOption(value) {
            let options = this.select.options;

            for (let i = 0; i < options.length; i++) {
                let option = options[i];
                if (option.value === value) {
                    option.selected = false;
                    option.removeAttribute('selected');
                }
            }

            if (this.removeEvent) {
                this.removeEvent(value);
            }
        }

        /**
         * @Private
         * Set the option variables to this.
         */
        _initOptions(options) {
            for (let key in options) {
                if (options.hasOwnProperty(key)) {
                    this[key] = options[key];
                }
            }
        }

        /**
         * @Private
         * Creates all the static elements for the Dual listbox.
         */
        _initReusableElements() {
            this.dualListbox = document.createElement('div');
            this.dualListbox.classList.add(MAIN_BLOCK);
            if (this.select.id) {
                this.dualListbox.classList.add(this.select.id);
            }

            this.dualListBoxContainer = document.createElement('div');
            this.dualListBoxContainer.classList.add(CONTAINER_ELEMENT);

            this.availableListTitle = document.createElement('div');
            this.availableListTitle.id = this.select.id + '-available-title';
            this.availableListTitle.classList.add(TITLE_ELEMENT);
            this.availableListTitle.innerText = this.availableTitle;

            this.availableList = document.createElement('ul');
            this.availableList.classList.add(AVAILABLE_ELEMENT);
            this.availableList.id = this.select.id + '-available-list';
            this.availableList.setAttribute('role', 'listbox');
            this.availableList.setAttribute('tabindex', '0');
            this.availableList.setAttribute('aria-multiselectable', 'false');
            this.availableList.setAttribute('aria-labelledby', this.availableListTitle.id);

            this.selectedListTitle = document.createElement('div');
            this.selectedListTitle.id = this.select.id + '-selected-title';
            this.selectedListTitle.classList.add(TITLE_ELEMENT);
            this.selectedListTitle.innerText = this.selectedTitle;

            this.selectedList = document.createElement('ul');
            this.selectedList.classList.add(SELECTED_ELEMENT);
            this.selectedList.id = this.select.id + '-selected-list';
            this.selectedList.setAttribute('role', 'listbox');
            this.selectedList.setAttribute('tabindex', '0');
            this.selectedList.setAttribute('aria-multiselectable', 'false');
            this.selectedList.setAttribute('aria-labelledby', this.selectedListTitle.id);

            this._createButtons();
            this._createSearchLeft();
            this._createSearchRight();
        }

        /**
         * @Private
         * Selects the option with the matching value
         *
         * @param {Object} value
         */
        _selectOption(value) {
            let options = this.select.options;

            for (let i = 0; i < options.length; i++) {
                let option = options[i];
                if (option.value === value) {
                    option.selected = true;
                    option.setAttribute('selected', '');
                }
            }

            if (this.addEvent) {
                this.addEvent(value);
            }
        }

        /**
         * @Private
         * Splits the options and places them in the correct list.
         */
        _splitOptions(options) {
            for (let i = 0; i < options.length; i++) {
                let option = options[i];
                if (DualListbox.isDomElement(option)) {
                    this._addOption({
                        text: option.innerHTML,
                        value: option.value,
                        selected: option.attributes.selected
                    });
                } else {
                    this._addOption(option);
                }
            }
        }

        /**
         * @Private
         * Adds option to the selected of available list (depending on the data).
         */
        _addOption(option) {
            let listItem = this._createListItem(option);

            if (option.selected) {
                this.selected.push(listItem);
            } else {
                this.available.push(listItem);
            }
        }

        /**
         * @Private
         * Returns true if argument is a DOM element
         */
        static isDomElement(o) {
            return (
                typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
                    o && typeof o === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName === "string"
            );
        }
    }

    // export default DualListbox;

    // export { DualListbox };

