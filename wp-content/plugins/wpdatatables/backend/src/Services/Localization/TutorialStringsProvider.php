<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Localization;

/**
 * Bootstrap tour tutorial translation strings.
 *
 * @package WPDataTables\Services\Localization
 */
class TutorialStringsProvider
{
    /**
         * Helper function that returns an array of strings for tutorials
         * @return array
         */
        public static function getTutorialsTranslationStrings()
        {
            $guideTeacherIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/male-teacher.png">';
            $waveIMG = '<img class="wdt-emoji-body" src="' . WDT_ROOT_URL . 'assets/img/wave.png">';
            $partyTitleIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/party-popper.png">';
            $hourglassIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/hourglass-not-done.png">';
            $raisedHandsIMG = '<img class="wdt-emoji-title m-l-5" src="' . WDT_ROOT_URL . 'assets/img/raising-hands.png">';
            $chartIMG = '<img class="wdt-emoji-title" src="' . WDT_ROOT_URL . 'assets/img/chart-increasing.png">';
            $username = wp_get_current_user()->user_login;
    
            return array(
                'cannot_be_empty_field' => __('The field cannot be empty!', 'wpdatatables'),
                'cannot_be_empty_chart_type' => __('Please choose the chart type.', 'wpdatatables'),
                'cannot_be_empty_chart_table' => __('Please select wpDataTable from the dropdown.', 'wpdatatables'),
                'cannot_be_empty_chart_table_columns' => __('Columns field cannot be empty.', 'wpdatatables'),
                'cancel_button' => __('Cancel', 'wpdatatables'),
                'cancel_tour' => __('The tutorial is not canceled, closed, or end properly. Please cancel it by clicking on the Cancel button.', 'wpdatatables'),
                'error_data_source' => __('Please check the data source that you use for this table.', 'wpdatatables'),
                'finish_button' => __('Finish Tutorial', 'wpdatatables'),
                'next_button' => __('Continue', 'wpdatatables'),
                'start_button' => __('Start', 'wpdatatables'),
                'skip_button' => __('Skip Tutorial', 'wpdatatables'),
                'tour0' => array(
                    'step0' => array(
                        'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                        'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial, we will show you how to create a simple table from scratch by choosing a custom number of columns and rows. How to customize each cell, merge cells and a lot more.', 'wpdatatables'),
                    ),
                    'step1' => array(
                        'title' => __(' Let\'s create a new wpDataTable from scratch!', 'wpdatatables'),
                        'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                    ),
                    'step2' => array(
                        'title' => __('Choose this option', 'wpdatatables'),
                        'content' => __('Please select \'Create a simple table from scratch\'.', 'wpdatatables'),
                    ),
                    'step3' => array(
                        'title' => __('Click Next', 'wpdatatables'),
                        'content' => __('Please click the \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step4' => array(
                        'title' => __('Welcome to the Simple table wizard!', 'wpdatatables'),
                        'content' => __('Please click \'Continue\' button to move on.', 'wpdatatables'),
                    ),
                    'step5' => array(
                        'title' => __('Choose a name for your table', 'wpdatatables'),
                        'content' => __('After inserting table name, click \'Continue\' to move on.', 'wpdatatables'),
                    ),
                    'step6' => array(
                        'title' => __('Choose the number of columns for your table', 'wpdatatables'),
                        'content' => __('Please choose how many columns it will have. Remember that you can always add or reduce the number of columns later. Click \'Continue\' when you finish.', 'wpdatatables'),
                    ),
                    'step7' => array(
                        'title' => __('Choose the number of rows for your table.', 'wpdatatables'),
                        'content' => __('Please choose how many rows it will have. Remember that you can always add or reduce the number of rows later. Click \'Continue\' when you finish.', 'wpdatatables'),
                    ),
                    'step8' => array(
                        'title' => __('Click on the \'Generate Table\' button', 'wpdatatables'),
                        'content' => __('When you click on the button, the empty table will be ready for you. ', 'wpdatatables'),
                    ),
                    'step9' => array(
                        'title' => $hourglassIMG . __('We are generating the table...', 'wpdatatables'),
                        'content' => __('Please, when you see the table, click \'Continue\' to move on.', 'wpdatatables'),
                    ),
                    'step10' => array(
                        'title' => __('Nice job! You just configured your table and it is ready to fill it with data.', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('Now we will guide you on how to insert data and check table layout throw Simple table editor, table toolbar and table preview. Please click \'Continue\' to move on.', 'wpdatatables'),
                    ),
                    'step11' => array(
                        'title' => __('This is Simple table editor', 'wpdatatables'),
                        'content' => __('Here you can populate your table with data. <br><br>You can move around the cells using keyboard arrows and the Tab button. <br><br>Rearrange columns or rows by drag and drop column or row headers. Easily resize column width and row height by dragging the right corner of the column header, or the bottom line of the row header. Click \'Continue\' to move on.', 'wpdatatables'),
                    ),
                    'step12' => array(
                        'title' => __('Check out the Simple table toolbar', 'wpdatatables'),
                        'content' => __('Here you can style and insert custom data for each cell or range of cells. You can add or delete columns and rows, merge cells, customize sections by colors, background, alignment, insert custom links, media, shortcodes, star ratings or custom HTML code.', 'wpdatatables'),
                    ),
                    'step13' => array(
                        'title' => __('Responsive table views', 'wpdatatables'),
                        'content' => __('You can switch between Desktop, Tablet or Mobile devices by clicking on the tab that you need, so you can make sure your table looks excellent across all devices. ', 'wpdatatables'),
                    ),
                    'step14' => array(
                        'title' => __('Real-time preview', 'wpdatatables'),
                        'content' => __('Here you will see how your table will look like on the page. Please click \'Continue\' to move on.', 'wpdatatables'),
                    ),
                    'step15' => array(
                        'title' => $partyTitleIMG . __('Congrats! Your table is ready.', 'wpdatatables'),
                        'content' => __('Now you can copy the shortcode for this table, and check out how it looks on your website when you paste it to a post or page. You can always come back and edit the table as you like.', 'wpdatatables'),
                    )
                ),
                'tour1' => array(
                    'step0' => array(
                        'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                        'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a wpDataTable linked to an existing data source. "Linked" in this context means that if you create a table, for example, based on an Excel file, it will read the data from this file every time it loads, making sure all table values changes are instantly reflected in the table.', 'wpdatatables'),
                    ),
                    'step1' => array(
                        'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                        'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                    ),
                    'step2' => array(
                        'title' => __('Choose this option.', 'wpdatatables'),
                        'content' => __('Please select \'Create a table linked to an existing data source\'.', 'wpdatatables'),
                    ),
                    'step3' => array(
                        'title' => __('Click Next', 'wpdatatables'),
                        'content' => __('Please click the \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step4' => array(
                        'title' => __('Input data source type', 'wpdatatables'),
                        'content' => __('Please select a data source type that you need.', 'wpdatatables'),
                    ),
                    'step5' => array(
                        'title' => __('Select Data source type', 'wpdatatables'),
                        'content' => __('Please choose the data source that you need (SQL, Excel, CSV, JSON, Google Spreadsheet, or PHP array) and then click \'Continue\' button.', 'wpdatatables'),
                    ),
                    'step6' => array(
                        'title' => __('Input file path or URL', 'wpdatatables'),
                        'content' => __('Upload your file or provide the full URL here. When you finish click \'Continue\' button.', 'wpdatatables'),
                    ),
                    'step7' => array(
                        'title' => __('Write SQL query', 'wpdatatables'),
                        'content' => __('Please write your custom SQL query and then click \'Continue\' button.', 'wpdatatables'),
                    ),
                    'step8' => array(
                        'title' => __('Click Save Changes', 'wpdatatables'),
                        'content' => __('Please click on the \'Save Changes\' button to create a table.<br><br> If you get an error message after button click and you are not able to solve it, please contact us on our support platform and provide us this data source that you use for creating this table and copy error message as well and click Skip tutorial.', 'wpdatatables'),
                    ),
                    'step9' => array(
                        'title' => $hourglassIMG . __('The table is creating...', 'wpdatatables'),
                        'content' => __('Now the table is creating. Wait until you see it in the background and then click \'Continue\'.', 'wpdatatables'),
                    ),
                    'step10' => array(
                        'title' => $partyTitleIMG . __('Nice job! You just created your first wpDataTable!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('Now you can copy the shortcode for this table, and check out how it looks on your website when you paste it to a post or page.', 'wpdatatables'),
                    )
                ),
                'tour2' => array(
                    'step0' => array(
                        'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                        'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a table manually, fully in WordPress dashboard.', 'wpdatatables'),
                    ),
                    'step1' => array(
                        'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                        'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                    ),
                    'step2' => array(
                        'title' => __('Choose this option', 'wpdatatables'),
                        'content' => __('Please select \'Create a table manually\' to proceed.', 'wpdatatables'),
                    ),
                    'step3' => array(
                        'title' => __('Click NEXT', 'wpdatatables'),
                        'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step4' => array(
                        'title' => __('Welcome to manual table constructor', 'wpdatatables'),
                        'content' => __('This table constructor will help you to create a table from scratch. <br><br>  Click \'Continue\' to set section by section.', 'wpdatatables'),
                    ),
                    'step5' => array(
                        'title' => __('Choose names and number of columns.', 'wpdatatables'),
                        'content' => __('Give your table a name and choose how many columns it will have.<br><br>  Click the \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                    ),
                    'step6' => array(
                        'title' => __('Column creating wizard', 'wpdatatables'),
                        'content' => __('Here you can set the name, choose the type for each column, drag and drop to reorder them or remove a column.<br><br>  When you set everything, click the \'Continue\' button.', 'wpdatatables'),
                    ),
                    'step7' => array(
                        'title' => $partyTitleIMG . __('Congrats! You just configured your table.', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                    )
                ),
                'tour3' => array(
                    'step0' => array(
                        'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                        'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(',  this tutorial will show you how to create a table by importing data from the existing data source.', 'wpdatatables'),
                    ),
                    'step1' => array(
                        'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                        'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                    ),
                    'step2' => array(
                        'title' => __('Choose this option', 'wpdatatables'),
                        'content' => __('Please select \'Create a table by importing data from a data source\' to proceed.', 'wpdatatables'),
                    ),
                    'step3' => array(
                        'title' => __('Click Next', 'wpdatatables'),
                        'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step4' => array(
                        'title' => __('Choose your data source', 'wpdatatables'),
                        'content' => __('Upload your file or provide the full URL here. When you finish click \'Continue\' button.', 'wpdatatables'),
                    ),
                    'step5' => array(
                        'title' => __('Click \'Next\'', 'wpdatatables'),
                        'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step6' => array(
                        'title' => __('Now you can edit your table in the manual constructor', 'wpdatatables'),
                        'content' => __('This constructor will show you columns that will be saved in the database based on your file.<br><br>  Click \'Continue\' button to check section by section.', 'wpdatatables'),
                    ),
                    'step7' => array(
                        'title' => __('Choose a name for your table.', 'wpdatatables'),
                        'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                    ),
                    'step8' => array(
                        'title' => __('Welcome to Column Creating Wizard.', 'wpdatatables'),
                        'content' => __('Your data preview is here. You can change the name and type for each column, drag and drop to reorder them, remove or add a column.<br><br>  When you set everything, click \'Continue\' button.', 'wpdatatables'),
                    ),
                    'step9' => array(
                        'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                    )
                ),
                'tour4' => array(
                    'step0' => array(
                        'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                        'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial, we will show you how to create a table by generating a query from WordPress posts.', 'wpdatatables'),
                    ),
                    'step1' => array(
                        'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                        'content' => __('Click on \'Create a Table\' to access the wpDataTables Table Wizard.', 'wpdatatables'),
                    ),
                    'step2' => array(
                        'title' => __('Choose this option', 'wpdatatables'),
                        'content' => __('Please select \'Generate a query to the WordPress database\' to proceed.', 'wpdatatables'),
                    ),
                    'step3' => array(
                        'title' => __('Click Next', 'wpdatatables'),
                        'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step4' => array(
                        'title' => __('Choose a name for your table.', 'wpdatatables'),
                        'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                    ),
                    'step5' => array(
                        'title' => __('Welcome to MySQL query generator.', 'wpdatatables'),
                        'content' => __('Choose one or more post types from the lefthand side for your table, either by dragging and dropping the post type names or by selecting post types and clicking on the right arrow. Once the post types are marked as selected, \'All post properties\' section will be populated.<br><br>From this section, you need to choose the post properties, you can as well select the ones you want to show in the table either by dragging and dropping or by selecting and clicking on the right arrow. When you finish, click \'Continue\' to move forward.', 'wpdatatables'),
                    ),
                    'step6' => array(
                        'title' => __('Choose relations', 'wpdatatables'),
                        'content' => __('Define relations (joining rules) between post types.<br><br>  Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                    ),
                    'step7' => array(
                        'title' => __('Define post types relations', 'wpdatatables'),
                        'content' => __('When you define it toggle checkbox to have an inner join, uncheck to have left join. <br><br> Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                    ),
                    'step8' => array(
                        'title' => __('Set the conditions between the columns', 'wpdatatables'),
                        'content' => __('Add and define conditions between columns in your table by clicking the \'+ Add Condition\' button.<br><br>  When you are ready, click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step9' => array(
                        'title' => __('Set the grouping rules', 'wpdatatables'),
                        'content' => __('By the \'+ Add Grouping\' button, you can define the column grouping rules for the table.<br><br>  Once you\'re done, click "Continue" button to move on.', 'wpdatatables'),
                    ),
                    'step10' => array(
                        'title' => __('Click \'Next\'', 'wpdatatables'),
                        'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step11' => array(
                        'title' => $hourglassIMG . __('We are creating your query...', 'wpdatatables'),
                        'content' => __('Please wait until wpDataTables query constructor builds the query for you. Once done, it will show five lines of data as a preview. <br><br>Click \'Continue\' button when you see it in the background.', 'wpdatatables'),
                    ),
                    'step12' => array(
                        'title' => __('Your query preview is ready!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('Here you see the query that wpDataTables generated for you. If you are not completely satisfied with it, you can edit it straight from this window.<br><br> Click "Continue" button to move on.', 'wpdatatables'),
                    ),
                    'step13' => array(
                        'title' => __('Preview of the first 5 rows.', 'wpdatatables'),
                        'content' => __('Here you can see the first few rows the MySQL server returns using the query provided. If you see “No data”, it means that the MySQL server could not execute it, either because of an error in the query or because it is simply returning an empty data-set, even though the syntax is correct. <br>If this is the case, please click \'Skip Tutorial\' and try to create a new one. <br><br>If everything is fine, please click \'NEXT\' to continue.', 'wpdatatables'),
                    ),
                    'step14' => array(
                        'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                    )
                ),
                'tour5' => array(
                    'step0' => array(
                        'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                        'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', this tutorial will show you how to create a table by generating a query from the MySQL database.', 'wpdatatables'),
                    ),
                    'step1' => array(
                        'title' => __('Let\'s create a new wpDataTable!', 'wpdatatables'),
                        'content' => __('Click on \'Create a Table\' to access wpDataTables Table Wizard.', 'wpdatatables'),
                    ),
                    'step2' => array(
                        'title' => __('Choose this option', 'wpdatatables'),
                        'content' => __('Please select \'Generate a query to the MySQL database\' to proceed.', 'wpdatatables'),
                    ),
                    'step3' => array(
                        'title' => __('Click Next', 'wpdatatables'),
                        'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step4' => array(
                        'title' => __('Choose a name for your table', 'wpdatatables'),
                        'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                    ),
                    'step5' => array(
                        'title' => __('Welcome to MySQL query generator', 'wpdatatables'),
                        'content' => __('Here you can choose one or more MySQL tables as data sources for your new wpDataTable, either by dragging and dropping the table names or by selecting the MySQL table and clicking the right arrow. \'All SQL columns\' section will be populated. Next, please choose which columns you would like to show in the wpDataTable - as well, either with drag and drop or by selecting columns and clicking on the right arrow to move them to the \'Selected SQL columns\' section.<br><br> When you finish, click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step6' => array(
                        'title' => __('Define SQL tables relations', 'wpdatatables'),
                        'content' => __('Once you configure the relations, mark the checkbox for using an inner join rule, or uncheck to use left join.<br><br> When you finish, click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step7' => array(
                        'title' => __('Set conditions between the columns.', 'wpdatatables'),
                        'content' => __('Add and define conditions between columns in your table by clicking on the button \'+ Add Condition\'.<br><br> When you finish, click on \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step8' => array(
                        'title' => __('Set grouping rules', 'wpdatatables'),
                        'content' => __('By the \'+ Add Grouping\' button, you can define the column grouping rules for the table.<br><br> Once you\'re done, click "Continue" button to move on.', 'wpdatatables'),
                    ),
                    'step9' => array(
                        'title' => __('Click \'Next\'', 'wpdatatables'),
                        'content' => __('Please click \'Next\' button to continue.', 'wpdatatables'),
                    ),
                    'step10' => array(
                        'title' => $hourglassIMG . __('We are creating your query...', 'wpdatatables'),
                        'content' => __('Please wait until wpDataTables query constructor builds the query for you. Once done, it will show five lines of data as a preview.<br><br> Click \'Continue\' button when you see it in the background.', 'wpdatatables'),
                    ),
                    'step11' => array(
                        'title' => __('Your query preview is ready!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('Here you see the query that wpDataTables generated for you. If you are not completely satisfied with it, you can edit it straight from this window.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step12' => array(
                        'title' => __('Preview of the first 5 rows', 'wpdatatables'),
                        'content' => __('Here you can see the first few rows the MySQL server returns using the query provided. If you see “No data”, it means that the MySQL server could not execute it, either because of an error in the query or because it is simply returning an empty data-set, even though the syntax is correct. <br>If this is the case, please click \'Skip Tutorial\' and try to create a new one. <br>If everything is fine, please click \'Continue\' to move forward.', 'wpdatatables'),
                    ),
                    'step13' => array(
                        'title' => $partyTitleIMG . __('Congrats! You are ready to create the table!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('To start working with it, finish the tutorial, click on the \'Create the table\' button and choose in which editor you would like to open it. ', 'wpdatatables'),
                    )
                ),
                'tour6' => array(
                    'step0' => array(
                        'title' => $guideTeacherIMG . __('Welcome to the tutorial!', 'wpdatatables'),
                        'content' => __('Hello ', 'wpdatatables') . $username . $waveIMG . __(', in this tutorial we will show you how to create a chart in wpDataTables plugin.', 'wpdatatables'),
                    ),
                    'step1' => array(
                        'title' => __('Let\'s create a new wpDataTables Chart!', 'wpdatatables'),
                        'content' => __('Click on \'Create a Chart\' to access the wpDataTables Chart Wizard.', 'wpdatatables'),
                    ),
                    'step2' => array(
                        'title' => $chartIMG . __('Welcome to the Chart Wizard!', 'wpdatatables'),
                        'content' => __('You are at the first step now; we will introduce you the wpDataTables Chart Wizard section by section.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step3' => array(
                        'title' => __('Follow the steps in the Chart Wizard', 'wpdatatables'),
                        'content' => __('By following these steps, you will finish building your chart in the Chart Wizard. The current step will always be highlighted in blue.<br><br> Click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step4' => array(
                        'title' => __('Choose a name for your Chart', 'wpdatatables'),
                        'content' => __('Click \'Continue\' button when you’re ready to move forward.', 'wpdatatables'),
                    ),
                    'step5' => array(
                        'title' => __('In wpDataTables you can find several charts render engines.', 'wpdatatables'),
                        'content' => __('Click on the dropdown, and you will see several options that you can choose from. <br><br>To continue, click on the dropdown.', 'wpdatatables'),
                    ),
                    'step6' => array(
                        'title' => __('Choose your desired chart engine.', 'wpdatatables'),
                        'content' => __('By clicking on one of the three options, you will choose the engine that will render your chart.<br><br> When you finish, please click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step7' => array(
                        'title' => __('Different charts types based on the engine you choose. ', 'wpdatatables'),
                        'content' => __('Here you can choose a chart type. Please, click on the chart type that you prefer.<br><br> When you finish, please click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step10' => array(
                        'title' => __('The first step is finished!', 'wpdatatables'),
                        'content' => __('Let\'s move on. Please, click \'Next\' to continue.', 'wpdatatables'),
                    ),
                    'step11' => array(
                        'title' => __('Now you need to choose a wpDataTable based on which we will build a chart for you', 'wpdatatables'),
                        'content' => __('Click on the dropdown, and all your tables will be listed. The columns of the table that you choose will be used for creating the chart.<br><br>If you didn\'t create a wpDataTable yet, then please click on the \'Skip Tutorial\' button and create wpDataTable that would contain the data to visualize first.', 'wpdatatables'),
                    ),
                    'step12' => array(
                        'title' => __('Pick your wpDataTable', 'wpdatatables'),
                        'content' => __('Pick a wpDataTable from which you want to render a chart and when you finish, please click \'Continue\' to move on.', 'wpdatatables'),
                    ),
                    'step13' => array(
                        'title' => __('The second step is finished!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('Let\'s see what is coming up next. <br><br> Please, click \'Next\' to continue.', 'wpdatatables'),
                    ),
                    'step14' => array(
                        'title' => __('Just a heads up!', 'wpdatatables'),
                        'content' => __('Here you will choose from which columns you will create a chart.<br><br> Please click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step15' => array(
                        'title' => __('Meet the wpDataTable Column Blocks', 'wpdatatables'),
                        'content' => __('Here you will choose columns you want to use in the chart. Drag and drop it, or click on the arrow to move the desired column to the \'Columns used in the chart\' section.<br><br> When you finish please, click \'Continue.\'', 'wpdatatables'),
                    ),
                    'step16' => array(
                        'title' => __('Well done!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('Just two more steps to go. Please click \'Next\' to continue.', 'wpdatatables'),
                    ),
                    'step17' => array(
                        'title' => __('Chart settings and chart preview.', 'wpdatatables'),
                        'content' => __('Here you can adjust chart settings, different parameters are grouped in section; adjusting the parameters will be reflected in the preview of your chart in real-time on the right-hand side.<br><br> Please click \'Continue\' button to move forward.', 'wpdatatables'),
                    ),
                    'step18' => array(
                        'title' => __('In this sidebar, you can find the chart settings section.', 'wpdatatables'),
                        'content' => __('By clicking on each section, you can set your desired parameters per section.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                    ),
                    'step19' => array(
                        'title' => __('Here are the available chart options', 'wpdatatables'),
                        'content' => __('Set different chart options for the chosen section to get your desired chart look.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                    ),
                    'step27' => array(
                        'title' => __('How your chart will look like on the page of your website', 'wpdatatables'),
                        'content' => __('Here you can see a preview of your chart based on the settings you have chosen.<br><br> Please click \'Continue\' button to move on.', 'wpdatatables'),
                    ),
                    'step28' => array(
                        'title' => __('You can save your chart now', 'wpdatatables'),
                        'content' => __('If you are satisfied with your chart appearance, click on the \'Save chart\' button and all your settings for this chart will be saved in the database.', 'wpdatatables'),
                    ),
                    'step29' => array(
                        'title' => $partyTitleIMG . __('Congrats! Your first chart is ready!', 'wpdatatables') . $raisedHandsIMG,
                        'content' => __('Now you can copy the shortcode for this chart and paste it in any WP post or page. <br><br>You may now finish this tutorial. ', 'wpdatatables'),
                    )
                )
            );
        }
}
