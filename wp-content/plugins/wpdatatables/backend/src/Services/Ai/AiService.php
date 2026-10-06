<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Ai;

use Throwable;
use RuntimeException;
use WPDataTables\Common\Exceptions\ServiceUnavailableException;
use WordPress\AiClient\AiClient;
use WordPress\AiClient\Providers\Models\DTO\ModelRequirements;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;

/**
 * Thin boundary over the WP 7.0 AI client (`wp-includes/ai-client.php`).
 *
 * Every call into the WordPress AI surface goes through here so the rest of the
 * plugin never touches `wp_ai_client_prompt()` / `WP_AI_Client_Prompt_Builder`
 * directly. The WP 7.0 fluent builder returns `string|WP_Error` from its
 * generating methods (it never throws); this service maps those `WP_Error`s to
 * typed exceptions the REST `Controller` base already knows how to turn into
 * HTTP status codes (503 when no provider is configured / AI is disabled).
 *
 * @package WPDataTables\Services\Ai
 */
class AiService
{
    /** WordPress.org AI plugin bootstrap file. */
    const AI_PLUGIN_FILE = 'ai/ai.php';

    /** Transient key for the cached text-model catalogue. */
    const MODELS_TRANSIENT = 'wpdatatables_ai_text_models';

    /** How long to cache the model catalogue (12 hours). */
    const MODELS_TTL = 43200;

    /**
     * Per-request memo for {@see listTextModels()} so the catalogue is resolved
     * at most once per request even though several callers ask for it.
     *
     * @var array<int, array{id: string, name: string, provider: string}>|null
     */
    private $modelsCache = null;

    /**
     * Whether AI text generation is actually usable right now.
     *
     * Two gates: the environment gate (`wp_supports_ai()`, filterable) AND a
     * "is a provider with a text-generation model configured" check. The latter
     * is the real equivalent of the announced `WP_AI_Client::is_available()` —
     * `wp_supports_ai()` alone returns true even with no provider configured.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (!function_exists('wp_supports_ai') || !function_exists('wp_ai_client_prompt')) {
            return false;
        }

        if (!wp_supports_ai()) {
            return false;
        }

        try {
            return (bool) wp_ai_client_prompt('ping')->is_supported_for_text_generation();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Whether the WP AI client API exists in this WordPress build.
     *
     * @return bool
     */
    public function isClientPresent(): bool
    {
        return function_exists('wp_supports_ai') && function_exists('wp_ai_client_prompt');
    }

    /**
     * Machine-readable reason when {@see isAvailable()} is false.
     *
     * @return string One of unsupported|disabled|unconfigured|''.
     */
    public function getUnavailableReasonCode(): string
    {
        if ($this->isAvailable()) {
            return '';
        }

        if (!$this->isClientPresent()) {
            return 'unsupported';
        }

        if (!wp_supports_ai()) {
            return 'disabled';
        }

        return 'unconfigured';
    }

    /**
     * Admin-facing explanation for why AI table generation is unavailable.
     *
     * @return string
     */
    public function getUnavailableMessage(): string
    {
        switch ($this->getUnavailableReasonCode()) {
            case 'unsupported':
                return __(
                    'WordPress 7.0 or newer is required for AI table generation. Update WordPress, then install an AI connector and configure it under Settings → Connectors.',
                    'wpdatatables'
                );
            case 'disabled':
                if ($this->isAiPluginActive()) {
                    return __(
                        'WordPress AI is disabled on this site. Enable it under Settings → AI to generate tables with AI.',
                        'wpdatatables'
                    );
                }

                return __(
                    'WordPress AI is disabled on this site. Install the WordPress AI plugin and configure a connector to use this feature.',
                    'wpdatatables'
                );
            default:
                return __(
                    'Install an AI connector plugin and add your provider credentials under Settings → Connectors to describe a table in plain English and let AI suggest columns or SQL.',
                    'wpdatatables'
                );
        }
    }

    /**
     * Whether the WordPress.org AI plugin is active.
     *
     * @return bool
     */
    public function isAiPluginActive(): bool
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active(self::AI_PLUGIN_FILE);
    }

    /**
     * Whether the WordPress.org AI plugin files are present.
     *
     * @return bool
     */
    public function isAiPluginInstalled(): bool
    {
        return file_exists(WP_PLUGIN_DIR . '/' . self::AI_PLUGIN_FILE);
    }

    /**
     * Admin URL for the core Connectors screen (provider credentials).
     *
     * @return string
     */
    public function getConnectorsUrl(): string
    {
        return admin_url('options-connectors.php');
    }

    /**
     * Admin URL for the WordPress.org AI plugin settings screen.
     *
     * @return string
     */
    public function getAiPluginSettingsUrl(): string
    {
        return admin_url('options-general.php?page=ai-wp-admin');
    }

    /**
     * Admin URL to install the WordPress.org AI plugin.
     *
     * @return string
     */
    public function getInstallAiPluginUrl(): string
    {
        return admin_url('plugin-install.php?s=ai&tab=search&type=term');
    }

    /**
     * Context-aware admin URL for resolving AI unavailability.
     *
     * @return string
     */
    public function getSettingsUrl(): string
    {
        $reason = $this->getUnavailableReasonCode();

        if ($reason === 'unsupported') {
            $url = admin_url('update-core.php');
        } elseif ($reason === 'disabled') {
            $url = $this->isAiPluginActive()
                ? $this->getAiPluginSettingsUrl()
                : $this->getInstallAiPluginUrl();
        } elseif ($this->isClientPresent()) {
            $url = $this->getConnectorsUrl();
        } else {
            $url = $this->getInstallAiPluginUrl();
        }

        /**
         * Filters the admin URL shown in wpDataTables AI setup UI.
         *
         * @since 7.x
         *
         * @param string $url    Resolved URL for the current unavailability reason.
         * @param string $reason One of unsupported|disabled|unconfigured|''.
         */
        return (string) apply_filters('wpdatatables/ai/settings_url', $url, $reason);
    }

    /**
     * Context-aware CTA label for {@see getSettingsUrl()}.
     *
     * @return string
     */
    public function getSettingsActionLabel(): string
    {
        switch ($this->getUnavailableReasonCode()) {
            case 'unsupported':
                return __('Update WordPress', 'wpdatatables');
            case 'disabled':
                return $this->isAiPluginActive()
                    ? __('Open AI settings', 'wpdatatables')
                    : __('Install WordPress AI plugin', 'wpdatatables');
            default:
                return $this->isClientPresent()
                    ? __('Configure Connectors', 'wpdatatables')
                    : __('Install WordPress AI plugin', 'wpdatatables');
        }
    }

    /**
     * Run a single-shot prompt and decode the model's JSON reply to an array.
     *
     * @param string $systemInstruction The role/format instruction. NEVER mix
     *                                   user-supplied data into this string.
     * @param string $userPrompt        The user-facing prompt (user input must be
     *                                   embedded as a quoted data value here).
     * @param int    $maxTokens         Per-endpoint output cap.
     * @param string $model             Optional model ID to prefer (e.g. the
     *                                   user's pick in the UI). Empty = use the
     *                                   default {@see modelPreference()} order.
     * @return array<string, mixed> The decoded JSON object.
     *
     * @throws ServiceUnavailableException When AI is off / unconfigured (503).
     * @throws RuntimeException            On any other generation/parse failure (500).
     */
    public function generateJson(string $systemInstruction, string $userPrompt, int $maxTokens, string $model = ''): array
    {
        if (!$this->isAvailable()) {
            throw new ServiceUnavailableException(
                'No AI provider is configured. Configure one under Settings → Connectors to use this feature.'
            );
        }

        $result = wp_ai_client_prompt($userPrompt)
            ->using_system_instruction($systemInstruction)
            ->using_max_tokens($maxTokens)
            ->using_model_preference(...$this->modelPreference($model))
            ->as_json_response()
            ->generate_text();

        if (is_wp_error($result)) {
            $status = (int) ($result->get_error_data()['status'] ?? 0);
            if ($status === 503) {
                throw new ServiceUnavailableException($result->get_error_message());
            }
            throw new RuntimeException('AI request failed: ' . $result->get_error_message());
        }

        $decoded = self::decodeJson((string) $result);

        if (!is_array($decoded)) {
            throw new RuntimeException('AI returned a malformed (non-JSON) response.');
        }

        return $decoded;
    }

    /**
     * Text-generation models the site's configured providers actually expose.
     *
     * Powers the model picker in the UI: only models a configured provider can
     * serve are listed, so the user never sees an option that 404s the way the
     * inherited environment default (e.g. Fable 5) does. Returns `[]` when AI is
     * unavailable or no provider exposes a text model.
     *
     * Resolving the catalogue hits the provider's ListModels API (the WP AI
     * client only memoises that per-request), so the result is cached in a
     * transient for {@see MODELS_TTL}. A failed/empty fetch is never cached, so
     * a transient API blip just means we retry on the next request rather than
     * showing an empty picker for 12 hours.
     *
     * @return array<int, array{id: string, name: string, provider: string}>
     */
    public function listTextModels(): array
    {
        if ($this->modelsCache !== null) {
            return $this->modelsCache;
        }

        $cached = get_transient(self::MODELS_TRANSIENT);
        if (is_array($cached) && $cached !== []) {
            $this->modelsCache = $cached;
            return $cached;
        }

        $models = $this->fetchTextModels();

        if ($models !== []) {
            set_transient(self::MODELS_TRANSIENT, $models, self::MODELS_TTL);
            $this->modelsCache = $models;
        }

        return $models;
    }

    /**
     * Resolve the text-model catalogue from the WP AI client (uncached).
     *
     * @return array<int, array{id: string, name: string, provider: string}>
     */
    private function fetchTextModels(): array
    {
        if (!$this->isAvailable() || !class_exists(AiClient::class)) {
            return [];
        }

        try {
            $requirements = new ModelRequirements([CapabilityEnum::textGeneration()], []);
            $providerModels = AiClient::defaultRegistry()->findModelsMetadataForSupport($requirements);
        } catch (Throwable $e) {
            return [];
        }

        $models = [];
        foreach ($providerModels as $providerModel) {
            $providerName = $providerModel->getProvider()->getName();
            foreach ($providerModel->getModels() as $model) {
                $models[] = [
                    'id'       => $model->getId(),
                    'name'     => $model->getName(),
                    'provider' => $providerName,
                ];
            }
        }

        return $models;
    }

    /**
     * The model that should be pre-selected in the UI.
     *
     * The first {@see modelPreference()} default that the configured providers
     * actually expose. This stops the picker from defaulting to whatever model
     * happens to sort first in the provider catalogue (e.g. Fable 5, which this
     * account is not entitled to). Empty when no default is available.
     *
     * @return string Model ID, or '' to let the server decide.
     */
    public function defaultModel(): string
    {
        $available = array_column($this->listTextModels(), 'id');
        if ($available === []) {
            return '';
        }

        foreach ($this->modelPreference() as $candidate) {
            if (in_array($candidate, $available, true)) {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * Preferred text-generation models, in priority order.
     *
     * Pinning this stops the WP AI client from inheriting its environment
     * default model (which may be a model this account is not entitled to, e.g.
     * the `Fable 5 is not available, use Opus 4.8` 404). A user-supplied pick is
     * tried first; the defaults follow as fallbacks so a transient outage on the
     * chosen model still resolves.
     *
     * @param string $preferred Optional model ID to try before the defaults.
     * @return array<int, string> Model IDs in preference order.
     */
    private function modelPreference(string $preferred = ''): array
    {
        $default = ['claude-opus-4-8', 'claude-sonnet-4-6'];

        /**
         * Filters the default preferred AI model IDs for wpDataTables generation.
         *
         * @since 7.x
         * @param array<int, string> $models Model IDs in priority order.
         */
        $default = (array) apply_filters('wpdatatables/ai/model_preference', $default);

        if ($preferred !== '') {
            array_unshift($default, $preferred);
        }

        return array_values(array_unique($default));
    }

    /**
     * Decode a model reply that should be JSON, tolerating Markdown code fences
     * some providers still wrap around structured output.
     *
     * @param string $raw
     * @return mixed Decoded value, or null on failure.
     */
    private static function decodeJson(string $raw)
    {
        $text = trim($raw);

        // Strip a leading/trailing ```json ... ``` fence if present.
        if (strpos($text, '```') === 0) {
            $text = preg_replace('/^```[a-zA-Z]*\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
            $text = trim((string) $text);
        }

        return json_decode($text, true);
    }
}
