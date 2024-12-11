<?php if (!defined('PmWiki')) exit();
/*  Copyright 2019-2024 Petko Yotov www.pmwiki.org/petko
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script includes and configures one or more JavaScript utilities, 
    when they are enabled by the wiki administrator, notably:
    
    * Tables of contents
    * Sortable tables
    * Localized time stamps
    * Improved recent changes
    * Syntax highlighting (PmWiki markup)
    * Syntax highlighting (external)
    * Copy code button from <pre> blocks.
    * Collapsible sections
    * Email obfuscation
    
    To disable all these functions, add to config.php:
      $EnablePmUtils = 0;
*/
function PmUtilsJS($pagename) {
  global $PmTOC, $EnableSortable, $EnableHighlight, $EnableLocalTimes, $ToggleNextSelector,
    $LinkFunctions, $FarmD, $HTMLStylesFmt, $HTMLHeaderFmt, $EnablePmSyntax, $CustomSyntax, 
    $EnableCopyCode, $EnableDarkThemeToggle, $EnableRedirectQuiet, 
    $EnableUploadDrop, $UploadExtSize, $EnableUploadAuthorRequired;

  $utils = "$FarmD/pub/pmwiki-utils.js";
  $dark  = "$FarmD/pub/pmwiki-darktoggle.js";
  
  $cc = IsEnabled($EnableCopyCode, 0)? XL('Copy code') : '';
  
  if ($cc) {
    SDVA($HTMLStylesFmt, array('copycode'=>'
    .pmcopycode { cursor:pointer; display:block; border-radius:.2em; opacity:.2; position:relative; z-index:2; }
    .pmcopycode::before { content:"+"; display:block; width:.8em; height:.8em; line-height:.8em; text-align:center;  }
    .pmcopycode.copied::before { content:"\\2714"; }
    .pmcopycode.copied { background-color:#afa; }
    html.pmDarkTheme .pmcopycode.copied { background-color: #272; }
    pre:hover .pmcopycode { opacity:1; }
    '));
  }
  if (IsEnabled($EnableUploadDrop, 0) && CondAuth($pagename, 'upload', 1)) {
    $ddmu = array(
      'action'  => '{$PageUrl}?action=postupload',
      'token'   => ['$TokenName', pmtoken()],
      'label'   => XL('ULdroplabel'),
      'badtype' => str_replace('$', '#', XL('ULbadtype')),
      'toobig'  => str_replace('$', '#', preg_replace('/\\s+/',' ', XL('ULtoobigext'))),
      'sizes'   => array(),
    );
    if(IsEnabled($EnableUploadAuthorRequired, 0)) {
      $ddmu['areq'] = XL('ULauthorrequired');
    }
    
    foreach($UploadExtSize as $ext=>$bytes) {
      if($bytes>0) $ddmu['sizes'][$ext] = $bytes;
    }
  }
  else $ddmu = false;
  
  if (file_exists($utils)) {
    $mtime = filemtime($utils);
    $config = array(
      'sortable' => IsEnabled($EnableSortable, 1),
      'highlight' => IsEnabled($EnableHighlight, 0),
      'copycode' => $cc,
      'toggle' => IsEnabled($ToggleNextSelector, 0),
      'localtimes' => IsEnabled($EnableLocalTimes, 0),
      'rediquiet' => IsEnabled($EnableRedirectQuiet, 0),
      'updrop' => $ddmu,
    );
    $enabled = $PmTOC['Enable'];
    foreach($config as $i) {
      $enabled = $enabled || $i;
    }
    
    if ($enabled) {
      $config['pmtoc'] = $PmTOC;
      SDVA($HTMLHeaderFmt, array('pmwiki-utils' =>
        "<script type='text/javascript' src='\$FarmPubDirUrl/pmwiki-utils.js?st=$mtime'
          data-config='".pm_json_encode($config, true)."' data-fullname='{\$FullName}'></script>"
      ));
    }
  }
  
  if (IsEnabled($EnablePmSyntax, 0)) { # inject before skins and local.css
    $cs = is_array(@$CustomSyntax) ? 
      pm_json_encode(array_values($CustomSyntax), true) : '';
    array_unshift($HTMLHeaderFmt, "<script data-imap='{\$EnabledIMap}'
      src='\$FarmPubDirUrl/guiedit/pmwiki.syntax.js'
      data-label=\"$[Highlight]\" data-mode='$EnablePmSyntax'
      data-custom=\"$cs\"></script>");
  }
  if (IsEnabled($EnablePmSyntax, 0) || $ddmu) {
    array_unshift($HTMLHeaderFmt, "<link rel='stylesheet' 
      href='\$FarmPubDirUrl/guiedit/pmwiki.syntax.css'>");
  }
  
  // Dark theme toggle, needs to be very early
  $enabled = IsEnabled($EnableDarkThemeToggle, 0);
  if ($enabled && file_exists($dark)) {
    $config = array(
      'enable' => $enabled,
      'label'=> XL('Color theme: '),
      'modes'=> array( XL('Light'), XL('Dark'), XL('Auto'), ),
    );
    $json = pm_json_encode($config);
    array_unshift($HTMLHeaderFmt, "<script src='\$FarmPubDirUrl/pmwiki-darktoggle.js' 
      data-config='$json'></script>");
  }
}
PmUtilsJS($pagename);

