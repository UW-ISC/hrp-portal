<p class="clear"></p>
<br/>
<?php if( !$showCredentialsForm ) : ?>
    <div>
        <h3>Backup Glossary Terms to File</h3>

        <?php if( $showBackupDownloadLink ) : ?>
            <a href="<?php echo esc_url($showBackupDownloadLink); ?>" class="button">Download Backup</a>
            <p>
                <strong>URL to the backup file:</strong>
                <input type="text" readonly="readonly" size="90" value="<?php echo esc_url($showBackupDownloadLink); ?>" />
            </p>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('cmtt_do_backup'); ?>
            <input type="submit" value="Backup to CSV" name="cmtt_doBackup" class="button button-primary"/>
        </form>
    </div>
<?php endif; ?>

<br/><br/>
<div>
    <h3>Export Glossary Terms</h3>
    <form method="post">
        <input type="submit" value="Export to CSV" name="cmtt_doExport" class="button button-primary"/>
    </form>

</div>
<br/><br/>

<div>
    <h3>Import Glossary Terms from File</h3>
    <p>
        If the term already exists in the database, only content is updated. Otherwise, new term is added.
    </p>

    <div>
        <strong>Important!!</strong>
        <ul style="list-style: circle; margin-left: 2em">
            <li>File should be UTF-8 encoded</li>
            <li>If you use MS Excel, please remember that by default it can't save proper CSV format (comma-delimited) - see <a href="http://support.microsoft.com/kb/291296" target="_blank" rel="nofollow">Microsoft Knowledge Base Article</a></li>
            <li>All the fields which can contain commas, must be enclosed in quotes! (to be 100% safe enclose each field in quotes)</li>
            <li>The only two mandatory fields are Title and Description</li>
            <li>Minimal row: <code>"","Title","","Description"</code></li>
        </ul>
    </div>

    <?php if( isset($_GET['msg']) && $_GET['msg'] == 'imported' ): ?>
        <div id="message" class="updated below-h2">File <?php
            if( $_GET['itemsnumber'] == 0 ) echo 'import failed';
            else 'succesfully imported';
            ?> (<?php echo esc_attr($_GET['itemsnumber']); ?> items read from file)</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="importCSV" />
        <input type="submit" value="Import from CSV" name="cmtt_doImport" class="button button-primary"/>
    </form><br />
    Format example:<br />
    <pre>
Id,Title,Excerpt,Description,Synonyms,Variations,Categories,Abbreviation,Tags,Meta
100,"Example Term","Example term excerpt text","Description, if multiline then uses&lt;br&gt;HTML element","synonym1,synonym2","variation1,variation2","categoryID1,categoryID2","abbreviationID1","tagID1,tagID2",
101,"Another",,"Excerpt can be empty",,
    </pre>
    <p>
        Download the example file prepared by CreativeMinds: <a href="<?php echo CMTT_PLUGIN_URL ?>assets/cm_tooltip_glossary_import.csv">cm_tooltip_glossary_import.csv</a>
    </p>
</div>