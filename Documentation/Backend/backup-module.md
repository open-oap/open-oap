# Backup Module

## Overview

The OpenOAP Backup Module provides a convenient way to create complete TYPO3 backups directly from the backend. It allows administrators to backup database and files, monitor the backup process, and download the completed backup archive.

By default, the backup module is **disabled** and must be explicitly enabled in the extension configuration.

## Features

- Create complete TYPO3 backups (database + files)
- Asynchronous processing to avoid timeout issues with large sites
- Progress monitoring with visual feedback
- Email notification when backup is complete
- Download completed backups directly from the backend

## Configuration

The backup module has several configuration options available in the TYPO3 backend:

1. Go to **Admin Tools** → **Settings** → **Extension Configuration**
2. Select **open_oap**
3. Navigate to the **Backup** tab

### Available Settings

| Setting | Type | Default | Description |
| ------- | ---- | ------- | ----------- |
| enableBackupModule | boolean | 0 (disabled) | Enable or disable the backup module in the backend |
| backupAccess | options | admin | Access level for the backup module: 'user' or 'admin' |
| phpBinaryPath | string | php | Path to the PHP binary to use for CLI commands |
| mysqlDumpBinaryPath | string | mysqldump | Path to the mysqldump binary |

### Enabling the Module

1. Set `enableBackupModule` to `1` (enabled)
2. Save the configuration
3. Flush the TYPO3 cache from the backend or using the CLI command `typo3 cache:flush`

After enabling the module and flushing the cache, the backup module will appear in the TYPO3 backend under the **System** section.

## Usage

1. Navigate to **System** → **Backup** in the TYPO3 backend
2. Click the **Create Backup** button to start a new backup
3. Monitor the progress on the same page
4. Once complete, use the **Download** button to retrieve the backup archive

## Technical Details

- Backups are stored in `var/backup/` directory
- The backup process runs asynchronously using the TYPO3 CLI command
- Database backups are created using the mysqldump utility
- Files are archived using PHP's native ZipArchive class
- Status information is stored in JSON files in the backup directory
- Log messages are stored in the backup log file

## Security Considerations

- Backup files contain sensitive information and should be protected
- Downloaded backup files are automatically deleted from the server after download
- The backup module requires admin privileges to access by default, but can be configured to allow regular user access via the `backupAccess` setting

## Troubleshooting

If the backup process fails or appears stuck:

1. Check the TYPO3 system log for error messages
2. Verify that the PHP and mysqldump binaries are correctly configured
3. Ensure sufficient disk space is available for the backup
4. Check permissions on the var/backup directory

For servers with multiple PHP versions installed or custom configurations, you can specify the exact path to the PHP binary using the `phpBinaryPath` setting. 