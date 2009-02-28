<?php
/**
$Id$

Class: Error

    Error debugging functionality.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class Error {

    private function __construct() {}

    /**
    Function handleError
     */
    public static function handleError($errno, $errstr, $errfile, $errline, $context) {
        
        restore_error_handler();
        restore_exception_handler();
        
        while (ob_get_level())
            @ob_end_clean();
        
        $error = array();
        $error['file'] = $errfile;
        $error['line'] = $errline;
        $error['number'] = $errno;
        $error['message'] = $errstr;
        $error['code'] = '';
        $error['context'] = $context;
        
        switch ($errno) {
            case E_ERROR:
                $error['type'] = 'Error';
                $error['class'] = 'error';
                break;
            case E_WARNING:
                $error['type'] = 'Warning';
                $error['class'] = 'notice';
                break;
            case E_PARSE:
                $error['type'] = 'Parsing Error';
                $error['class'] = 'error';
                break;
            case E_NOTICE:
                $error['type'] = 'Notice';
                $error['class'] = 'notice';
                break;
            case E_CORE_ERROR:
                $error['type'] = 'Core Error';
                $error['class'] = 'error';
                break;
            case E_CORE_WARNING:
                $error['type'] = 'Core Warning';
                $error['class'] = 'notice';
                break;
            case E_COMPILE_ERROR:
                $error['type'] = 'Compile Error';
                $error['class'] = 'error';
                break;
            case E_COMPILE_WARNING:
                $error['type'] = 'Compile Warning';
                $error['class'] = 'notice';
                break;
            case E_USER_ERROR:
                $error['type'] = 'User Error';
                $error['class'] = 'error';
                break;
            case E_USER_WARNING:
                $error['type'] = 'User Warning';
                $error['class'] = 'notice';
                break;
            case E_USER_NOTICE:
                $error['type'] = 'User Notice';
                $error['class'] = 'notice';
                break;
            case E_STRICT:
                $error['type'] = 'Runtime Notice';
                $error['class'] = 'notice';
                break;
            default:
                $error['type'] = 'Unknown Error';
                $error['class'] = 'error';
                break;
        }
        
        // TODO: Shitty Code, Cleanup Needed
        if (is_readable($errfile)) {
            $lines = file($errfile);
            $startline = (($errline - 5) < 0) ? 0 : ($errline - 5);
            $startline = ((count($lines) - $startline) < 9) ? $startline + 1 - (count($lines) - $startline) : $startline;
            $startline = ($startline < 1) ? 0 : $startline;
            $lines = array_slice($lines, $startline, 9);
            $error['startline'] = $startline + 1;
            
            foreach ($lines as $line) {
                $error['code'] .= htmlentities(rtrim($line));
                $error['code'] .= "&nbsp;\n";
            }
        }
        
        $error['backtrace'] = array();
        
        $backtraces = array_slice(debug_backtrace(), 1);
        
        $j = 0;
        
        foreach ($backtraces as $backtrace) {
            
            $func = (isset($backtrace['function'])) ? $backtrace['function'] : '';
            $file = (isset($backtrace['file'])) ? $backtrace['file'] : null;
            $line = (isset($backtrace['line'])) ? $backtrace['line'] : null;
            $args = (isset($backtrace['args'])) ? $backtrace['args'] : array();
            $code = '';
            
            $error['backtrace'][$j]['func'] = $func;
            $error['backtrace'][$j]['line'] = $line;
            $error['backtrace'][$j]['file'] = $file;
            $error['backtrace'][$j]['args'] = $args;
            $error['backtrace'][$j]['code'] = '';
            
            // TODO: Shitty Code, Cleanup Needed
            if (is_readable($file)) {
                $lines = file($file);
                $startline = (($line - 5) < 0) ? 0 : ($line - 5);
                $startline = ((count($lines) - $startline) < 9) ? $startline + 1 - (count($lines) - $startline) : $startline;
                $startline = ($startline < 1) ? 0 : $startline;
                $lines = array_slice($lines, $startline, 9);
                
                $error['backtrace'][$j]['startline'] = $startline + 1;
                
                foreach ($lines as $line) {
                    $code .= htmlentities(rtrim($line));
                    $code .= "&nbsp;\n";
                }
                
                $error['backtrace'][$j]['code'] = $code;
            }
            
            $j++;
        }
        
        self::render($error);
        die();
    }

    /**
    Function handleException
     */
    public static function handleException($exception) {
        
        restore_error_handler();
        restore_exception_handler();
        
        while (ob_get_level())
            @ob_end_clean();
        
        $error = array();
        $error['file'] = $exception->getFile();
        $error['line'] = $exception->getLine();
        $error['number'] = $exception->getCode();
        $error['message'] = $exception->getMessage();
        $error['type'] = get_class($exception);
        $error['class'] = 'error';
        $error['code'] = '';
        $error['context'] = '';
        
        // TODO: Shitty Code, Cleanup Needed
        if (is_readable($exception->getFile())) {
            $lines = file($exception->getFile());
            $startline = (($exception->getLine() - 5) < 0) ? 0 : ($exception->getLine() - 5);
            $startline = ((count($lines) - $startline) < 9) ? $startline + 1 - (count($lines) - $startline) : $startline;
            $startline = ($startline < 1) ? 0 : $startline;
            $lines = array_slice($lines, $startline, 9);
            $error['startline'] = $startline + 1;
            
            foreach ($lines as $line) {
                $error['code'] .= htmlentities(rtrim($line));
                $error['code'] .= "&nbsp;\n";
            }
        }
        
        $error['backtrace'] = array();
        
        $backtraces = $exception->getTrace();
        
        $j = 0;
        
        foreach ($backtraces as $backtrace) {
            
            $func = (isset($backtrace['function'])) ? $backtrace['function'] : '';
            $line = (isset($backtrace['line'])) ? $backtrace['line'] : null;
            $file = (isset($backtrace['file'])) ? $backtrace['file'] : null;
            $args = (isset($backtrace['args'])) ? $backtrace['args'] : array();
            $code = '';
            
            $error['backtrace'][$j]['func'] = $func;
            $error['backtrace'][$j]['line'] = $line;
            $error['backtrace'][$j]['file'] = $file;
            $error['backtrace'][$j]['args'] = $args;
            $error['backtrace'][$j]['code'] = '';
            
            // TODO: Shitty Code, Cleanup Needed
            if (is_readable($file)) {
                $lines = file($file);
                $startline = (($line - 5) < 0) ? 0 : ($line - 5);
                $startline = ((count($lines) - $startline) < 9) ? $startline + 1 - (count($lines) - $startline) : $startline;
                $startline = ($startline < 1) ? 0 : $startline;
                $lines = array_slice($lines, $startline, 9);
                
                $error['backtrace'][$j]['startline'] = $startline + 1;
                
                foreach ($lines as $line) {
                    $code .= htmlentities(rtrim($line));
                    $code .= "&nbsp;\n";
                }
            }
            
            $error['backtrace'][$j]['code'] = $code;
            
            $j++;
        }
        
        self::render($error);
        die;        
    }
    
    /**
    Function render
     */
    private static function render($error)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        
        extract($error);
        
        $tracehtml = '';
        
        foreach($backtrace as $trace) {
            if (isset($trace['file'])) {
                
                $tracehtml .= '<div class="success">' . $trace['func'] . '() was called from ' . $trace['file'] . ', line: ' . $trace['line'] . '.</div>';

                if(count($trace['args']) > 0) {
                    $tracehtml .= '<h4>Arguments:</h4>' . "\n";
                    $tracehtml .= '<pre name="code" class="php:nocontrols:nogutter">' . print_r($trace['args'], true) . '</pre>' . "\n";
                }

                if(isset($trace['startline'])) {
                    $tracehtml .= '<h4>Source:</h4>' . "\n";
                    $tracehtml .= '<pre name="code" class="php:nocontrols:firstline[' . $trace['startline'] . ']">' . $trace['code'] . '</pre>' . "\n";
                }
            }
        }
        
        $output = <<<ERROR
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Error Occurred</title>
    <style type="text/css">
    
        body
        {
            color:#222222;
            font-family:"Helvetica Neue","Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
            font-size:75%;
            background:#FFFFFF none repeat scroll 0%;
            line-height:1.5;
            margin:1.5em 0pt;
        }
    
        .container
        {
            margin:0pt auto;
            width:950px;
        }
        
        h1, h2, h3, h4
        {
            font-weight:normal;
            color:#111111;
            font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;
        }        

        h2 {
            font-size:2em;
            margin-bottom:0.75em;
        }        
        
        h3
        {
            font-size:1.5em;
            line-height:1;
            margin-bottom:1em;
        }        

        h4
        {
            font-size:1.2em;
            line-height:1.25;
            margin-bottom:1.25em;
        }
        
        hr.space {
            background:#FFFFFF none repeat scroll 0%;
            color:#FFFFFF;
            border:medium none;
            clear:both;
            float:none;
            height:0.1em;
            margin:0pt 0pt 1.4em;
            width:100%;
        }
        
        .error, .notice, .success {
            border:2px solid #DDDDDD;
            margin-bottom:1em;
            padding:0.8em;
        }        
        
        .error {
            background:#FBE3E4 none repeat scroll 0%;
            border-color:#FBC2C4;
            color:#D12F19;
        }
        
        .notice {
            background:#FFF6BF none repeat scroll 0%;
            border-color:#FFD324;
            color:#817134;
        }

        .success {
            background:#E6EFC2 none repeat scroll 0%;
            border-color:#C6D880;
            color:#529214;
        }       
        .dp-highlighter
        {
            font-family: "Consolas", "Courier New", Courier, mono, serif;
            font-size: 12px;
            background-color: #E7E5DC;
            width: 99%;
            overflow: auto;
            margin: 18px 0 18px 0 !important;
            padding-top: 1px; /* adds a little border on top when controls are hidden */
        }
        
        /* clear styles */
        .dp-highlighter ol,
        .dp-highlighter ol li,
        .dp-highlighter ol li span 
        {
            margin: 0;
            padding: 0;
            border: none;
        }
        
        .dp-highlighter a,
        .dp-highlighter a:hover
        {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
        }
        
        .dp-highlighter .bar
        {
            padding-left: 45px;
        }
        
        .dp-highlighter.collapsed .bar,
        .dp-highlighter.nogutter .bar
        {
            padding-left: 0px;
        }
        
        .dp-highlighter ol
        {
            list-style: decimal; /* for ie */
            background-color: #fff;
            margin: 0px 0px 1px 45px !important; /* 1px bottom margin seems to fix occasional Firefox scrolling */
            padding: 0px;
            color: #5C5C5C;
        }
        
        .dp-highlighter.nogutter ol,
        .dp-highlighter.nogutter ol li
        {
            list-style: none !important;
            margin-left: 0px !important;
        }
        
        .dp-highlighter ol li,
        .dp-highlighter .columns div
        {
            list-style: decimal-leading-zero; /* better look for others, override cascade from OL */
            list-style-position: outside !important;
            border-left: 3px solid #6CE26C;
            background-color: #F8F8F8;
            color: #5C5C5C;
            padding: 0 3px 0 10px !important;
            margin: 0 !important;
            line-height: 14px;
        }
        
        .dp-highlighter.nogutter ol li,
        .dp-highlighter.nogutter .columns div
        {
            border: 0;
        }
        
        .dp-highlighter .columns
        {
            background-color: #F8F8F8;
            color: gray;
            overflow: hidden;
            width: 100%;
        }
        
        .dp-highlighter .columns div
        {
            padding-bottom: 5px;
        }
        
        .dp-highlighter ol li.alt
        {
            background-color: #FFF;
            color: inherit;
        }
        
        .dp-highlighter ol li span
        {
            color: black;
            background-color: inherit;
        }
        
        /* Adjust some properties when collapsed */
        
        .dp-highlighter.collapsed ol
        {
            margin: 0px;
        }
        
        .dp-highlighter.collapsed ol li
        {
            display: none;
        }
        
        /* Additional modifications when in print-view */
        
        .dp-highlighter.printing
        {
            border: none;
        }
        
        .dp-highlighter.printing .tools
        {
            display: none !important;
        }
        
        .dp-highlighter.printing li
        {
            display: list-item !important;
        }
        
        /* Styles for the tools */
        
        .dp-highlighter .tools
        {
            padding: 3px 8px 3px 10px;
            font: 9px Verdana, Geneva, Arial, Helvetica, sans-serif;
            color: silver;
            background-color: #f8f8f8;
            padding-bottom: 10px;
            border-left: 3px solid #6CE26C;
        }
        
        .dp-highlighter.nogutter .tools
        {
            border-left: 0;
        }
        
        .dp-highlighter.collapsed .tools
        {
            border-bottom: 0;
        }
        
        .dp-highlighter .tools a
        {
            font-size: 9px;
            color: #a0a0a0;
            background-color: inherit;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .dp-highlighter .tools a:hover
        {
            color: red;
            background-color: inherit;
            text-decoration: underline;
        }
        
        /* About dialog styles */
        
        .dp-about { background-color: #fff; color: #333; margin: 0px; padding: 0px; }
        .dp-about table { width: 100%; height: 100%; font-size: 11px; font-family: Tahoma, Verdana, Arial, sans-serif !important; }
        .dp-about td { padding: 10px; vertical-align: top; }
        .dp-about .copy { border-bottom: 1px solid #ACA899; height: 95%; }
        .dp-about .title { color: red; background-color: inherit; font-weight: bold; }
        .dp-about .para { margin: 0 0 4px 0; }
        .dp-about .footer { background-color: #ECEADB; color: #333; border-top: 1px solid #fff; text-align: right; }
        .dp-about .close { font-size: 11px; font-family: Tahoma, Verdana, Arial, sans-serif !important; background-color: #ECEADB; color: #333; width: 60px; height: 22px; }
        
        /* Language specific styles */
        
        .dp-highlighter .comment, .dp-highlighter .comments { color: #008200; background-color: inherit; }
        .dp-highlighter .string { color: blue; background-color: inherit; }
        .dp-highlighter .keyword { color: #069; font-weight: bold; background-color: inherit; }
        .dp-highlighter .preprocessor { color: gray; background-color: inherit; }
    
    </style>
    
    <script type="text/javascript">
        <!--
        var dp={sh:{Toolbar:{},Utils:{},RegexLib:{},Brushes:{},Strings:{AboutDialog:'<html><head><title>About...</title></head><body class="dp-about"><table cellspacing="0"><tr><td class="copy"><p class="title">dp.SyntaxHighlighter</div><div class="para">Version: {V}</p><p><a href="http://www.dreamprojections.com/syntaxhighlighter/?ref=about" target="_blank">http://www.dreamprojections.com/syntaxhighlighter</a></p>&copy;2004-2007 Alex Gorbatchev.</td></tr><tr><td class="footer"><input type="button" class="close" value="OK" onClick="window.close()"/></td></tr></table></body></html>'},ClipboardSwf:null,Version:'1.5.1'}};dp.SyntaxHighlighter=dp.sh;dp.sh.Toolbar.Commands={ExpandSource:{label:'+ expand source',check:function(highlighter){return highlighter.collapse;},func:function(sender,highlighter)
        {sender.parentNode.removeChild(sender);highlighter.div.className=highlighter.div.className.replace('collapsed','');}},ViewSource:{label:'view plain',func:function(sender,highlighter)
        {var code=dp.sh.Utils.FixForBlogger(highlighter.originalCode).replace(/</g,'&lt;');var wnd=window.open('','_blank','width=750, height=400, location=0, resizable=1, menubar=0, scrollbars=0');wnd.document.write('<textarea style="width:99%;height:99%">'+code+'</textarea>');wnd.document.close();}},CopyToClipboard:{label:'copy to clipboard',check:function(){return window.clipboardData!=null||dp.sh.ClipboardSwf!=null;},func:function(sender,highlighter)
        {var code=dp.sh.Utils.FixForBlogger(highlighter.originalCode).replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');if(window.clipboardData)
        {window.clipboardData.setData('text',code);}
        else if(dp.sh.ClipboardSwf!=null)
        {var flashcopier=highlighter.flashCopier;if(flashcopier==null)
        {flashcopier=document.createElement('div');highlighter.flashCopier=flashcopier;highlighter.div.appendChild(flashcopier);}
        flashcopier.innerHTML='<embed src="'+dp.sh.ClipboardSwf+'" FlashVars="clipboard='+encodeURIComponent(code)+'" width="0" height="0" type="application/x-shockwave-flash"></embed>';}
        alert('The code is in your clipboard now');}},PrintSource:{label:'print',func:function(sender,highlighter)
        {var iframe=document.createElement('IFRAME');var doc=null;iframe.style.cssText='position:absolute;width:0px;height:0px;left:-500px;top:-500px;';document.body.appendChild(iframe);doc=iframe.contentWindow.document;dp.sh.Utils.CopyStyles(doc,window.document);doc.write('<div class="'+highlighter.div.className.replace('collapsed','')+' printing">'+highlighter.div.innerHTML+'</div>');doc.close();iframe.contentWindow.focus();iframe.contentWindow.print();alert('Printing...');document.body.removeChild(iframe);}},About:{label:'?',func:function(highlighter)
        {var wnd=window.open('','_blank','dialog,width=300,height=150,scrollbars=0');var doc=wnd.document;dp.sh.Utils.CopyStyles(doc,window.document);doc.write(dp.sh.Strings.AboutDialog.replace('{V}',dp.sh.Version));doc.close();wnd.focus();}}};dp.sh.Toolbar.Create=function(highlighter)
        {var div=document.createElement('DIV');div.className='tools';for(var name in dp.sh.Toolbar.Commands)
        {var cmd=dp.sh.Toolbar.Commands[name];if(cmd.check!=null&&!cmd.check(highlighter))
        continue;div.innerHTML+='<a href="#" onclick="dp.sh.Toolbar.Command(\\''+name+'\\',this);return false;">'+cmd.label+'</a>';}
        return div;}
        dp.sh.Toolbar.Command=function(name,sender)
        {var n=sender;while(n!=null&&n.className.indexOf('dp-highlighter')==-1)
        n=n.parentNode;if(n!=null)
        dp.sh.Toolbar.Commands[name].func(sender,n.highlighter);}
        dp.sh.Utils.CopyStyles=function(destDoc,sourceDoc)
        {var links=sourceDoc.getElementsByTagName('link');for(var i=0;i<links.length;i++)
        if(links[i].rel.toLowerCase()=='stylesheet')
        destDoc.write('<link type="text/css" rel="stylesheet" href="'+links[i].href+'"></link>');}
        dp.sh.Utils.FixForBlogger=function(str)
        {return(dp.sh.isBloggerMode==true)?str.replace(/<br\\s*\\/?>|&lt;br\\s*\\/?&gt;/gi,'\\n'):str;}
        dp.sh.RegexLib={MultiLineCComments:new RegExp('/\\\\*[\\\\s\\\\S]*?\\\\*/','gm'),SingleLineCComments:new RegExp('//.*$','gm'),SingleLinePerlComments:new RegExp('#.*$','gm'),DoubleQuotedString:new RegExp('"(?:\\\\.|(\\\\\\\\\\\\")|[^\\\\""\\\\n])*"','g'),SingleQuotedString:new RegExp("'(?:\\\\.|(\\\\\\\\\\\\')|[^\\\\''\\\\n])*'",'g')};dp.sh.Match=function(value,index,css)
        {this.value=value;this.index=index;this.length=value.length;this.css=css;}
        dp.sh.Highlighter=function()
        {this.noGutter=false;this.addControls=true;this.collapse=false;this.tabsToSpaces=true;this.wrapColumn=80;this.showColumns=true;}
        dp.sh.Highlighter.SortCallback=function(m1,m2)
        {if(m1.index<m2.index)
        return-1;else if(m1.index>m2.index)
        return 1;else
        {if(m1.length<m2.length)
        return-1;else if(m1.length>m2.length)
        return 1;}
        return 0;}
        dp.sh.Highlighter.prototype.CreateElement=function(name)
        {var result=document.createElement(name);result.highlighter=this;return result;}
        dp.sh.Highlighter.prototype.GetMatches=function(regex,css)
        {var index=0;var match=null;while((match=regex.exec(this.code))!=null)
        this.matches[this.matches.length]=new dp.sh.Match(match[0],match.index,css);}
        dp.sh.Highlighter.prototype.AddBit=function(str,css)
        {if(str==null||str.length==0)
        return;var span=this.CreateElement('SPAN');str=str.replace(/ /g,'&nbsp;');str=str.replace(/</g,'&lt;');str=str.replace(/\\n/gm,'&nbsp;<br>');if(css!=null)
        {if((/br/gi).test(str))
        {var lines=str.split('&nbsp;<br>');for(var i=0;i<lines.length;i++)
        {span=this.CreateElement('SPAN');span.className=css;span.innerHTML=lines[i];this.div.appendChild(span);if(i+1<lines.length)
        this.div.appendChild(this.CreateElement('BR'));}}
        else
        {span.className=css;span.innerHTML=str;this.div.appendChild(span);}}
        else
        {span.innerHTML=str;this.div.appendChild(span);}}
        dp.sh.Highlighter.prototype.IsInside=function(match)
        {if(match==null||match.length==0)
        return false;for(var i=0;i<this.matches.length;i++)
        {var c=this.matches[i];if(c==null)
        continue;if((match.index>c.index)&&(match.index<c.index+c.length))
        return true;}
        return false;}
        dp.sh.Highlighter.prototype.ProcessRegexList=function()
        {for(var i=0;i<this.regexList.length;i++)
        this.GetMatches(this.regexList[i].regex,this.regexList[i].css);}
        dp.sh.Highlighter.prototype.ProcessSmartTabs=function(code)
        {var lines=code.split('\\n');var result='';var tabSize=4;var tab='\\t';function InsertSpaces(line,pos,count)
        {var left=line.substr(0,pos);var right=line.substr(pos+1,line.length);var spaces='';for(var i=0;i<count;i++)
        spaces+=' ';return left+spaces+right;}
        function ProcessLine(line,tabSize)
        {if(line.indexOf(tab)==-1)
        return line;var pos=0;while((pos=line.indexOf(tab))!=-1)
        {var spaces=tabSize-pos%tabSize;line=InsertSpaces(line,pos,spaces);}
        return line;}
        for(var i=0;i<lines.length;i++)
        result+=ProcessLine(lines[i],tabSize)+'\\n';return result;}
        dp.sh.Highlighter.prototype.SwitchToList=function()
        {var html=this.div.innerHTML.replace(/<(br)\\/?>/gi,'\\n');var lines=html.split('\\n');if(this.addControls==true)
        this.bar.appendChild(dp.sh.Toolbar.Create(this));if(this.showColumns)
        {var div=this.CreateElement('div');var columns=this.CreateElement('div');var showEvery=10;var i=1;while(i<=150)
        {if(i%showEvery==0)
        {div.innerHTML+=i;i+=(i+'').length;}
        else
        {div.innerHTML+='&middot;';i++;}}
        columns.className='columns';columns.appendChild(div);this.bar.appendChild(columns);}
        for(var i=0,lineIndex=this.firstLine;i<lines.length-1;i++,lineIndex++)
        {var li=this.CreateElement('LI');var span=this.CreateElement('SPAN');li.className=(i%2==0)?'alt':'';span.innerHTML=lines[i]+'&nbsp;';li.appendChild(span);this.ol.appendChild(li);}
        this.div.innerHTML='';}
        dp.sh.Highlighter.prototype.Highlight=function(code)
        {function Trim(str)
        {return str.replace(/^\\s*(.*?)[\\s\\n]*$/g,'$1');}
        function Chop(str)
        {return str.replace(/\\n*$/,'').replace(/^\\n*/,'');}
        function Unindent(str)
        {var lines=dp.sh.Utils.FixForBlogger(str).split('\\n');var indents=new Array();var regex=new RegExp('^\\\\s*','g');var min=1000;for(var i=0;i<lines.length&&min>0;i++)
        {if(Trim(lines[i]).length==0)
        continue;var matches=regex.exec(lines[i]);if(matches!=null&&matches.length>0)
        min=Math.min(matches[0].length,min);}
        if(min>0)
        for(var i=0;i<lines.length;i++)
        lines[i]=lines[i].substr(min);return lines.join('\\n');}
        function Copy(string,pos1,pos2)
        {return string.substr(pos1,pos2-pos1);}
        var pos=0;if(code==null)
        code='';this.originalCode=code;this.code=Chop(Unindent(code));this.div=this.CreateElement('DIV');this.bar=this.CreateElement('DIV');this.ol=this.CreateElement('OL');this.matches=new Array();this.div.className='dp-highlighter';this.div.highlighter=this;this.bar.className='bar';this.ol.start=this.firstLine;if(this.CssClass!=null)
        this.ol.className=this.CssClass;if(this.collapse)
        this.div.className+=' collapsed';if(this.noGutter)
        this.div.className+=' nogutter';if(this.tabsToSpaces==true)
        this.code=this.ProcessSmartTabs(this.code);this.ProcessRegexList();if(this.matches.length==0)
        {this.AddBit(this.code,null);this.SwitchToList();this.div.appendChild(this.bar);this.div.appendChild(this.ol);return;}
        this.matches=this.matches.sort(dp.sh.Highlighter.SortCallback);for(var i=0;i<this.matches.length;i++)
        if(this.IsInside(this.matches[i]))
        this.matches[i]=null;for(var i=0;i<this.matches.length;i++)
        {var match=this.matches[i];if(match==null||match.length==0)
        continue;this.AddBit(Copy(this.code,pos,match.index),null);this.AddBit(match.value,match.css);pos=match.index+match.length;}
        this.AddBit(this.code.substr(pos),null);this.SwitchToList();this.div.appendChild(this.bar);this.div.appendChild(this.ol);}
        dp.sh.Highlighter.prototype.GetKeywords=function(str)
        {return'\\\\b'+str.replace(/ /g,'\\\\b|\\\\b')+'\\\\b';}
        dp.sh.BloggerMode=function()
        {dp.sh.isBloggerMode=true;}
        dp.sh.HighlightAll=function(name,showGutter,showControls,collapseAll,firstLine,showColumns)
        {function FindValue()
        {var a=arguments;for(var i=0;i<a.length;i++)
        {if(a[i]==null)
        continue;if(typeof(a[i])=='string'&&a[i]!='')
        return a[i]+'';if(typeof(a[i])=='object'&&a[i].value!='')
        return a[i].value+'';}
        return null;}
        function IsOptionSet(value,list)
        {for(var i=0;i<list.length;i++)
        if(list[i]==value)
        return true;return false;}
        function GetOptionValue(name,list,defaultValue)
        {var regex=new RegExp('^'+name+'\\\\[(\\\\w+)\\\\]$','gi');var matches=null;for(var i=0;i<list.length;i++)
        if((matches=regex.exec(list[i]))!=null)
        return matches[1];return defaultValue;}
        function FindTagsByName(list,name,tagName)
        {var tags=document.getElementsByTagName(tagName);for(var i=0;i<tags.length;i++)
        if(tags[i].getAttribute('name')==name)
        list.push(tags[i]);}
        var elements=[];var highlighter=null;var registered={};var propertyName='innerHTML';FindTagsByName(elements,name,'pre');FindTagsByName(elements,name,'textarea');if(elements.length==0)
        return;for(var brush in dp.sh.Brushes)
        {var aliases=dp.sh.Brushes[brush].Aliases;if(aliases==null)
        continue;for(var i=0;i<aliases.length;i++)
        registered[aliases[i]]=brush;}
        for(var i=0;i<elements.length;i++)
        {var element=elements[i];var options=FindValue(element.attributes['class'],element.className,element.attributes['language'],element.language);var language='';if(options==null)
        continue;options=options.split(':');language=options[0].toLowerCase();if(registered[language]==null)
        continue;highlighter=new dp.sh.Brushes[registered[language]]();element.style.display='none';highlighter.noGutter=(showGutter==null)?IsOptionSet('nogutter',options):!showGutter;highlighter.addControls=(showControls==null)?!IsOptionSet('nocontrols',options):showControls;highlighter.collapse=(collapseAll==null)?IsOptionSet('collapse',options):collapseAll;highlighter.showColumns=(showColumns==null)?IsOptionSet('showcolumns',options):showColumns;var headNode=document.getElementsByTagName('head')[0];if(highlighter.Style&&headNode)
        {var styleNode=document.createElement('style');styleNode.setAttribute('type','text/css');if(styleNode.styleSheet)
        {styleNode.styleSheet.cssText=highlighter.Style;}
        else
        {var textNode=document.createTextNode(highlighter.Style);styleNode.appendChild(textNode);}
        headNode.appendChild(styleNode);}
        highlighter.firstLine=(firstLine==null)?parseInt(GetOptionValue('firstline',options,1)):firstLine;highlighter.Highlight(element[propertyName]);highlighter.source=element;element.parentNode.insertBefore(highlighter.div,element);}}
        
        dp.sh.Brushes.Php=function()
        {var funcs='abs acos acosh addcslashes addslashes '+'array_change_key_case array_chunk array_combine array_count_values array_diff '+'array_diff_assoc array_diff_key array_diff_uassoc array_diff_ukey array_fill '+'array_filter array_flip array_intersect array_intersect_assoc array_intersect_key '+'array_intersect_uassoc array_intersect_ukey array_key_exists array_keys array_map '+'array_merge array_merge_recursive array_multisort array_pad array_pop array_product '+'array_push array_rand array_reduce array_reverse array_search array_shift '+'array_slice array_splice array_sum array_udiff array_udiff_assoc '+'array_udiff_uassoc array_uintersect array_uintersect_assoc '+'array_uintersect_uassoc array_unique array_unshift array_values array_walk '+'array_walk_recursive atan atan2 atanh base64_decode base64_encode base_convert '+'basename bcadd bccomp bcdiv bcmod bcmul bindec bindtextdomain bzclose bzcompress '+'bzdecompress bzerrno bzerror bzerrstr bzflush bzopen bzread bzwrite ceil chdir '+'checkdate checkdnsrr chgrp chmod chop chown chr chroot chunk_split class_exists '+'closedir closelog copy cos cosh count count_chars date decbin dechex decoct '+'deg2rad delete ebcdic2ascii echo empty end ereg ereg_replace eregi eregi_replace error_log '+'error_reporting escapeshellarg escapeshellcmd eval exec exit exp explode extension_loaded '+'feof fflush fgetc fgetcsv fgets fgetss file_exists file_get_contents file_put_contents '+'fileatime filectime filegroup fileinode filemtime fileowner fileperms filesize filetype '+'floatval flock floor flush fmod fnmatch fopen fpassthru fprintf fputcsv fputs fread fscanf '+'fseek fsockopen fstat ftell ftok getallheaders getcwd getdate getenv gethostbyaddr gethostbyname '+'gethostbynamel getimagesize getlastmod getmxrr getmygid getmyinode getmypid getmyuid getopt '+'getprotobyname getprotobynumber getrandmax getrusage getservbyname getservbyport gettext '+'gettimeofday gettype glob gmdate gmmktime ini_alter ini_get ini_get_all ini_restore ini_set '+'interface_exists intval ip2long is_a is_array is_bool is_callable is_dir is_double '+'is_executable is_file is_finite is_float is_infinite is_int is_integer is_link is_long '+'is_nan is_null is_numeric is_object is_readable is_real is_resource is_scalar is_soap_fault '+'is_string is_subclass_of is_uploaded_file is_writable is_writeable mkdir mktime nl2br '+'parse_ini_file parse_str parse_url passthru pathinfo readlink realpath rewind rewinddir rmdir '+'round str_ireplace str_pad str_repeat str_replace str_rot13 str_shuffle str_split '+'str_word_count strcasecmp strchr strcmp strcoll strcspn strftime strip_tags stripcslashes '+'stripos stripslashes stristr strlen strnatcasecmp strnatcmp strncasecmp strncmp strpbrk '+'strpos strptime strrchr strrev strripos strrpos strspn strstr strtok strtolower strtotime '+'strtoupper strtr strval substr substr_compare';var keywords='and or xor __FILE__ __LINE__ array as break case '+'cfunction class const continue declare default die do else '+'elseif empty enddeclare endfor endforeach endif endswitch endwhile '+'extends for foreach function include include_once global if '+'new old_function return static switch use require require_once '+'var while __FUNCTION__ __CLASS__ '+'__METHOD__ abstract interface public implements extends private protected throw';this.regexList=[{regex:dp.sh.RegexLib.SingleLineCComments,css:'comment'},{regex:dp.sh.RegexLib.MultiLineCComments,css:'comment'},{regex:dp.sh.RegexLib.DoubleQuotedString,css:'string'},{regex:dp.sh.RegexLib.SingleQuotedString,css:'string'},{regex:new RegExp('\\\\$\\\\w+','g'),css:'vars'},{regex:new RegExp(this.GetKeywords(funcs),'gmi'),css:'func'},{regex:new RegExp(this.GetKeywords(keywords),'gm'),css:'keyword'}];this.CssClass='dp-c';}
        dp.sh.Brushes.Php.prototype=new dp.sh.Highlighter();dp.sh.Brushes.Php.Aliases=['php'];
        
        window.onload = function () {
            dp.SyntaxHighlighter.HighlightAll('code');
        }
        //-->
    </script>
</head>
<body>
    <div class="container">

        <h2>$type on $file, line $line:</h2>

        <div class="$class">$message</div>

        <h4>Source:</h4>
        <pre name="code" class="php:nocontrols:firstline[$startline]">$code</pre>

        <hr class="space" />

        <h3>Backtrace:</h3>
        
        $tracehtml

    </div>
    
    <br />

</body>
</html>    
ERROR;
        echo $output;
    }
}