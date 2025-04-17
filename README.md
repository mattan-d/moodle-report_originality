# Moodle Originality Report Plugin

## Overview

The Originality Report plugin for Moodle provides administrators and teachers with comprehensive analytics and reporting tools to monitor originality scores across courses, assignments, and students. This plugin integrates with Moodle's plagiarism detection system to generate insightful reports that help identify patterns and areas requiring attention.

## Features

- **Multiple Report Types**:
  - Teachers who haven't opened reports
  - Courses with lowest average originality scores
  - Students with lowest average originality scores
  - Number of assignments submitted for checking
  - Average originality score by course and module

- **Flexible Filtering**:
  - Filter by grade range
  - Filter by date range
  - Reset filters with a single click

- **Export Capabilities**:
  - Export any report to PDF with custom logo and footer
  - Export any report to CSV
  - Full support for RTL languages (including Hebrew)
  - Filenames include date and time for easy organization

- **Customization Options**:
  - Upload a custom logo for PDF reports
  - Set custom footer text for PDF reports

- **Responsive Design**:
  - Works on desktop and mobile devices
  - Accessible interface

## Requirements

- Moodle 4.0 or higher
- PHP 7.4 or higher
- A plagiarism detection plugin that uses the `plagiarism_originality_sub` and `plagiarism_originality_stats` tables

## Installation

1. Download the plugin from the Moodle plugins directory or from the GitHub repository.
2. Extract the contents to the `report/originality` directory in your Moodle installation.
3. Log in as an administrator and visit the notifications page to complete the installation.
4. Alternatively, you can install the plugin via the Moodle plugin installer in the Site Administration.

## Configuration

1. Navigate to Site Administration > Reports > Originality Reports Settings.
2. Upload a logo to be displayed in the header of PDF reports (optional).
3. Set custom footer text for PDF reports (optional).

## Usage

### Accessing the Reports

1. Log in as an administrator or a user with the appropriate capabilities.
2. Navigate to Site Administration > Reports > Originality Reports.
3. Alternatively, the report can be accessed from the course administration menu if you have the appropriate permissions.

### Using the Filters

1. Select the report type you want to view.
2. Set the grade range (0-100) to filter submissions by their originality score.
3. Set the date range to filter submissions by when they were created.
4. Click "Apply filters" to update the report.
5. Click "Reset filters" to return to the default filter settings.

### Exporting Reports

1. Apply the desired filters to the report.
2. Click the "Export to PDF" button to download a PDF version of the report.
3. Click the "Export to CSV" button to download a CSV version of the report.
4. The exported files will include the current date and time in the filename.

## Permissions

The plugin uses the following capability:

- `report/originality:view`: Allows users to view the originality reports.

By default, this capability is granted to managers, course creators, and editing teachers.

## Support

For support, bug reports, or feature requests, please use the GitHub issue tracker or contact the plugin maintainer.

## License

This plugin is licensed under the GNU GPL v3 or later. See the LICENSE file for details.

## Credits

Developed by Mattan Dor (CentricApp) © 2025.

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue on the GitHub repository.
