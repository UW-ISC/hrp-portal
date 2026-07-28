<?php
/**
 * MCP Prompt: wpDataTables file upload workflow
 *
 * When the agent loads this prompt, it receives instructions to use MCP tools
 * directly instead of WP-CLI or external tools for creating tables from files.
 *
 * @package wpDataTables_MCP_Server
 * @since   0.2.0
 */

namespace WDTMCP\Infrastructure\WP\MCP\Prompts;

use WP\MCP\Domain\Prompts\McpPromptBuilder;

defined( 'ABSPATH' ) or die('Access denied.');

/**
 * Prompt builder for wpDataTables file upload workflow.
 */
class FileUploadWorkflowPrompt extends McpPromptBuilder {

	/**
	 * Configure the prompt.
	 */
	protected function configure(): void {
		$this->name        = 'wpdatatables-file-upload-workflow';
		$this->title       = 'wpDataTables: Create Table from File';
		$this->description = 'Instructions for creating a wpDataTable from an Excel, CSV, or other data file. Use this when the user asks to upload a file and create a sortable table.';
	}

	/**
	 * Handle prompt execution: return messages with workflow instructions.
	 *
	 * @param array $arguments Optional arguments (unused).
	 * @return array Result with messages array per MCP spec.
	 */
	public function handle( array $arguments ): array {
		$text = <<<TEXT
When the user asks to upload a file (Excel, CSV, JSON, XML, etc.) and create a wpDataTable:

**Preferred MCP workflow for local files:**
1. Read the file from disk and call `upload-data-file` with `filename` and `content_base64`.
2. Use the returned `attachment_id` with `create-table-from-source` (live linked table) or `import-table-from-source` (one-time MySQL import, Standard tier).
3. To change an existing table after creation, use `edit-table` (works for both Simple and data-source tables; it routes automatically), or `update-table-settings` / `update-simple-table-styles` for explicit control.

**Alternatives for local files:**
- User uploads via wp-admin → Media → Add New, then use `list-media` to get `attachment_id`.
- Or POST multipart to WordPress REST API `{site}/wp-json/wp/v2/media` with application password auth.

**For public remote URLs:** call `create-table-from-source` or `import-table-from-source` with `source_url` and `source_type`. CSV and Excel remote URLs are auto-downloaded to the Media Library.

**Google Sheets:** use `source_type: google_spreadsheet` with a public Google Sheet URL. Private sheets require Google API configuration in wpDataTables settings.

**Do not use:** local file paths as `source_url`, localhost URLs, or wp-config hacks.
TEXT;

		return array(
			'description' => 'Workflow for creating wpDataTables from files',
			'messages'   => array(
				array(
					'role'    => 'user',
					'content' => array(
						'type' => 'text',
						'text' => $text,
					),
				),
			),
		);
	}
}
