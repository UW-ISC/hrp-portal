<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Activation;

use WPDataTables\Services\Tools\ToolsService;

/**
 * ActivationController — admin-ajax handlers for plugin (and add-on) licence
 * activation / deactivation against the Melograno Store API.
 *
 * The nonce/cap checks and the wire format ($_POST in, `echo`/`exit` out) keep
 * the existing admin JS unaffected. The bodies talk to the Melograno Store over
 * curl and persist licence state via `update_option` /
 * `ToolsService::deactivatePlugin`; the `WDT_STORE_API_URL` constant resolves via
 * the global-constant fallback. The legacy global functions remain one-line
 * delegators.
 *
 * Plain class by design (NOT extending the REST-shaped
 * {@see \WPDataTables\Controllers\Controller}).
 *
 * @package WPDataTables\Controllers\Activation
 */
class ActivationController
{
    /**
     * Validate a purchase code and activate the plugin / add-on.
     *
     * @return void
     */
    public function activatePlugin()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }

        /** @var string $slug */
        $slug = sanitize_text_field($_POST['slug']);

        /** @var string $purchaseCode */
        $purchaseCode = sanitize_text_field($_POST['purchaseCodeStore']);

        /** @var string $domain */
        $domain = sanitize_text_field($_POST['domain']);
        $domain = ToolsService::getDomain($domain);

        /** @var string $subdomain */
        $subdomain = sanitize_text_field($_POST['subdomain']);
        $subdomain = ToolsService::getSubDomain($subdomain);

        $ch = curl_init(
            WDT_STORE_API_URL . 'activation/code?slug=' . $slug . '&purchaseCode=' . $purchaseCode .
            '&domain=' . $domain . '&subdomain=' . $subdomain
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, apply_filters('wpdatatables_curlopt_ssl_verifypeer', 1));

        // Response from the Melograno Store
        $response = json_decode(curl_exec($ch));

        curl_close($ch);

        if ($response->valid && $response->domainRegistered) {
            if ($slug === 'wpdatatables') {
                update_option('wdtPurchaseCodeStore', $purchaseCode);
                update_option('wdtActivated', true);
            } else if ($slug === 'wdt-powerful-filters') {
                update_option('wdtPurchaseCodeStorePowerful', $purchaseCode);
                update_option('wdtActivatedPowerful', true);
            } else if ($slug === 'reportbuilder') {
                update_option('wdtPurchaseCodeStoreReport', $purchaseCode);
                update_option('wdtActivatedReport', true);
            } else if ($slug === 'wdt-gravity-integration') {
                update_option('wdtPurchaseCodeStoreGravity', $purchaseCode);
                update_option('wdtActivatedGravity', true);
            } else if ($slug === 'wdt-formidable-integration') {
                update_option('wdtPurchaseCodeStoreFormidable', $purchaseCode);
                update_option('wdtActivatedFormidable', true);
            } else if ($slug === 'wdt-master-detail') {
                update_option('wdtPurchaseCodeStoreMasterDetail', $purchaseCode);
                update_option('wdtActivatedMasterDetail', true);
            }
        }

        $result = [
            'valid' => $response->valid,
            'domainRegistered' => $response->domainRegistered
        ];

        echo json_encode($result);
        exit();
    }

    /**
     * Deactivate the plugin / add-on licence.
     *
     * @return void
     */
    public function deactivatePlugin()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtSettingsNonce')) {
            exit();
        }

        /** @var string $slug */
        $slug = sanitize_text_field($_POST['slug']);

        switch ($slug) {
            case 'wpdatatables':
                $purchaseCode = get_option('wdtPurchaseCodeStore');
                break;
            case 'wdt-master-detail':
                $purchaseCode = get_option('wdtPurchaseCodeStoreMasterDetail');
                break;
            case 'wdt-powerful-filters':
                $purchaseCode = get_option('wdtPurchaseCodeStorePowerful');
                break;
            case 'wdt-gravity-integration':
                $purchaseCode = get_option('wdtPurchaseCodeStoreGravity');
                break;
            case 'wdt-formidable-integration':
                $purchaseCode = get_option('wdtPurchaseCodeStoreFormidable');
                break;
            case 'reportbuilder':
                $purchaseCode = get_option('wdtPurchaseCodeStoreReport');
                break;
            default:
                $purchaseCode = '';
                break;
        }

        /** @var string $envatoTokenEmail */
        $envatoTokenEmail = sanitize_text_field($_POST['envatoTokenEmail']);

        /** @var string $domain */
        $domain = sanitize_text_field($_POST['domain']);
        $domain = ToolsService::getDomain($domain);

        /** @var string $subdomain */
        $subdomain = sanitize_text_field($_POST['subdomain']);
        $subdomain = ToolsService::getSubDomain($subdomain);

        /** @var string $type */
        $type = sanitize_text_field($_POST['type']);

        if ($type === 'code') {
            $ch = curl_init(
                WDT_STORE_API_URL . 'activation/code/deactivate?slug=' . $slug . '&purchaseCode=' . $purchaseCode . '&domain=' . $domain . '&subdomain=' . $subdomain
            );
        } else {
            $ch = curl_init(
                WDT_STORE_API_URL . 'activation/envato/deactivate?slug=' . $slug . '&envatoTokenEmail=' . $envatoTokenEmail . '&domain=' . $domain . '&subdomain=' . $subdomain
            );
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Response from the Melograno Store
        $response = json_decode(curl_exec($ch));

        curl_close($ch);

        if ($response->deactivated === true || $response === null) {
            ToolsService::deactivatePlugin($slug);
        }

        $result = [
            'deactivated' => $response->deactivated,
        ];

        echo json_encode($result);
        exit();
    }
}
