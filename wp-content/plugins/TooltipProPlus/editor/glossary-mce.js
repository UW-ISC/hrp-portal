(function $main(a){
    tinymce.create("tinymce.plugins.Glossary",{
        init:function(ed,url){
//            c=c.replace(/js$/g,"images/");
//            b.onLoadContent.add(function(d,e){
//                if(d.editorId!=="mce_fullscreen"){
//                    a(window).trigger("easyrecipeload",[d,e])
//                    }
//                });
//        b.onSetContent.add(function(d,e){
//            if(d.editorId==="mce_fullscreen"&&!e.initial){
//                a(window).trigger("easyrecipeload",[d,e])
//                }
//            });
    ed.addButton("glossary_exclude",{
        title:"Exclude from Glossary",
        image:url+"/icon.png",
        onclick:function(){
            ed.focus();
            ed.selection.setContent('[glossary_exclude]'+ed.selection.getContent()+'[/glossary_exclude]');
            }
        });
  
},
getInfo:function(){
    return{
        longname:"Glossary",
        author:"CreativeMinds",
        authorurl:"http://plugins.cminds.com/",
        infourl:"http://tooltip.cminds.com/",
        version:"2.0"
    }
}
});
tinymce.PluginManager.add("glossary",tinymce.plugins.Glossary)
}(jQuery));