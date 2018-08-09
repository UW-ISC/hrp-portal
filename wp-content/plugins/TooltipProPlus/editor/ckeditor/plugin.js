/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @file Sample plugin for CKEditor.
 */
( function() {
  CKEDITOR.plugins.add( 'glossary',
  {
    init: function( editor )
    {
        var a = {
            exec:function(editor) {
                var selection = editor.getSelection();
                var text = selection.getSelectedText();
                var nodeHtml = selection.getStartElement();
                console.log(nodeHtml);
                console.log(nodeHtml.innerHTML);
                nodeHtml.setText(nodeHtml.getText().replace(text, '[glossary_exclude]'+text+'[/glossary_exclude]'));
            }
        }
      editor.addCommand( 'glossary_exclude_cmd', a);

      editor.ui.addButton( 'glossary_exclude',
      {
        label: 'Exclude from Glossary',
        command: 'glossary_exclude_cmd',
        toolbar: 'links',
        icon: this.path + '../icon.png'
      } );
    }
  } );
} )();
