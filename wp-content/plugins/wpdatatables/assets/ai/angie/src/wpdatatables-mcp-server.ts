import { AngieMcpSdk } from '@elementor/angie-sdk'
import { McpServer } from '@modelcontextprotocol/sdk/server/mcp'
import { Client } from '@modelcontextprotocol/sdk/client'
import { StreamableHTTPClientTransport } from '@modelcontextprotocol/sdk/client/streamableHttp'
import { CallToolRequestSchema, ListToolsRequestSchema } from '@modelcontextprotocol/sdk/types'

declare const window: Window & {
  wpWdtApiSettings?: {
    root?: string
    nonce?: string
  }
  /** Core REST base from `wp-api-request` (admin); includes trailing `wp-json/`. */
  wpApiSettings?: { root?: string; nonce?: string }
}

/**
 * Sent to the model in the MCP `initialize` result, so it applies to every wpDataTables
 * tool call regardless of which server description the chat surfaces.
 */
const SERVER_INSTRUCTIONS = [
  'This server exposes the wpDataTables WordPress plugin (tables, charts, media, SQL constructor, plugin settings).',
  '',
  'Tool names mirror the wpDataTables abilities with hyphens, e.g. `wpdatatables-list-tables`,',
  '`wpdatatables-get-table-info`, `wpdatatables-create-simple-table`, `wpdatatables-list-charts`,',
  '`wpdatatables-open-table-editor`. Angie prefixes them with the server name (`wpDataTables__<tool>`);',
  'call whatever names appear in tools/list and never claim the tools are unavailable without calling tools/list first.',
  '',
  'Answer wpDataTables questions with these tools instead of guessing: start from `wpdatatables-list-tables` or',
  '`wpdatatables-list-charts` for discovery, then `wpdatatables-get-table-info` / `wpdatatables-get-chart-info` for details.',
  'For SQL-backed tables use `wpdatatables-list-db-tables` and `wpdatatables-describe-db-table` before',
  '`wpdatatables-create-table-from-query`. Use `wpdatatables-open-table-editor` / `wpdatatables-open-chart-wizard` for navigation.',
  '',
  'FORMATTING: whenever you list or mention a table or chart, render it as a markdown link built from the',
  '`adminLink` field in tool output, e.g. [Table title](adminLink).',
].join('\n')

function ensureTrailingSlash(url: string): string {
  return url.endsWith('/') ? url : `${url}/`
}

function getRestRootUrl(): string {
  const restRoot = window.wpWdtApiSettings?.root || window.wpApiSettings?.root || '/wp-json/'

  if (/^https?:\/\//.test(restRoot)) {
    return ensureTrailingSlash(restRoot)
  }

  return ensureTrailingSlash(new URL(restRoot, window.location.origin).toString())
}

/**
 * The mcp-adapter stores every session for a user under one `mcp_adapter_sessions`
 * user meta key, read-modify-written as a whole array. When several MCP plugins hand
 * shake at once (each ships its own adapter copy but shares that key) the last write
 * drops the other sessions, and the losing client gets -32005 / HTTP 404 on its next
 * request. Reconnecting establishes a fresh session outside the contended window.
 */
function isLostSessionError(error: unknown): boolean {
  const message = error instanceof Error ? error.message : String(error)

  return message.includes('-32005') || message.includes('Session not found') || message.includes('404')
}

async function connectPhpClient(mcpUrl: URL): Promise<Client> {
  const phpClient = new Client({ name: 'wpdatatables-angie-proxy', version: '1.0.0' })
  await phpClient.connect(
    new StreamableHTTPClientTransport(new URL(mcpUrl), {
      requestInit: { credentials: 'include' },
    }),
  )

  return phpClient
}

async function delay(ms: number): Promise<void> {
  await new Promise((resolve) => setTimeout(resolve, ms))
}

async function connectWithRetry(mcpUrl: URL, attempts = 4): Promise<Client> {
  for (let attempt = 1; ; attempt++) {
    try {
      return await connectPhpClient(mcpUrl)
    } catch (error) {
      if (attempt >= attempts || !isLostSessionError(error)) throw error
      await delay(attempt * 300 + Math.floor(Math.random() * 250))
    }
  }
}

async function connectAndListTools(mcpUrl: URL, attempts = 4) {
  for (let attempt = 1; ; attempt++) {
    try {
      const phpClient = await connectPhpClient(mcpUrl)
      const tools = (await phpClient.listTools()).tools.map(({ outputSchema: _, ...tool }) => tool)

      return { phpClient, tools }
    } catch (error) {
      if (attempt >= attempts || !isLostSessionError(error)) throw error
      await delay(attempt * 300 + Math.floor(Math.random() * 250))
    }
  }
}

async function createWpDataTablesMcpServer() {
  const nonce = window.wpWdtApiSettings?.nonce || window.wpApiSettings?.nonce
  const mcpUrl = new URL('mcp/wpdatatables-mcp-server', getRestRootUrl())
  if (nonce) mcpUrl.searchParams.set('_wpnonce', nonce)

  // Retry initialize AND tools/list together. A concurrent handshake can create
  // our session and then evict it before listTools runs.
  let { phpClient, tools } = await connectAndListTools(mcpUrl)

  const server = new McpServer(
    { name: 'wpdatatables-mcp-service', version: '1.0.0' },
    { capabilities: { tools: {} }, instructions: SERVER_INSTRUCTIONS },
  )

  server.server.setRequestHandler(ListToolsRequestSchema, async () => ({ tools }))
  server.server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const params = {
      name: request.params.name,
      arguments: request.params.arguments ?? {},
    }

    try {
      return await phpClient.callTool(params)
    } catch (error) {
      // A later handshake by another MCP plugin can evict our session mid-chat.
      if (!isLostSessionError(error)) throw error

      phpClient = await connectWithRetry(mcpUrl)

      return await phpClient.callTool(params)
    }
  })

  return server
}

const init = async () => {
  try {
    const server = await createWpDataTablesMcpServer()
    const sdk = new AngieMcpSdk()

    // Do not call sdk.waitForReady(). That method waits for this bundle's ge.iframe,
    // which is only set when THIS SDK copy boots the sidebar. Angie already booted
    // the sidebar in its own bundle, so waitForReady hangs forever and tools never
    // register. registerLocalServer() already queues until angieDetector pings succeed.
    await sdk.registerLocalServer({
      name: 'wpDataTables',
      title: 'wpDataTables',
      version: '1.0.0',
      description:
        'wpDataTables – Tables and Charts for WordPress. You can create, list, inspect, edit, and open tables and charts with these tools ' +
        '(wpdatatables-create-simple-table, wpdatatables-create-table-from-source, wpdatatables-create-table-from-query, wpdatatables-list-tables, wpdatatables-list-charts). ' +
        'Never say you cannot create a wpDataTable without calling tools/list first. ' +
        'Whenever you list or mention tables or charts, render each as a clickable markdown link using adminLink from tool outputs (e.g. [Table title](adminLink)).',
      capabilities: { tools: {} },
      server,
    })
  } catch (error) {
    console.error('❌ [wpDataTables MCP] Registration failed:', error)
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init)
} else {
  void init()
}
