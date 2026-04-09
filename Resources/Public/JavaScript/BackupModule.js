/**
 * JavaScript for the backup module
 */
import $ from 'jquery';
import Notification from '@typo3/backend/notification.js';

/**
 * Backup Module
 */
const BackupModule = {
    /**
     * Initialization
     */
    initialize: function() {
        // Check for backup status containers
        const statusContainers = document.querySelectorAll('.backup-status-container');
        if (statusContainers && statusContainers.length > 0) {
            statusContainers.forEach(container => {
                // Extract backup ID from container ID
                const backupId = container.id.replace('backup-status-container-', '');
                if (backupId) {
                    this.initStatusCheck(backupId, container);
                }
            });

            this.initCancelButtons();
        }
    },

    /**
     * Formats file size for display
     */
    formatFileSize: function(bytes) {
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        bytes = Math.max(bytes, 0);
        let pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
        pow = Math.min(pow, units.length - 1);
        bytes /= Math.pow(1024, pow);

        return Math.round(bytes * 100) / 100 + ' ' + units[pow];
    },

    /**
     * Initialize the cancel buttons
     */
    initCancelButtons: function() {
        const cancelButtons = document.querySelectorAll('[data-action="cancelBackup"]');
        if (cancelButtons) {
            cancelButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Don't remove this - it's needed to allow the button to work properly
                    // as both a link and also have a JavaScript event handler
                    e.preventDefault();

                    const backupId = this.getAttribute('data-backup-id');

                    // Show confirmation dialog
                    if (confirm('Are you sure you want to cancel the backup process?')) {
                        // Follow the link's href if confirmed
                        window.location.href = button.getAttribute('href');
                    }
                });
            });
        }
    },

    /**
     * Start the status check interval for a specific backup
     */
    initStatusCheck: function(backupId, container) {
        if (!backupId || !container) return;

        const progressBar = container.querySelector('.progress-bar');
        const statusBadge = container.querySelector('.backup-status-badge');
        const errorMessage = container.querySelector('.backup-error-message');
        const downloadButton = container.querySelector('.backup-download-button');
        const cancelButton = container.querySelector('[data-action="cancelBackup"]');
        const self = this;

        // Only check status for backups that are running
        const currentStatus = statusBadge.textContent.trim().toLowerCase();
        if (currentStatus.indexOf('completed') !== -1 ||
            currentStatus.indexOf('error') !== -1 ||
            currentStatus.indexOf('canceled') !== -1 ||
            currentStatus.indexOf('downloaded') !== -1) {
            return;
        }

        // Check every 3 seconds
        const checkInterval = setInterval(function() {
            // Use jQuery AJAX for better compatibility with TYPO3 backend
            $.ajax({
                url: TYPO3.settings.ajaxUrls['openoap_backup_status_check'],
                type: 'POST',
                data: {
                    backupId: backupId
                },
                dataType: 'json',
                success: function(data) {
                    // Update progress bar
                    if (progressBar) {
                        // Update progress bar width with inline style
                        progressBar.style.width = data.progress + '%';
                        progressBar.setAttribute('aria-valuenow', data.progress);
                        progressBar.querySelector('span').textContent = data.progress + '%';
                    }

                    // Update status badge
                    if (statusBadge) {
                        let badgeHtml = '';
                        if (data.status === 'running') {
                            badgeHtml = '<span class="badge badge-info">In Progress</span>';
                        } else if (data.status === 'completed') {
                            badgeHtml = '<span class="badge badge-success">Completed</span>';
                            Notification.success('Backup Completed', 'The backup has been successfully created and is ready for download.');
                        } else if (data.status === 'error') {
                            badgeHtml = '<span class="badge badge-danger">Error</span>';
                            Notification.error('Backup Failed', 'An error occurred while creating the backup.');
                        } else if (data.status === 'canceled') {
                            badgeHtml = '<span class="badge badge-warning">Canceled</span>';
                            Notification.info('Backup Canceled', 'The backup process has been canceled.');
                            clearInterval(checkInterval);
                        } else if (data.status === 'downloaded') {
                            badgeHtml = '<span class="badge badge-info">Downloaded & Deleted</span>';
                            Notification.info('Backup Downloaded', 'The backup has been downloaded and the file has been deleted.');
                            clearInterval(checkInterval);
                        }
                        statusBadge.innerHTML = badgeHtml;
                    }

                    // Update archive size if available
                    const archiveSizeElement = container.querySelector('.backup-archive-size');
                    if (archiveSizeElement && data.archiveSizeFormatted) {
                        archiveSizeElement.textContent = data.archiveSizeFormatted;
                    } else if (archiveSizeElement && data.archiveSize) {
                        // Format size manually if formatted size is not available
                        archiveSizeElement.textContent = self.formatFileSize(data.archiveSize);
                    }

                    // Show error message if any
                    if (errorMessage && data.status === 'error' && data.error) {
                        errorMessage.innerHTML = '<div class="alert alert-danger"><p>' + data.error + '</p></div>';
                    }

                    // Show download button when completed
                    if (data.status === 'completed') {
                        // Reload page to show download button if it completed
                        if (!document.querySelector('a[href*="download"][href*="' + backupId + '"]')) {
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        }
                    }

                    // Hide cancel button when not running
                    if (cancelButton && data.status !== 'running') {
                        cancelButton.style.display = 'none';
                    }

                    // Stop checking if backup is completed or has error
                    if (data.status === 'completed' || data.status === 'error' || data.status === 'canceled' || data.status === 'downloaded') {
                        clearInterval(checkInterval);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching backup status for ' + backupId + ':', errorThrown);
                }
            });
        }, 3000);
    }
};

// Initialize when the DOM is loaded - execute immediately!
BackupModule.initialize();

// Return the module
export default BackupModule;
