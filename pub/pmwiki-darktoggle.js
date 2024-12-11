/*
  Dark mode toggle for PmWiki
  (c) 2024 Petko Yotov www.pmwiki.org/petko
  licensed GNU GPLv2 or any more recent version released by the FSF.
*/

(function(__script__){
  try {
    var Config = JSON.parse(__script__.dataset.config);
  }
  catch(e) {
    var Config = {};
  }

  function aE(el, ev, fn) {
    if(typeof el == 'string') el = dqsa(el);
    for(var i=0; i<el.length; i++) el[i].addEventListener(ev, fn);
  }
  function dqsa(str) { return document.querySelectorAll(str); }
  function dce(tag)  { return document.createElement(tag); }
  function setStyles(el, obj) {
    for (var key in obj) {
      if (obj.hasOwnProperty(key)) {
        el.style[key] = obj[key];
      }
    }
  }
  var wLS = window.localStorage;

  function getLS(key, parse) {
    return wLS? wLS.getItem(key) : null;
  }
  function setLS(key, value) { 
    if(wLS) wLS.setItem(key, value);
  }

  function wmmd() {
    if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
      return 1;
    return 0;
  }

  function pref(enabled) {
    var x = getLS('pmDarkToggled');
    if(x === null) x = conftheme;
    else x = parseInt(x);
    if(enabled && x==2) return wmmd();
    return x;
  }

  function toggleSheets(enabled) {
    var themesheets = dqsa('link[rel="stylesheet"][data-theme]');
    for(var i=0; i<themesheets.length; i++) {
      var sheet = themesheets[i];
      var isDark = sheet.dataset.theme == 'dark'? 1:0;
      sheet.disabled = (isDark != enabled);
    }    
  }
  function toggleImages(enabled) {
    var darkpics = dqsa('img[data-darksrc]');
    for(var i=0; i<darkpics.length; i++) {
      var pic = darkpics[i];
      if(!pic.dataset.lightsrc) pic.dataset.lightsrc = pic.src;
      pic.src = enabled ? pic.dataset.darksrc : pic.dataset.lightsrc;
    }
  }

  var clist = document.documentElement.classList, 
    conftheme = Config.enable - 1, label = false, current = false;

  var prev_dark = pref(1)
  if(prev_dark) clist.add('pmDarkTheme');
  toggleSheets(prev_dark);

  function update(toggle) {
    var isToggled = pref(); // 0=light, 1=dark, 2=auto

    if(toggle) {
      isToggled = (isToggled+1)%3;
      setLS('pmDarkToggled', isToggled);
      if(current) current.textContent = Config.modes[isToggled];
    }

    var enabled = isToggled==2? wmmd() : isToggled;

    if(enabled == prev_dark) return;
    prev_dark = enabled+0;

    if(enabled) clist.add('pmDarkTheme');
    else clist.remove('pmDarkTheme');
    toggleSheets(enabled);
    toggleImages(enabled);
  }

  function initLabel() {
    label = dce('div');
    label.className = 'frame darkThemeLabel';
    setStyles(label, {
      display: 'none',
      zIndex: 1000,
      position: 'fixed'
    });
    document.body.appendChild(label);
    current = dce('mark');
    label.append(Config.label, current);
  }

  function over() {
    if(!label) initLabel();
    current.textContent = Config.modes[pref()];

    label.style.display = 'block';

    var lrect = label.getBoundingClientRect()
      rect = this.getBoundingClientRect(), 
      iw = window.innerWidth, ih = window.innerHeight, 
      lh = lrect.height, lw = lrect.width;

    var left = (rect.left < iw-lw)
      ? rect.left + 'px' 
      : (iw-lw-10) + 'px';
    label.style.left = left;

    var top = (rect.top < lh)
      ? (rect.bottom) + 'px'
      : (rect.top-lh) + 'px';
    label.style.top = top;
  }

  function out(e) {
    label.style.display = 'none';
  }
  aE([document], 'DOMContentLoaded', function(){
    toggleSheets(prev_dark);
    toggleImages(prev_dark);
    if(! wLS) return; // no localStorage
    
    aE('.pmToggleDarkTheme', 'mouseenter', over);
    aE('.pmToggleDarkTheme', 'mouseleave', out);
    aE('.pmToggleDarkTheme', 'click', update);
    setInterval(update, 1000);// sync other tabs
  });
  aE([window], 'beforeprint', function(){
    if(! prev_dark) return;
    toggleSheets(0);
    toggleImages(0);
    
  });
  aE([window], 'afterprint', function(){
    if(! prev_dark) return;
    toggleSheets(1);
    toggleImages(1);
  });


})(document.currentScript);
