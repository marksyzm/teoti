// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
mySettings = {
	nameSpace: 'bbcode',
	previewParserPath:	"ajax/bbcode-parser.php", // path to your BBCode parser
	markupSet: [
		{'name':'Bold', 'key':'B', openWith:'[b]', closeWith:'[/b]'},
		{'name':'Italic', 'key':'I', openWith:'[i]', closeWith:'[/i]'},
		{'name':'Underline', 'key':'U', openWith:'[u]', closeWith:'[/u]'},
		{separator:'---------------' },
		{'name':'Left Align', openWith:'[left]', closeWith:'[/left]'},
		{'name':'Center Align', 'key':'E', openWith:'[center]', closeWith:'[/center]'},
		{'name':'Right Align', 'key':'R', openWith:'[right]', closeWith:'[/right]'},
		{'name':'Justify', 'key':'J', openWith:'[justify]', closeWith:'[/justify]'},
		{separator:'---------------' },
		{'name':'Picture', 'key':'P', replaceWith:'[img][![Source]!][/img]'},
		//{'name':'Link', 'key':'L', openWith:'[url=[![Url]!]]', closeWith:'[/url]', placeHolder:'Your text to link here...'},
		{'name':'Link', 'key':'L', replaceWith:function(markitup){
				if (markitup.selection.match(new RegExp('^(https?:|www\.)'))) return '[url]'+markitup.selection+'[/url]';
				var urlPrompt = prompt('Please enter a URL. '+(markitup.selection.length > 0 ? '':'\n\nIf it is a media file or online video you don\'t have to enter text in the next pop up.'))
				if (urlPrompt) {
					if (markitup.selection.length > 0) {
						return '[url='+urlPrompt+']'+markitup.selection+'[/url]';
					} else {
						var textPrompt = prompt('Enter text to go in your link (optional). Leave empty to use the link as the title or to embed media.');
						if (textPrompt) {
							return '[url='+urlPrompt+']'+textPrompt+'[/url]';
						} else return '[url]'+urlPrompt+'[/url]';
					}
				} else return markitup.selection;
			}
		},
		{separator:'---------------' },
		{'name':'Colors', openWith:'[color=[![Color]!]]', closeWith:'[/color]', dropMenu: [
          {'name':'Yellow', openWith:'[color=#FCE94F]', closeWith:'[/color]', 'className':"col1-1" },
          {'name':'Yellow', openWith:'[color=#EDD400]', closeWith:'[/color]', 'className':"col1-2" },
          {'name':'Yellow',	openWith:'[color=#C4A000]', closeWith:'[/color]', 'className':"col1-3" },
          {'name':'Orange',	openWith:'[color=#FCAF3E]', closeWith:'[/color]', 'className':"col2-1" },
          {'name':'Orange', openWith:'[color=#F57900]', closeWith:'[/color]', 'className':"col2-2" },
          {'name':'Orange',	openWith:'[color=#CE5C00]', closeWith:'[/color]', 'className':"col2-3" },
          {'name':'Brown',	openWith:'[color=#E9B96E]', closeWith:'[/color]', 'className':"col3-1" },
          {'name':'Brown', 	openWith:'[color=#C17D11]', closeWith:'[/color]', 'className':"col3-2" },
          {'name':'Brown', 	openWith:'[color=#8F5902]', closeWith:'[/color]', 'className':"col3-3" },
          {'name':'Green', 	openWith:'[color=#8AE234]', closeWith:'[/color]', 'className':"col4-1" },
          {'name':'Green', 	openWith:'[color=#73D216]', closeWith:'[/color]', 'className':"col4-2" },
          {'name':'Green', 	openWith:'[color=#4E9A06]', closeWith:'[/color]', 'className':"col4-3" },
          {'name':'Blue', 	openWith:'[color=#729FCF]', closeWith:'[/color]', 'className':"col5-1" },
          {'name':'Blue', 	openWith:'[color=#3465A4]', closeWith:'[/color]', 'className':"col5-2" },
          {'name':'Blue', 	openWith:'[color=#204A87]', closeWith:'[/color]', 'className':"col5-3" },
          {'name':'Purple', 	openWith:'[color=#AD7FA8]', closeWith:'[/color]', 'className':"col6-1" },
          {'name':'Purple', 	openWith:'[color=#75507B]', closeWith:'[/color]', 'className':"col6-2" },
          {'name':'Purple', 	openWith:'[color=#5C3566]', closeWith:'[/color]', 'className':"col6-3" },
          {'name':'Red', 	openWith:'[color=#EF2929]', closeWith:'[/color]', 'className':"col7-1" },
          {'name':'Red', 	openWith:'[color=#CC0000]', closeWith:'[/color]', 'className':"col7-2" },
          {'name':'Red', 	openWith:'[color=#A40000]', closeWith:'[/color]', 'className':"col7-3" },
          {'name':'White', 	openWith:'[color=#FFFFFF]', closeWith:'[/color]', 'className':"col8-1" },
          {'name':'Grey', 	openWith:'[color=#D3D7CF]', closeWith:'[/color]', 'className':"col8-2" },
          {'name':'Grey', 	openWith:'[color=#BABDB6]', closeWith:'[/color]', 'className':"col8-3" },
          {'name':'Grey', 	openWith:'[color=#888A85]', closeWith:'[/color]', 'className':"col9-1" },
          {'name':'Grey', 	openWith:'[color=#555753]', closeWith:'[/color]', 'className':"col9-2" },
          {'name':'Black', 	openWith:'[color=#000000]', closeWith:'[/color]', 'className':"col9-3" }
    ]},
    {'name':'Size', 'key':'S', openWith:'[size=[![Text size]!]]', closeWith:'[/size]', dropMenu :[
        {'name':'X Small', openWith:'[size=1]', closeWith:'[/size]' },
        {'name':'Small', openWith:'[size=2]', closeWith:'[/size]' },
        {'name':'Normal', openWith:'[size=3]', closeWith:'[/size]' },
        {'name':'Large', openWith:'[size=4]', closeWith:'[/size]' },
        {'name':'X Large', openWith:'[size=5]', closeWith:'[/size]' },
        {'name':'XX Large', openWith:'[size=6]', closeWith:'[/size]' },
        {'name':'XXX Large', openWith:'[size=7]', closeWith:'[/size]' }
    ]},
    {'name':'Font Family', dropMenu :[
        {'name':'Arial', openWith:'[font=Arial]', closeWith:'[/font]' },
        {'name':'Century Gothic', openWith:'[font=Century Gothic]', closeWith:'[/font]' },
        {'name':'Comic Sans MS', openWith:'[font=Comic Sans MS]', closeWith:'[/font]' },
        {'name':'Georgia', openWith:'[font=Georgia]', closeWith:'[/font]' },
        {'name':'Lucida Console', openWith:'[font=Lucida Console]', closeWith:'[/font]' },
        {'name':'Lucida Sans Unicode', openWith:'[font=Lucida Sans Unicode]', closeWith:'[/font]' },
        {'name':'Tahoma', openWith:'[font=Tahoma]', closeWith:'[/font]' },
        {'name':'Times New Roman', openWith:'[font=Times New Roman]', closeWith:'[/font]' },
        {'name':'Trebuchet MS', openWith:'[font=Trebuchet MS]', closeWith:'[/font]' },
        {'name':'Verdana', openWith:'[font=Verdana]', closeWith:'[/font]' }
    ]},
		{separator:'---------------' },
		{'name':'Bulleted list', openWith:'[list]\n', closeWith:'\n[/list]'},
		{'name':'Numeric list', openWith:'[list=[![Starting number]!]]\n', closeWith:'\n[/list]'}, 
		{'name':'List item', openWith:'[*] '},
		{separator:'---------------' },
		{'name':'Quotes', openWith:'[quote]', closeWith:'[/quote]'},
		{'name':'Code', openWith:'[code]', closeWith:'[/code]'}, 
		{separator:'---------------' },
		{'name':'Clean', 'className':"clean", replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } },
		{'name':'Preview', 'className':"preview", 'call':'preview' }
	]
}