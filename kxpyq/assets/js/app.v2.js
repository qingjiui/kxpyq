/* Pyq Theme - App.js */
(function(){
'use strict';

var pyq = window.pyq = {};

/* === Toast 提示 === */
pyq.toast = function(msg, duration){
  var existing = document.querySelector(".pyq-toast");
  if(existing) existing.remove();
  var el = document.createElement("div");
  el.className = "pyq-toast";
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(function(){el.remove()}, duration || 2000);
};
var currentPage = 1, loading = false, hasMore = true;
var baseUrl = window.location.origin + '/usr/themes/kxpyq/ajax.php';

/* === 预加载 === */
function hidePreloader(){
  var pre = document.getElementById('pyq-preloader');
  if(pre && !pre.classList.contains('hide')){
    pre.classList.add('hide');
    setTimeout(function(){pre.remove()},500);
  }
}
window.addEventListener('load', hidePreloader);
setTimeout(hidePreloader, 3000);

/* === 加载游客信息 === */
function getCookie(name){
  var v = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return v ? decodeURIComponent(v[2]) : '';
}
function loadGuestInfo(){
  var savedName = getCookie('pyq_guest_name');
  var savedEmail = getCookie('pyq_guest_email');
  if(!savedName) return;
  document.querySelectorAll('.pyq-guest-name').forEach(function(el){
    if(!el.value) el.value = savedName;
  });
  document.querySelectorAll('.pyq-guest-email').forEach(function(el){
    if(!el.value && savedEmail) el.value = savedEmail;
  });
}
window.addEventListener('load', loadGuestInfo);

/* === 顶部栏滚动变色 === */
var topbar = document.getElementById('pyq-topbar');
var backtop = document.getElementById('pyq-backtop');

/* === 无限滚动 === */
var feed = document.getElementById('pyq-feed');
var loadingEl = document.getElementById('pyq-loading');
var noMore = document.getElementById('pyq-no-more');

function loadMore(){
  if(loading || !hasMore) return;
  loading = true;
  var _loadingEl = document.getElementById('pyq-loading');
  var _noMore = document.getElementById('pyq-no-more');
  if(_loadingEl) _loadingEl.style.display = 'flex';
  currentPage++;
  
  fetch(baseUrl + '?act=load&page=' + currentPage + '&limit=10')
    .then(function(r){ return r.json(); })
    .then(function(data){
      loading = false;
      if(_loadingEl) _loadingEl.style.display = 'none';
      var posts = data.data || data.posts;
      if(!posts || posts.length === 0){
        hasMore = false;
        if(_noMore) _noMore.style.display = 'block';
        return;
      }
      var container = document.getElementById('pyq-feed');
      posts.forEach(function(p){
        container.insertBefore(createCard(p), _loadingEl);
      });
      if(typeof pyq.observeLazy === 'function') pyq.observeLazy();
      if(posts.length < 10){
        hasMore = false;
        if(_noMore) _noMore.style.display = 'block';
      }
    })
    .catch(function(){
      loading = false;
      if(_loadingEl) _loadingEl.style.display = 'none';
    });
}

pyq.renderLikes = function(likes){
  if(!likes || !likes.length) return '';
  var icon = '<div class="pyq-likes-icon"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>';
  var allGuest = likes.every(function(lk){ return !lk.name || lk.name === '访客'; });
  if(allGuest){
    return '<div class="pyq-likes">' + icon + '<div class="pyq-likes-list"><span>' + likes.length + '位访客点赞</span></div></div>';
  }
  var list = '';
  likes.forEach(function(lk){ list += '<span>' + esc(lk.name || '访客') + '</span>'; });
  return '<div class="pyq-likes">' + icon + '<div class="pyq-likes-list">' + list + '</div></div>';
};

function createCard(p){
  var div = document.createElement('div');
  div.className = 'pyq-card';
  div.setAttribute('data-cid', p.cid);
  
  var html = '<div class="pyq-card-left"><img src="'+esc(p.avatar)+'" alt=""></div>';
  html += '<div class="pyq-card-right">';
  html += '<div class="pyq-card-user"><span class="pyq-card-username">'+esc(username)+'</span>';
  if(p.is_top) html += '<span class="pyq-card-tag">置顶</span>';
  if(p.tag) html += '<span class="pyq-card-tag">'+esc(p.tag)+'</span>';
  html += '</div>';
  
  if(p.is_long){
    html += '<div class="pyq-card-text collapsed" id="text-'+p.cid+'">'+p.content+'</div>';
    html += '<span class="pyq-card-expand" onclick="pyq.toggleText(this,'+p.cid+')">展开全文</span>';
  } else {
    html += '<div class="pyq-card-text">'+p.content+'</div>';
  }
  
  if(p.images && p.images.length>0){
    var gc = '';
    if(p.images.length===1) gc='g1';
    else if(p.images.length===2||p.images.length===4) gc='g2 g4';
    html += '<div class="pyq-card-images '+gc+'">';
    p.images.forEach(function(img){
      var imgSrc = window.__PYQ_NO_GD__ ? img : (window.location.origin + '/usr/themes/kxpyq/img-proxy.php?url=' + encodeURIComponent(img) + '&w=800');
      html += '<div class="pyq-card-img-item"><img class="lazy" src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22%3E%3C/svg%3E" data-src="'+esc(imgSrc)+'" data-original="'+esc(img)+'" alt="" onclick="pyq.openLightbox(this)"></div>';
    });
    html += '</div>';
  }
  
  if(p.music && p.music.length>0){
    var mc = p.music_cover || '';
    html += '<div class="pyq-music-wrap" data-bg="'+esc(p.music_cover||'')+'"><div class="pyq-music-card" data-src="'+esc(p.music[0])+'" data-name="'+esc(p.music_name)+'" data-lrc="'+(p.music_lrc_b64||'')+'">';
    if(mc) html += '<div class="pyq-music-card-bg" style="background-image:url(\''+esc(mc)+'\')"></div>';
    html += '<div class="pyq-music-card-left">';
    if(mc) html += '<img src="'+esc(mc)+'" alt="">';
    else html += '<div style="width:100%;height:100%;background:var(--card2);display:flex;align-items:center;justify-content:center"><svg viewBox="0 0 24 24" width="28" height="28" fill="var(--text2)"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg></div>';
    html += '</div>';
    html += '<div class="pyq-music-card-right"><div class="pyq-music-card-info">';
    html += '<div class="pyq-music-card-title">'+esc(p.music_name||'未知歌曲')+'</div>';
    html += '<div class="pyq-music-card-artist">'+esc(p.music_artist||'未知歌手')+'</div>';
    html += '</div>';
    html += '<div class="pyq-music-card-play" onclick="pyq.playCardMusic(this)"><svg class="icon-play" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg><svg class="icon-pause" viewBox="0 0 24 24" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg></div>';
    html += '<div class="pyq-music-progress" onmousedown="pyq.seekMusic(event,this)" ontouchstart="pyq.seekMusic(event,this)"><div class="pyq-music-progress-bar"></div><div class="pyq-music-progress-dot"></div></div>';
    html += '</div>';
    html += '<div class="pyq-lrc-bar" onclick="pyq.toggleLrc(this)"><svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/></svg><span>歌词</span></div>';
    html += '<div class="pyq-music-lrc"><div class="pyq-lrc-bg" style="background-image:url('+esc(p.music_cover||'')+')"></div><div class="pyq-lrc-overlay"></div><div class="pyq-lrc-lines"></div></div>';
    html += '</div></div>';
  }
  
  if(p.link_url){
    html += '<a class="pyq-card-link" href="'+esc(p.link_url)+'" target="_blank" rel="noopener">';
    if(p.link_thumb) html += '<img class="pyq-card-link-thumb" src="'+esc(p.link_thumb)+'" alt="">';
    html += '<div class="pyq-card-link-info"><div class="pyq-card-link-title">'+esc(p.link_title||p.link_url)+'</div>';
    if(p.link_desc) html += '<div class="pyq-card-link-desc">'+esc(p.link_desc)+'</div>';
    html += '</div></a>';
  }
  
  if(p.video_url){
    html += '<div class="pyq-card-video"><video src="'+esc(p.video_url)+'" preload="metadata" playsinline controls></video><div class="play-btn" onclick="pyq.playVideo(this)"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div></div>';
  }
  
  if(p.location){
    html += '<div class="pyq-card-location"><svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg><a href="https://uri.amap.com/search?keyword='+encodeURIComponent(p.location)+'" target="_blank" rel="noopener" class="pyq-location-link">'+esc(p.location)+'</a></div>';
  }
  
  html += '<div class="pyq-card-actions"><span class="pyq-card-time">'+esc(p.date)+'</span>'
  html += '<div class="pyq-card-btns">';
  html += '<div class="pyq-reaction-wrap"><div class="pyq-reaction-picker" id="rpicker-'+p.cid+'"><span onclick="pyq.react('+p.cid+',\'👍\',this)">👍</span><span onclick="pyq.react('+p.cid+',\'❤️\',this)">❤️</span><span onclick="pyq.react('+p.cid+',\'😂\',this)">😂</span><span onclick="pyq.react('+p.cid+',\'😮\',this)">😮</span><span onclick="pyq.react('+p.cid+',\'😢\',this)">😢</span></div><button class="pyq-card-action-btn '+(p.liked?'liked':'')+'" onclick="pyq.toggleReaction(this,'+p.cid+')"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg><span>'+(p.like_count>0?p.like_count:'赞')+'</span></button></div>';
  html += '<button class="pyq-card-action-btn" onclick="pyq.toggleComment('+p.cid+')"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span>评论</span></button>';
  html += '<button class="pyq-card-action-btn" onclick="pyq.sharePost('+p.cid+')"><svg viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg><span>分享</span></button>';
  html += '<button class="pyq-card-more" onclick="pyq.toggleActionBtns(this)"><span></span><span></span><span></span></button>';
  html += '</div></div>';
  
  var hasSocial = (p.likes && p.likes.length>0) || (p.comments && p.comments.length>0);
  html += '<div class="pyq-social'+(hasSocial?' has-content':'')+'" id="social-'+p.cid+'">';
  if(p.likes && p.likes.length>0){
    html += pyq.renderLikes(p.likes);
  }
  html += '<div class="pyq-comment-box" id="cbox-'+p.cid+'">';
  html += '<div class="pyq-guest-fields" id="guest-'+p.cid+'"><input type="text" class="pyq-guest-name" placeholder="昵称（必填）" maxlength="20"><input type="email" class="pyq-guest-email" placeholder="邮箱（可选）" maxlength="50"></div>';
  html += '<div class="pyq-comment-input-wrap"><textarea class="pyq-comment-input" placeholder="写评论..." maxlength="500"></textarea>';
  html += '<button class="pyq-emoji-btn" onclick="pyq.toggleEmoji(this)"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M8 14s1.5 2 4 2 4-2 4-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="9.5" r="1" fill="currentColor"/><circle cx="15" cy="9.5" r="1" fill="currentColor"/></svg></button></div>';
  html += '<div class="pyq-emoji-panel" id="emoji-'+p.cid+'"></div>';
  html += '<button class="pyq-comment-send" onclick="pyq.sendComment(this,'+p.cid+')">发送</button></div>';
  html += '</div></div>';
  
  div.innerHTML = html;
  // 加载保存的游客信息
  setTimeout(loadGuestInfo, 100);
  return div;
}

function esc(s){
  if(!s) return '';
  var d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

// 统一滚动处理（topbar/backtop + 无限加载）
var scrollTimer = null;
var loadCooldown = false;
window.addEventListener('scroll', function(){
  var y = window.scrollY || document.documentElement.scrollTop;
  // topbar样式切换
  if(topbar){
    if(y>60) topbar.classList.add('scrolled');
    else topbar.classList.remove('scrolled');
  }
  // 返回顶部按钮
  if(backtop){
    if(y>300) backtop.classList.add('show');
    else backtop.classList.remove('show');
  }
  // 无限加载（防抖）
  if(scrollTimer) clearTimeout(scrollTimer);
  scrollTimer = setTimeout(function(){
    if(loading || !hasMore || loadCooldown) return;
    var scrollHeight = document.documentElement.scrollHeight;
    var clientHeight = window.innerHeight;
    if(y + clientHeight >= scrollHeight - 200){
      loadCooldown = true;
      loadMore();
      setTimeout(function(){ loadCooldown = false; }, 500);
    }
  }, 100);
}, {passive:true});

/* === 公告喇叭 === */
var bell = document.getElementById('pyq-bell');
var noticePopup = document.getElementById('pyq-notice-popup');
var noticeClose = document.getElementById('pyq-notice-close');
if(bell && noticePopup){
  bell.addEventListener('click', function(){
    noticePopup.classList.toggle('show');
  });
  noticeClose.addEventListener('click', function(){
    noticePopup.classList.remove('show');
  });
  noticePopup.querySelector('.pyq-notice-mask').addEventListener('click', function(){
    noticePopup.classList.remove('show');
  });
}

/* === 音乐浮控 === */
var musicPP = document.getElementById('pyq-music-pp');
pyq.musicToggle = function(){
  var el = document.getElementById('pyq-music-float');
  if(el) el.classList.toggle('open');
};
pyq.musicPP = function(){
  if(currentCardBtn && document.body.contains(currentCardBtn)){
    pyq.playCardMusic(currentCardBtn);
  } else if(currentCardAudio){
    if(currentCardAudio.paused){ currentCardAudio.play(); }
    else { currentCardAudio.pause(); }
    updateMusicFloat();
  }
};
pyq.musicLocate = function(){
  if(currentCardBtn && document.body.contains(currentCardBtn)){
    var wrap = currentCardBtn.closest('.pyq-music-wrap');
    if(wrap) wrap.scrollIntoView({behavior:'smooth',block:'center'});
  }
};

pyq.toggleText = function(el, cid){
  var t = document.getElementById('text-'+cid);
  if(t.classList.contains('collapsed')){
    t.classList.remove('collapsed');
    el.textContent = '收起';
  } else {
    t.classList.add('collapsed');
    el.textContent = '展开全文';
  }
};

/* === 点赞 === */
pyq.toggleLike = function(btn, cid){
  fetch(baseUrl + '?act=like', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'cid='+cid
  }).then(function(r){return r.json()}).then(function(data){
    if(data.code===0){
      if(data.liked){
        btn.classList.add('liked');
        var particles = document.createElement('div');
        particles.className = 'like-particles';
        particles.innerHTML = '<span></span><span></span><span></span><span></span><span></span><span></span>';
        btn.style.position = 'relative';
        btn.appendChild(particles);
        setTimeout(function(){particles.remove()}, 700);
      } else {
        btn.classList.remove('liked');
      }
      btn.querySelector('span').textContent = data.count>0 ? data.count : '赞';
      // 实时更新社交区域的点赞列表
      var social = document.getElementById('social-'+cid);
      if(social){
        var likesDiv = social.querySelector('.pyq-likes');
        if(data.likes && data.likes.length > 0){
          var likesHtml = pyq.renderLikes(data.likes);
          if(likesDiv){
            likesDiv.outerHTML = likesHtml;
          } else {
            social.insertAdjacentHTML('afterbegin', likesHtml);
          }
          social.classList.add('has-content');
        } else {
          if(likesDiv) likesDiv.remove();
          // 如果没有评论也不显示
          if(!social.querySelector('.pyq-comment-box.show')) social.classList.remove('has-content');
        }
      }
    }
  }).catch(function(err){
    console.error('点赞失败:', err);
  });
};

/* === 评论切换 === */
pyq.toggleComment = function(cid){
  var box = document.getElementById('cbox-'+cid);
  if(!box) return;
  // 确保父级social区域可见
  var social = document.getElementById('social-'+cid);
  if(social) social.classList.add('has-content');
  box.classList.toggle('show');
  // 聚焦输入框
  if(box.classList.contains('show')){
    var input = box.querySelector('.pyq-comment-input');
    if(input) setTimeout(function(){input.focus()},100);
  }
};

/* === 三点菜单 === */
pyq.toggleActionBtns = function(btn){
  var parent = btn.parentElement;
  parent.classList.toggle('show');
};

/* === 回复评论 === */
pyq.replyTo = function(cid, coid, name){
  var box = document.getElementById('cbox-'+cid);
  box.setAttribute('data-parent', coid);
  box.setAttribute('data-reply-name', name);
  
  // 添加回复提示
  var hint = box.querySelector('.pyq-reply-hint');
  if(!hint){
    hint = document.createElement('div');
    hint.className = 'pyq-reply-hint';
    box.insertBefore(hint, box.firstChild);
  }
  hint.innerHTML = '回复 <strong>'+esc(name)+'</strong> <span onclick="pyq.cancelReply('+cid+')">取消</span>';
  
  // 显示评论框并聚焦
  box.classList.add('show');
  box.querySelector('.pyq-comment-input').focus();
};

pyq.cancelReply = function(cid){
  var box = document.getElementById('cbox-'+cid);
  box.removeAttribute('data-parent');
  box.removeAttribute('data-reply-name');
  var hint = box.querySelector('.pyq-reply-hint');
  if(hint) hint.remove();
};

/* === 分享功能 === */
pyq.sharePost = function(cid, title, desc, img){
  var url = window.location.origin + '/archives/' + cid + '.html';
  title = title || '说说';
  desc = desc || '';
  
  var existing = document.getElementById('pyq-share-modal');
  if(existing){ existing.remove(); }
  
  var modal = document.createElement('div');
  modal.id = 'pyq-share-modal';
  modal.className = 'pyq-share-modal open';
  
  var encodedUrl = encodeURIComponent(url);
  var encodedTitle = encodeURIComponent(title);
  var encodedDesc = encodeURIComponent(desc);
  
  var html = '';
  html += '<div class="pyq-share-mask"></div>';
  html += '<div class="pyq-share-sheet">';
  html += '<div class="pyq-share-title">分享到</div>';
  html += '<div class="pyq-share-grid">';
  html += '<a class="pyq-share-item" onclick="pyq.shareToWeixin(\''+url+'\')">';
  html += '<div class="pyq-share-icon weixin"><svg viewBox="0 0 24 24"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-7.062-6.122zm-2.18 2.769c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.36 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982z"/></svg></div>';
  html += '<span>微信</span>';
  html += '</a>';
  html += '<a class="pyq-share-item" onclick="pyq.shareToQQ(\''+encodedUrl+'\',\''+encodedTitle+'\',\''+encodedDesc+'\')">';
  html += '<div class="pyq-share-icon qq"><svg viewBox="0 0 24 24"><path d="M12.003 2c-2.265 0-6.29 1.364-6.29 7.325v1.195S3.55 14.96 3.55 17.474c0 .665.17 1.025.281 1.025.114 0 .902-.484 1.748-2.072 0 0-.18 2.197 1.904 3.967 0 0-1.77.495-1.77 1.182 0 .686 4.078.43 6.29.43 2.239 0 6.29.256 6.29-.43 0-.687-1.77-1.182-1.77-1.182 2.085-1.77 1.905-3.967 1.905-3.967.845 1.588 1.634 2.072 1.746 2.072.111 0 .283-.36.283-1.025 0-2.514-2.166-6.954-2.166-6.954V9.325C18.29 3.364 14.268 2 12.003 2z"/></svg></div>';
  html += '<span>QQ</span>';
  html += '</a>';
  html += '<a class="pyq-share-item" onclick="pyq.shareToWeibo(\''+encodedUrl+'\',\''+encodedTitle+'\')">';
  html += '<div class="pyq-share-icon weibo"><svg viewBox="0 0 24 24"><path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.737 5.443zm-1.683-7.157c-2.882.291-4.857 2.049-4.408 3.924.449 1.875 3.078 3.046 5.96 2.756 2.88-.29 4.856-2.047 4.407-3.922-.45-1.877-3.077-3.048-5.959-2.758zm1.633 4.573c-.608.267-1.304.046-1.551-.494-.242-.537.074-1.168.683-1.421.615-.257 1.308-.032 1.548.503.24.541-.072 1.153-.68 1.412zm1.398-2.076c-.18.078-.394.022-.474-.137-.08-.16.009-.348.19-.42.184-.075.394-.016.473.141.079.16-.006.345-.189.416zM20.31 8.666c-.774-1.837-2.456-2.849-4.156-2.625l-.031.006c-.477.065-.793.488-.728.966.065.478.488.793.966.728l.024-.004c1.054-.137 2.079.477 2.564 1.618.477 1.123.201 2.394-.588 3.185l-.006.006c-.346.341-.346.894.002 1.239a.88.88 0 0 0 1.239-.002c1.271-1.274 1.716-3.243.677-4.121zm-3.168-.158c-.393-.052-.787.052-1.117.256a.878.878 0 0 0 .932 1.464c.122-.078.287-.113.434-.086.153.027.284.122.361.262.078.144.087.312.025.462a.877.877 0 0 0 1.464.93c.276-.427.373-.952.233-1.448a1.756 1.756 0 0 0-2.332-.84z"/></svg></div>';
  html += '<span>微博</span>';
  html += '</a>';
  html += '<a class="pyq-share-item" onclick="pyq.generatePoster('+cid+')">';
  html += '<div class="pyq-share-icon poster"><svg viewBox="0 0 24 24"><path d="M19 12V7H5v5H3V5c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2v7h-2zm-2 2H5c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-3c0-1.1-.9-2-2-2zm-1 4H6v-1h10v1z"/></svg></div>';
  html += '<span>海报</span>';
  html += '</a>';
  html += '<a class="pyq-share-item" onclick="pyq.copyWithTitle(\''+title+'\',\''+url+'\')">';
  html += '<div class="pyq-share-icon copy-title"><svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg></div>';
  html += '<span>带标题复制</span>';
  html += '</a>';
  html += '<a class="pyq-share-item" onclick="pyq.copyLinkOnly(\''+url+'\')">';
  html += '<div class="pyq-share-icon copy-link"><svg viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg></div>';
  html += '<span>复制链接</span>';
  html += '</a>';
  html += '<a class="pyq-share-item" onclick="pyq.showQR(\''+url+'\')">';
  html += '<div class="pyq-share-icon qrcode"><svg viewBox="0 0 24 24" width="24" height="24"><rect x="3" y="3" width="7" height="7" rx="1" fill="currentColor"/><rect x="4" y="4" width="5" height="5" rx="0.5" fill="#fff"/><rect x="14" y="3" width="7" height="7" rx="1" fill="currentColor"/><rect x="15" y="4" width="5" height="5" rx="0.5" fill="#fff"/><rect x="3" y="14" width="7" height="7" rx="1" fill="currentColor"/><rect x="4" y="15" width="5" height="5" rx="0.5" fill="#fff"/><rect x="14" y="14" width="4" height="4" rx="1" fill="currentColor"/><rect x="15" y="15" width="2" height="2" rx="0.5" fill="#fff"/></svg></div>';
  html += '<span>二维码</span>';
  html += '</a>';
  html += '<a class="pyq-share-item" onclick="pyq.closeShare()">';
  html += '<div class="pyq-share-icon cancel"><svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div>';
  html += '<span>取消</span>';
  html += '</a>';
  html += '</div>';
  html += '</div>';
  
  modal.innerHTML = html;
  document.body.appendChild(modal);
  
  modal.querySelector('.pyq-share-mask').addEventListener('click', pyq.closeShare);
};

pyq.closeShare = function(){
  var modal = document.getElementById('pyq-share-modal');
  if(modal){
    modal.classList.remove('open');
    setTimeout(function(){modal.remove()}, 300);
  }
};

pyq.showQR = function(url) {
  pyq.closeShare();
  if (typeof qrcode !== 'undefined') {
    pyq._showQRCanvas(url);
    return;
  }
  // 懒加载 QR 编码库
  var s = document.createElement('script');
  s.src = pyq._themeUrl() + '/assets/lib/qrcode/qrcode.min.js';
  s.onload = function() { pyq._showQRCanvas(url); };
  s.onerror = function() { pyq.toast('二维码加载失败'); };
  document.head.appendChild(s);
};

pyq._showQRCanvas = function(url) {
  var existing = document.getElementById('pyq-qr-modal');
  if (existing) { existing.remove(); }
  try {
    var qr = qrcode(0, 'M');
    qr.addData(url);
    qr.make();
    var size = 200, mc = qr.getModuleCount();
    var cs = Math.floor(size / mc), as = mc * cs, margin = Math.floor((size - as) / 2);
    var cvs = document.createElement('canvas');
    cvs.width = size; cvs.height = size;
    var ctx = cvs.getContext('2d');
    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, size, size);
    ctx.fillStyle = '#000';
    for (var r = 0; r < mc; r++) for (var cl = 0; cl < mc; cl++) {
      if (qr.isDark(r, cl)) ctx.fillRect(margin + cl * cs, margin + r * cs, cs, cs);
    }
    var dataUrl = cvs.toDataURL('image/png');
    var modal = document.createElement('div');
    modal.id = 'pyq-qr-modal';
    modal.className = 'pyq-share-modal open';
    modal.innerHTML = '<div class="pyq-share-mask" onclick="this.parentElement.remove()"></div><div class="pyq-qr-popup"><div class="pyq-qr-title">扫码访问</div><img src="'+dataUrl+'" alt="QR" style="width:200px;height:200px;border-radius:8px"><div class="pyq-qr-url">'+url+'</div><button class="pyq-qr-close" onclick="this.parentElement.parentElement.remove()">关闭</button></div>';
    document.body.appendChild(modal);
  } catch(e) {
    pyq.toast('二维码生成失败');
  }
};

pyq._themeUrl = function() {
  var p = document.querySelector('script[src*="app"]');
  return p ? p.src.replace(/\/assets\/js\/.*/, '') : '';
};

pyq.copyWithTitle = function(title, url){
  var text = title + '\n' + url;
  if(navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(text).then(function(){ pyq.toast('已复制标题+链接'); });
  } else {
    var ta = document.createElement('textarea'); ta.value = text; ta.style.cssText = 'position:fixed;left:-9999px';
    document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
    pyq.toast('已复制标题+链接');
  }
};
pyq.copyLinkOnly = function(url){
  if(navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(url).then(function(){ pyq.toast('已复制链接'); });
  } else {
    var ta = document.createElement('textarea'); ta.value = url; ta.style.cssText = 'position:fixed;left:-9999px';
    document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
    pyq.toast('已复制链接');
  }
};

pyq.shareToWeixin = function(url){
  var input = document.createElement('input');
  input.value = url;
  document.body.appendChild(input);
  input.select();
  document.execCommand('copy');
  document.body.removeChild(input);
  pyq.toast('链接已复制，请打开微信粘贴分享');
};

pyq.shareToQQ = function(url, title, desc){
  window.open('https://connect.qq.com/widget/shareqq/index.html?url='+url+'&title='+title+'&desc='+desc, '_blank');
};

pyq.shareToWeibo = function(url, title){
  window.open('https://service.weibo.com/share/share.php?url='+url+'&title='+title, '_blank');
};

pyq.generatePoster = function(cid){
  // 先关闭分享弹窗
  pyq.closeShare();
  
  // 显示加载提示
  var loading = document.createElement('div');
  loading.className = 'pyq-poster-loading';
  loading.innerHTML = '<div class="pyq-poster-loading-inner"><div class="pyq-poster-spinner"></div><p>正在生成海报...</p></div>';
  document.body.appendChild(loading);
  
  // 调用插件API
  fetch('/index.php/ArticlePoster/make?cid=' + cid)
    .then(function(res){ return res.json(); })
    .then(function(json){
      loading.remove();
      if(json.code === 200 && json.data){
        pyq.showPoster(json.data);
      } else{
        pyq.toast(json.msg || '海报生成失败');
      }
    })
    .catch(function(err){
      loading.remove();
      pyq.toast('海报生成失败：' + err.message);
    });
};

pyq.showPoster = function(imgUrl){
  var existing = document.getElementById('pyq-poster-modal');
  if(existing){ existing.remove(); }
  
  var modal = document.createElement('div');
  modal.id = 'pyq-poster-modal';
  modal.className = 'pyq-poster-modal open';
  modal.innerHTML = '<div class="pyq-poster-mask"></div>' +
    '<div class="pyq-poster-box">' +
    '<img class="pyq-poster-img" src="' + imgUrl + '">' +
    '<div class="pyq-poster-actions">' +
    '<a class="pyq-poster-download" href="' + imgUrl + '" download="poster.jpg">保存图片</a>' +
    '<button class="pyq-poster-close" onclick="pyq.closePoster()">关闭</button>' +
    '</div></div>';
  
  document.body.appendChild(modal);
  modal.querySelector('.pyq-poster-mask').addEventListener('click', pyq.closePoster);
};

pyq.closePoster = function(){
  var modal = document.getElementById('pyq-poster-modal');
  if(modal){
    modal.classList.remove('open');
    setTimeout(function(){modal.remove()}, 300);
  }
};
pyq.sendComment = function(btn, cid){
  var box = btn.closest('.pyq-comment-box');
  var input = box.querySelector('.pyq-comment-input');
  var text = input.value.trim();
  if(!text) return;
  
  // 获取游客信息
  var nameInput = box.querySelector('.pyq-guest-name');
  var emailInput = box.querySelector('.pyq-guest-email');
  var author = nameInput ? nameInput.value.trim() : '';
  var email = emailInput ? emailInput.value.trim() : '';
  
  if(!author){
    if(nameInput) nameInput.focus();
    return;
  }
  
  if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
    if(emailInput) emailInput.focus();
    alert('请填写邮箱');
    return;
  }
  
  // 保存到Cookie
  try{
    document.cookie = 'pyq_guest_name=' + encodeURIComponent(author) + ';max-age=' + (365*86400) + ';path=/';
    if(email) document.cookie = 'pyq_guest_email=' + encodeURIComponent(email) + ';max-age=' + (365*86400) + ';path=/';
  }catch(e){}
  
  // 获取父评论ID
  var parentId = box.getAttribute('data-parent') || 0;
  var replyName = box.getAttribute('data-reply-name') || '';
  
  btn.disabled = true;
  btn.textContent = '发送中...';
  
  var body = 'cid='+cid+'&text='+encodeURIComponent(text)+'&author='+encodeURIComponent(author);
  if(email) body += '&mail='+encodeURIComponent(email);
  if(parentId > 0) body += '&parent='+parentId;
  
  fetch(baseUrl + '?act=comment', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:body
  }).then(function(r){return r.json()}).then(function(data){
    btn.disabled = false;
    btn.textContent = '发送';
    if(data.code===0){
      input.value = '';
      // 清除回复状态
      box.removeAttribute('data-parent');
      box.removeAttribute('data-reply-name');
      var replyHint = box.querySelector('.pyq-reply-hint');
      if(replyHint) replyHint.remove();
      
      // 添加评论到列表
      var social = document.getElementById('social-'+cid);
      var commentBox = social.querySelector('.pyq-comments');
      if(!commentBox){
        commentBox = document.createElement('div');
        commentBox.className = 'pyq-comments';
        social.appendChild(commentBox);
      }
      var item = document.createElement('div');
      item.className = 'pyq-comment-item';
      var locationHtml = data.data.location ? '<span class="pyq-comment-location">'+esc(data.data.location)+'</span>' : '';
      var replyHtml = data.data.parent > 0 ? '<span class="pyq-comment-reply">回复</span><span class="pyq-comment-target">'+esc(replyName)+'</span>' : '';
      var ownerBadge = data.data.is_owner ? '<span class="pyq-comment-badge">博主</span>' : '';
      var replyBtn = '<button class="pyq-reply-btn" onclick="pyq.replyTo('+cid+','+data.data.coid+','+esc(JSON.stringify(data.data.author))+')">回复</button>';
      item.innerHTML = '<span class="pyq-comment-user">'+esc(data.data.author)+ownerBadge+'</span>' + replyHtml + '<span class="pyq-comment-text">：'+esc(data.data.text)+'</span>' + locationHtml + replyBtn;
      commentBox.appendChild(item);
      social.classList.add('has-content');
      
      // 邮件通知状态
      if(data.data.email_sent){
        console.log('评论通知邮件已发送');
      } else {
        console.log('评论通知邮件发送失败（不影响评论发布）');
      }
      
      // 关闭表情面板
      var emojiPanel = box.querySelector('.pyq-emoji-panel');
      if(emojiPanel) emojiPanel.classList.remove('show');
    } else {
      alert(data.msg || '评论失败');
    }
  }).catch(function(err){
    btn.disabled = false;
    btn.textContent = '发送';
    console.error('评论提交失败:', err);
    alert('评论提交失败，请重试');
  });
};

/* === 灯箱 === */
pyq._fancyboxLoading = false;
pyq._fancyboxQueue = null;
pyq._loadFancybox = function(cb) {
  if (typeof Fancybox !== 'undefined') { cb(); return; }
  if (this._fancyboxLoading) { this._fancyboxQueue = cb; return; }
  this._fancyboxLoading = true;
  var themeUrl = document.querySelector('script[data-themeurl]')?.dataset?.themeurl || '';
  if (!themeUrl) {
    var s = document.querySelector('script[src*="app"]');
    if (s) themeUrl = s.src.replace(/\/assets\/js\/.*/, '');
  }
  var link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = themeUrl + '/assets/lib/fancybox/fancybox.css';
  document.head.appendChild(link);
  var script = document.createElement('script');
  script.src = themeUrl + '/assets/lib/fancybox/fancybox.umd.min.js';
  script.onload = function() {
    pyq._fancyboxLoading = false;
    if (typeof Fancybox !== 'undefined') { Fancybox.bind('[data-fancybox]', {Thumbs: false}); }
    cb();
    if (pyq._fancyboxQueue) { pyq._fancyboxQueue(); pyq._fancyboxQueue = null; }
  };
  document.body.appendChild(script);
};
pyq.openLightbox = function(img){
  var items = [];
  var container = img.closest('.pyq-card-images');
  var imgs = container ? container.querySelectorAll('img') : [img];
  imgs.forEach(function(i){
    items.push({src:i.getAttribute('data-original')||i.src, type:'image'});
  });
  var idx = Array.from(imgs).indexOf(img);
  var show = function() {
    if (typeof Fancybox !== 'undefined') Fancybox.show(items, {startIndex:idx});
  };
  if (typeof Fancybox === 'undefined') pyq._loadFancybox(show);
  else show();
};

/* === 卡片内音乐播放 === */
var currentCardAudio = null, currentCardBtn = null;
/* === LRC歌词解析 === */
var lrcRe = /\[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?\](.*)/g;
pyq.parseLrc = function(text) {
  var lines = [], m;
  /* 网易云JSON格式 */
  if (text.charAt(0) === '{' || text.charAt(0) === '[') {
    try {
      var arr = JSON.parse(text);
      if (!Array.isArray(arr)) arr = [arr];
      arr.forEach(function(item) {
        if (item.t >= 0 && item.c) {
          var txt = item.c.map(function(c) { return c.tx || ''; }).join('');
          txt = txt.trim();
          if (txt) lines.push({ t: item.t, text: txt });
        }
      });
      return lines;
    } catch(e) {}
  }
  /* 标准LRC格式 */
  text.split('\n').forEach(function(line) {
    lrcRe.lastIndex = 0;
    while ((m = lrcRe.exec(line)) !== null) {
      var ms = parseInt(m[1]) * 60000 + parseInt(m[2]) * 1000 + parseInt((m[3] || '0').padEnd(3, '0'));
      var txt = m[4].trim();
      if (txt) lines.push({ t: ms, text: txt });
    }
  });
  lines.sort(function(a, b) { return a.t - b.t; });
  return lines;
};

var lrcAnimFrame = null;
pyq.startLrcSync = function(audio, lrcEl, parsed) {
  var lines = lrcEl.querySelectorAll('.lrc-line');
  var lastIdx = -1;
  function tick() {
    if (audio.paused) return;
    var ct = audio.currentTime * 1000;
    var idx = -1;
    for (var i = parsed.length - 1; i >= 0; i--) {
      if (ct >= parsed[i].t) { idx = i; break; }
    }
    if (idx !== lastIdx && idx >= 0) {
      lastIdx = idx;
      for (var j = 0; j < lines.length; j++) lines[j].classList.remove('active');
      lines[idx].classList.add('active');
      var target = lines[idx].offsetTop - lrcEl.clientHeight / 2 + lines[idx].offsetHeight / 2;
      lrcEl.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
    }
    lrcAnimFrame = requestAnimationFrame(tick);
  }
  tick();
};

pyq.toggleLrc = function(bar) {
  var wrap = bar.closest('.pyq-music-wrap');
  var lrcEl = wrap ? wrap.querySelector('.pyq-music-lrc') : null;
  if (!lrcEl) return;
  if (bar.classList.contains('open')) {
    lrcEl.style.height = '0';
    bar.classList.remove('open');
  } else {
    lrcEl.style.height = lrcEl.scrollHeight + 'px';
    bar.classList.add('open');
  }
};

pyq.loadLrc = function(card, audio) {
  var wrap = card.closest('.pyq-music-wrap');
  var lrcEl = wrap ? wrap.querySelector('.pyq-music-lrc') : null;
  if (!lrcEl) return;
  var raw = '';
  try { raw = atob(card.getAttribute('data-lrc') || ''); } catch(e) { raw = card.getAttribute('data-lrc') || ''; }
  if (!raw.trim()) return;
  /* 设置背景图 */
  var bg = wrap.getAttribute('data-bg') || '';
  if (bg) {
    var bgEl = lrcEl.querySelector('.pyq-lrc-bg');
    if (bgEl) bgEl.style.backgroundImage = 'url(' + bg + ')';
  }
  /* 显示歌词按钮 */
  var bar = wrap.querySelector('.pyq-lrc-bar');
  if (bar) bar.style.display = 'flex';

  function renderLrc(text) {
    var parsed = pyq.parseLrc(text);
    if (!parsed.length) return;
    lrcEl._parsed = parsed;
    var linesEl = lrcEl.querySelector('.pyq-lrc-lines');
    linesEl.innerHTML = parsed.map(function(l) {
      return '<div class="lrc-line" data-time="' + l.t + '">' + esc(l.text) + '</div>';
    }).join('');
    /* 自动展开 */
    lrcEl.style.height = lrcEl.scrollHeight + 'px';
    var bar = wrap.querySelector('.pyq-lrc-bar');
    if (bar) bar.classList.add('open');
    linesEl.scrollTop = 0;
    pyq.startLrcSync(audio, linesEl, parsed);
  }

  if (/^https?:\/\//i.test(raw.trim())) {
    fetch(raw.trim()).then(function(r) { return r.text(); }).then(renderLrc).catch(function() { lrcEl.classList.remove('show'); });
  } else {
    renderLrc(raw);
  }
};

/* 进度条拖动 seek */
pyq.seekMusic = function(e, el) {
  e.preventDefault();
  if (!currentCardAudio || !currentCardAudio.duration) return;
  var rect = el.getBoundingClientRect();
  function doSeek(clientX) {
    var pct = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
    currentCardAudio.currentTime = pct * currentCardAudio.duration;
    el.querySelector('.pyq-music-progress-bar').style.width = (pct * 100) + '%';
    var dot = el.querySelector('.pyq-music-progress-dot');
    if (dot) dot.style.left = (pct * 100) + '%';
  }
  doSeek(e.touches ? e.touches[0].clientX : e.clientX);
  el.classList.add('dragging');
  function onMove(ev) { doSeek(ev.touches ? ev.touches[0].clientX : ev.clientX); }
  function onUp() {
    el.classList.remove('dragging');
    document.removeEventListener('mousemove', onMove);
    document.removeEventListener('mouseup', onUp);
    document.removeEventListener('touchmove', onMove);
    document.removeEventListener('touchend', onUp);
  }
  document.addEventListener('mousemove', onMove);
  document.addEventListener('mouseup', onUp);
  document.addEventListener('touchmove', onMove, {passive:false});
  document.addEventListener('touchend', onUp);
};

function updateMusicFloat(){
  musicPP = musicPP || document.getElementById('pyq-music-pp');
  if(!musicPP) return;
  var isPlaying = currentCardAudio && !currentCardAudio.paused;
  if(isPlaying){
    musicPP.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor"/></svg>';
  } else {
    musicPP.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>';
  }
}

/* PJAX换页后同步音乐UI */
pyq.syncMusicUI = function(){
  try{
  if(!currentCardAudio) return;
  updateMusicFloat();
  /* 检查旧btn是否还在DOM里，还在就复用 */
  if(currentCardBtn && document.body.contains(currentCardBtn)){
    var card = currentCardBtn.closest('.pyq-music-card');
    if(card){
      currentCardBtn.classList.add('playing');
      currentCardBtn.querySelector('.icon-play').style.display='none';
      currentCardBtn.querySelector('.icon-pause').style.display='block';
      var progBar = card.querySelector('.pyq-music-progress-bar');
      var progDot = card.querySelector('.pyq-music-progress-dot');
      if(progBar && currentCardAudio.duration){
        var pct = (currentCardAudio.currentTime / currentCardAudio.duration) * 100;
        progBar.style.width = pct + '%';
        if(progDot) progDot.style.left = pct + '%';
      }
      pyq.loadLrc(card, currentCardAudio);
    }
    return;
  }
  /* btn不在DOM里（换页了），通过URL匹配找卡片 */
  var src = currentCardAudio.src;
  var cards = document.querySelectorAll('.pyq-music-card');
  for(var i = 0; i < cards.length; i++){
    var ds = cards[i].getAttribute('data-src');
    var a = document.createElement('a'); a.href = ds;
    if(a.href === src){
      var btn = cards[i].querySelector('.pyq-music-card-play');
      if(btn){
        currentCardBtn = btn;
        btn.classList.add('playing');
        btn.querySelector('.icon-play').style.display='none';
        btn.querySelector('.icon-pause').style.display='block';
      }
      var progBar = cards[i].querySelector('.pyq-music-progress-bar');
      var progDot = cards[i].querySelector('.pyq-music-progress-dot');
      if(progBar && currentCardAudio.duration){
        var pct = (currentCardAudio.currentTime / currentCardAudio.duration) * 100;
        progBar.style.width = pct + '%';
        if(progDot) progDot.style.left = pct + '%';
      }
      pyq.loadLrc(cards[i], currentCardAudio);
      return;
    }
  }
  }catch(e){ console.error('[syncMusicUI]',e); }
};

pyq.playCardMusic = function(btn){
  var card = btn.closest('.pyq-music-card');
  var src = card.getAttribute('data-src');
  
  if(currentCardAudio && currentCardBtn === btn){
    if(currentCardAudio.paused){
      currentCardAudio.play();
      btn.classList.add('playing');
      btn.querySelector('.icon-play').style.display='none';
      btn.querySelector('.icon-pause').style.display='block';
      updateMusicFloat();
      var wrap = card.closest('.pyq-music-wrap');
      var lrcEl = wrap ? wrap.querySelector('.pyq-music-lrc') : null;
      if (lrcEl && lrcEl._parsed) {
        var linesEl = lrcEl.querySelector('.pyq-lrc-lines');
        if (linesEl) pyq.startLrcSync(currentCardAudio, linesEl, lrcEl._parsed);
      }
    } else {
      currentCardAudio.pause();
      btn.classList.remove('playing');
      btn.querySelector('.icon-play').style.display='block';
      btn.querySelector('.icon-pause').style.display='none';
      updateMusicFloat();
      if (lrcAnimFrame) cancelAnimationFrame(lrcAnimFrame);
    }
    return;
  }
  
  if(currentCardAudio){
    currentCardAudio.pause();
    if (lrcAnimFrame) cancelAnimationFrame(lrcAnimFrame);
    if(currentCardBtn){
      currentCardBtn.classList.remove('playing');
      currentCardBtn.querySelector('.icon-play').style.display='block';
      currentCardBtn.querySelector('.icon-pause').style.display='none';
    }
    /* hide old lrc */
    var oldBar = document.querySelector('.pyq-lrc-bar.open');
    if (oldBar) oldBar.classList.remove('open');
    var oldLrc = document.querySelector('.pyq-music-lrc');
    if (oldLrc) oldLrc.style.height = '0';
  }
  
  currentCardAudio = new Audio(src);
  currentCardBtn = btn;
  currentCardAudio.play();
  btn.classList.add('playing');
  btn.querySelector('.icon-play').style.display='none';
  btn.querySelector('.icon-pause').style.display='block';
  updateMusicFloat();
  
  pyq.loadLrc(card, currentCardAudio);

  /* 进度条（通过currentCardBtn动态查找，PJAX安全） */
  function updateProg() {
    if (!currentCardAudio.duration || !currentCardBtn) return;
    var c = currentCardBtn.closest('.pyq-music-card');
    if (!c) return;
    var pct = (currentCardAudio.currentTime / currentCardAudio.duration) * 100;
    var bar = c.querySelector('.pyq-music-progress-bar');
    var dot = c.querySelector('.pyq-music-progress-dot');
    if (bar) bar.style.width = pct + '%';
    if (dot) dot.style.left = pct + '%';
  }
  currentCardAudio.addEventListener('timeupdate', updateProg);
  currentCardAudio.addEventListener('loadedmetadata', updateProg);

  currentCardAudio.onended = function(){
    var endBtn = currentCardBtn || btn;
    endBtn.classList.remove('playing');
    endBtn.querySelector('.icon-play').style.display='block';
    endBtn.querySelector('.icon-pause').style.display='none';
    updateMusicFloat();
    var c = endBtn.closest('.pyq-music-card');
    if (c) {
      var bar = c.querySelector('.pyq-music-progress-bar');
      var dot = c.querySelector('.pyq-music-progress-dot');
      if (bar) bar.style.width = '0';
      if (dot) dot.style.left = '0';
    }
    if (lrcAnimFrame) cancelAnimationFrame(lrcAnimFrame);
    var w = c ? c.closest('.pyq-music-wrap') : null;
    if (w) {
      var b = w.querySelector('.pyq-lrc-bar');
      if (b) b.classList.remove('open');
      var l = w.querySelector('.pyq-music-lrc');
      if (l) l.style.height = '0';
    }
  };
};

/* === 顶部播放器 === */
var bgm = document.getElementById('pyq-bgm');
var musicBtn = document.getElementById('pyq-music-btn');

if(bgm && musicBtn){
  musicBtn.addEventListener('click', function(){
    if(bgm.paused){
      bgm.play();
      musicBtn.querySelector('.icon-play').style.display='none';
      musicBtn.querySelector('.icon-pause').style.display='block';
    } else {
      bgm.pause();
      musicBtn.querySelector('.icon-play').style.display='block';
      musicBtn.querySelector('.icon-pause').style.display='none';
    }
  });
}

/* === 多功能菜单 === */
var menuBtn = document.getElementById('pyq-menu-btn');
var menuPopup = document.getElementById('pyq-menu-popup');
if(menuBtn && menuPopup){
  menuBtn.addEventListener('click', function(e){
    e.stopPropagation();
    menuPopup.classList.toggle('open');
  });
  document.addEventListener('click', function(){
    menuPopup.classList.remove('open');
  });
  menuPopup.addEventListener('click', function(e){
    if(e.target.closest('a')) return;
    e.stopPropagation();
  });
  menuPopup.querySelectorAll('a').forEach(function(link){
    link.addEventListener('click', function(){
      menuPopup.classList.remove('open');
    });
  });
}

/* === 深色模式 === */
var darkToggle = document.getElementById('pyq-dark-toggle');
if(darkToggle){
  function updateDarkIcon(){
    var isDark = document.documentElement.classList.contains('darkmode');
    darkToggle.querySelector('.icon-sun').style.display = isDark ? 'none' : 'block';
    darkToggle.querySelector('.icon-moon').style.display = isDark ? 'block' : 'none';
  }
  updateDarkIcon();
  darkToggle.addEventListener('click', function(){
    document.documentElement.classList.toggle('darkmode');
    var isDark = document.documentElement.classList.contains('darkmode');
    try{localStorage.setItem('pyq-scheme',isDark?'dark':'light')}catch(e){}
    updateDarkIcon();
  });
}

/* === 搜索功能 === */
var searchBtn = document.getElementById('pyq-search-btn');
var searchOverlay = document.getElementById('pyq-search-overlay');
var searchInput = document.getElementById('pyq-search-input');
var searchClose = document.getElementById('pyq-search-close');
var searchResults = document.getElementById('pyq-search-results');

if(searchBtn && searchOverlay){
  searchBtn.addEventListener('click', function(){
    searchOverlay.classList.add('open');
    setTimeout(function(){searchInput.focus()},100);
  });
  searchClose.addEventListener('click', function(){
    searchOverlay.classList.remove('open');
    searchInput.value = '';
    searchResults.innerHTML = '';
  });
  searchOverlay.addEventListener('click', function(e){
    if(e.target === searchOverlay){
      searchOverlay.classList.remove('open');
      searchInput.value = '';
      searchResults.innerHTML = '';
    }
  });
  
  var searchTimer;
  searchInput.addEventListener('input', function(){
    clearTimeout(searchTimer);
    var q = this.value.trim();
    if(q.length < 2){searchResults.innerHTML='';return;}
    searchTimer = setTimeout(function(){
      searchResults.innerHTML = '<div class="pyq-search-loading">搜索中...</div>';
      fetch(baseUrl + '?act=search&q=' + encodeURIComponent(q))
        .then(function(r){return r.json()})
        .then(function(data){
          if(data.length===0){
            searchResults.innerHTML = '<div class="pyq-no-more">没有找到相关内容</div>';
            return;
          }
          var html = '';
          data.forEach(function(p){
            html += '<a class="pyq-search-item" href="'+esc(p.permalink)+'">';
            html += '<div class="pyq-search-title">'+esc(p.title)+'</div>';
            html += '<div class="pyq-search-excerpt">'+esc(p.excerpt)+'</div>';
            html += '</a>';
          });
          searchResults.innerHTML = html;
        })
        .catch(function(){
          searchResults.innerHTML = '<div class="pyq-no-more">搜索失败</div>';
        });
    }, 300);
  });
}

/* === 表情面板 === */
var emojiData = ['😀','😁','😂','🤣','😃','😄','😅','😆','😉','😊','😋','😎','😍','😘','🥰','😗','😙','🥲','😚','☺️','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🫡','🤐','🤨','😐','😑','😶','🫥','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🥵','🥶','🥴','😵','🤯','🤠','🥳','🥸','😎','🤓','🧐','😕','🫤','😟','🙁','☹️','😮','😯','😲','😳','🥺','🥹','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','👍','👎','👏','🙌','🤝','❤️','🔥','💯','⭐','🎉','🎊','🎈','💐','🌹','🌺','🌻','🍀','🌈','☀️','🌙','💫','✨'];

pyq.toggleEmoji = function(btn){
  var box = btn.closest('.pyq-comment-box');
  var panel = box.querySelector('.pyq-emoji-panel');
  if(panel.children.length === 0){
    emojiData.forEach(function(e){
      var span = document.createElement('span');
      span.textContent = e;
      span.onclick = function(){
        var input = box.querySelector('.pyq-comment-input');
        input.value += e;
        input.focus();
      };
      panel.appendChild(span);
    });
  }
  panel.classList.toggle('show');
};

document.addEventListener('click', function(e){
  if(!e.target.closest('.pyq-emoji-btn') && !e.target.closest('.pyq-emoji-panel')){
    document.querySelectorAll('.pyq-emoji-panel.show').forEach(function(p){
      p.classList.remove('show');
    });
  }
});


/* === 图片懒加载 === */
(function(){
  var lazyObserver = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting){
        var img = entry.target;
        if(img.dataset.src){
          img.src = img.dataset.src;
          img.onload = function(){img.classList.remove('lazy');img.classList.add('loaded');};
          img.onerror = function(){img.classList.remove('lazy');};
          lazyObserver.unobserve(img);
        }
      }
    });
  }, {rootMargin:'200px'});
  
  function observeLazy(){
    document.querySelectorAll('img.lazy').forEach(function(img){
      lazyObserver.observe(img);
    });
  }
  
  // 初始观察
  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded', observeLazy);
  } else {
    observeLazy();
  }
  
  // 暴露给全局，供AJAX加载后调用
  pyq.observeLazy = observeLazy;

/* === 账号弹窗（关于页面） === */
pyq.showAccount = function(name, value) {
  var modal = document.getElementById('pyq-account-modal');
  if (!modal) return;
  document.getElementById('pyq-account-title').textContent = name;
  document.getElementById('pyq-account-value').textContent = value;
  var btn = modal.querySelector('.pyq-account-copy');
  if (btn) { btn.textContent = '复制'; btn.classList.remove('copied'); }
  modal.classList.add('open');
};
pyq.closeAccount = function() {
  var modal = document.getElementById('pyq-account-modal');
  if (modal) modal.classList.remove('open');
};
pyq.copyAccount = function() {
  var text = document.getElementById('pyq-account-value').textContent;
  var btn = document.querySelector('.pyq-account-copy');
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(function() {
      btn.textContent = '已复制'; btn.classList.add('copied');
      setTimeout(function(){ btn.textContent = '复制'; btn.classList.remove('copied'); }, 2000);
    });
  } else {
    var ta = document.createElement('textarea');
    ta.value = text; ta.style.cssText = 'position:fixed;left:-9999px';
    document.body.appendChild(ta); ta.select(); document.execCommand('copy');
    document.body.removeChild(ta);
    btn.textContent = '已复制'; btn.classList.add('copied');
    setTimeout(function(){ btn.textContent = '复制'; btn.classList.remove('copied'); }, 2000);
  }
};

})();

/* === 视频支持 === */
pyq.playVideo = function(el){
  var video = el.previousElementSibling;
  video.play();
  el.style.display = 'none';
};
pyq.pauseVideo = function(el){
  var video = el;
  if(!video.paused){
    video.pause();
  }
};

/* === 话题标签 === */
pyq.searchHashtag = function(tag){
  var searchInput = document.querySelector('.pyq-search-input');
  if(searchInput){
    searchInput.value = tag;
    pyq.doSearch();
  }
  // 打开搜索面板
  var overlay = document.getElementById('pyq-search');
  if(overlay) overlay.classList.add('open');
};

/* === 下拉刷新 === */
(function(){
  var startY = 0, pulling = false;
  var refreshEl = document.createElement('div');
  refreshEl.className = 'pyq-pull-refresh';
  refreshEl.innerHTML = '<div class="spinner"></div>';
  document.body.appendChild(refreshEl);
  
  document.addEventListener('touchstart', function(e){
    if(window.scrollY === 0){
      startY = e.touches[0].pageY;
      pulling = true;
    }
  }, {passive:true});
  
  document.addEventListener('touchmove', function(e){
    if(!pulling) return;
    var diff = e.touches[0].pageY - startY;
    if(diff > 60 && window.scrollY === 0){
      refreshEl.classList.add('active');
    }
  }, {passive:true});
  
  document.addEventListener('touchend', function(){
    if(refreshEl.classList.contains('active')){
      pulling = false;
      setTimeout(function(){
        location.reload();
      }, 300);
    } else {
      pulling = false;
    }
  }, {passive:true});
})();

/* === 话题标签解析 === */
/* === 展开全部评论 === */
pyq.showAllComments = function(btn, cid) {
  var container = document.getElementById('comments-' + cid);
  if (!container) return;
  var hidden = container.querySelectorAll('.pyq-comment-hidden');
  hidden.forEach(function(el){ el.classList.remove('pyq-comment-hidden'); });
  btn.style.display = 'none';
};

/* === Emoji反应 === */
pyq.toggleReaction = function(btn, cid) {
  var picker = document.getElementById('rpicker-' + cid);
  if (!picker) return;
  if (picker.classList.contains('show')) {
    picker.classList.remove('show');
  } else {
    picker.classList.add('show');
    // 自动关闭
    setTimeout(function(){ picker.classList.remove('show'); }, 3000);
  }
};
pyq.react = function(cid, emoji, el) {
  var picker = document.getElementById('rpicker-' + cid);
  if (picker) picker.classList.remove('show');
  pyq.toggleLike(el.closest('.pyq-reaction-wrap').querySelector('.pyq-card-action-btn'), cid);
};

pyq.parseHashtags = function(text) {
    return text.replace(/#([^#]+)#/g, '<span class="pyq-hashtag" onclick="pyq.searchHashtag(\'$1\')">#$1#</span>');
};

/* === 分享到朋友圈（生成卡片图） === */
pyq.shareToMoments = function(cid, title, desc, img) {
    // 关闭分享弹窗
    pyq.closeShare();
    
    // 显示加载
    pyq.toast('正在生成分享卡片...');
    
    // 调用海报API生成图片
    fetch('/index.php/ArticlePoster/make?cid=' + cid)
        .then(function(res) { return res.json(); })
        .then(function(json) {
            if (json.code === 200 && json.data) {
                // 显示保存提示
                pyq.showSaveCard(json.data);
            } else {
                pyq.toast('生成失败，请重试');
            }
        })
        .catch(function() {
            pyq.toast('生成失败，请重试');
        });
};

pyq.showSaveCard = function(imgUrl) {
    var existing = document.getElementById('pyq-save-card');
    if (existing) existing.remove();
    
    var modal = document.createElement('div');
    modal.id = 'pyq-save-card';
    modal.className = 'pyq-poster-modal open';
    modal.innerHTML = '<div class="pyq-poster-mask" onclick="pyq.closeSaveCard()"></div>' +
        '<div class="pyq-poster-box">' +
        '<div style="text-align:center;margin-bottom:12px;font-size:14px;color:var(--text2)">长按图片保存，然后发朋友圈</div>' +
        '<img class="pyq-poster-img" src="' + imgUrl + '" style="max-height:50vh">' +
        '<div class="pyq-poster-actions">' +
        '<a class="pyq-poster-download" href="' + imgUrl + '" download="share.jpg">保存图片</a>' +
        '<button class="pyq-poster-close" onclick="pyq.closeSaveCard()">关闭</button>' +
        '</div></div>';
    
    document.body.appendChild(modal);
};

pyq.closeSaveCard = function() {
    var modal = document.getElementById('pyq-save-card');
    if (modal) {
        modal.classList.remove('open');
        setTimeout(function() { modal.remove(); }, 300);
    }
};

/* === PJAX 无刷新换页 === */
(function(){
  var wrap = document.getElementById('pyq-feed');
  if (!wrap) return;

  function extractContent(html) {
    var parser = new DOMParser();
    var doc = parser.parseFromString(html, 'text/html');
    var newWrap = doc.getElementById('pyq-feed');
    if (!newWrap) return null;
    return { title: doc.title, wrapHTML: newWrap.innerHTML, bodyClass: doc.body.className };
  }

  function reinit() {
    wrap = document.getElementById('pyq-feed');
    if (typeof Fancybox !== 'undefined') { Fancybox.unbind(); Fancybox.bind('[data-fancybox]', {Thumbs: false}); }
    if (typeof Prism !== 'undefined') { Prism.highlightAll(); }
    if (typeof pyq.observeLazy === 'function') pyq.observeLazy();
    loadingEl = document.getElementById('pyq-loading');
    noMore = document.getElementById('pyq-no-more');
    currentPage = 0; loading = false; hasMore = true;
    var loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) loadMoreBtn.onclick = function(){ pyq.loadMore(); };
    var searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.onkeydown = function(e){ if(e.key==='Enter') pyq.doSearch(); };
    if (typeof loadGuestInfo === 'function') loadGuestInfo();
    if (typeof pyq.syncMusicUI === 'function') pyq.syncMusicUI();
    window.scrollTo(0, 0);
  }

  function pjaxLoad(url, pushState) {
    var preloader = document.getElementById('pyq-preloader');
    if (preloader) preloader.style.display = 'flex';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-PJAX', 'true');
    xhr.timeout = 10000;
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 300) {
        var data = extractContent(xhr.responseText);
        if (data) {
          wrap.innerHTML = data.wrapHTML;
          document.title = data.title;
          if (data.bodyClass) document.body.className = data.bodyClass;
          if (pushState) history.pushState({url: url}, '', url);
          reinit();
        } else { window.location.href = url; }
      } else { window.location.href = url; }
      if (preloader) preloader.style.display = 'none';
    };
    xhr.onerror = function() { window.location.href = url; };
    xhr.ontimeout = function() { window.location.href = url; };
    xhr.send();
  }

  document.addEventListener('click', function(e) {
    var link = e.target.closest('a');
    if (!link) return;
    var href = link.getAttribute('href');
    if (!href || href === '#') return;
    if (link.target === '_blank') return;
    if (link.hasAttribute('download')) return;
    if (href.charAt(0) === '#') return;
    if (href.indexOf('/admin/') !== -1) return;
    if (link.hostname && link.hostname !== window.location.hostname) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey) return;
    if (link.hasAttribute('data-no-pjax')) return;
    e.preventDefault();
    var url = link.href;
    if (url !== window.location.href) pjaxLoad(url, true);
  });

  window.addEventListener('popstate', function(e) {
    var url = e.state && e.state.url ? e.state.url : window.location.href;
    pjaxLoad(url, false);
  });

  history.replaceState({url: window.location.href}, '', window.location.href);
})();

/* === 代码复制 === */
pyq.copyCode = function(btn){
  var codeBlock = btn.closest('.pyq-code-block');
  var code = codeBlock.querySelector('code');
  var text = code.textContent;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(function(){
      btn.classList.add('copied');
      btn.querySelector('span').textContent = '已复制';
      setTimeout(function(){
        btn.classList.remove('copied');
        btn.querySelector('span').textContent = '复制';
      }, 2000);
    });
  } else {
    var textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    btn.classList.add('copied');
    btn.querySelector('span').textContent = '已复制';
    setTimeout(function(){
      btn.classList.remove('copied');
      btn.querySelector('span').textContent = '复制';
    }, 2000);
  }
};

/* === 阅读进度条 === */
(function(){
  var bar = document.getElementById('pyq-progress');
  if (!bar) return;
  window.addEventListener('scroll', function(){
    var h = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    if (h <= 0) return;
    var pct = Math.min(100, Math.round(window.scrollY / h * 100));
    bar.style.setProperty('--progress', pct + '%');
  });
})();

/* === 字体大小调节 === */
(function(){
  var sizes = ['14px','16px','18px'];
  var idx = 1;
  try { idx = parseInt(localStorage.getItem('kxpyq-font-size') || '1'); } catch(e){}
  document.documentElement.style.setProperty('--font-size', sizes[idx]);
  var btn = document.createElement('button');
  btn.className = 'pyq-font-btn';
  btn.title = '字体大小';
  btn.textContent = 'Aa';
  btn.onclick = function(){
    idx = (idx + 1) % sizes.length;
    document.documentElement.style.setProperty('--font-size', sizes[idx]);
    try { localStorage.setItem('kxpyq-font-size', String(idx)); } catch(e){}
  };
  document.body.appendChild(btn);
})();

/* === 暗色模式定时切换 === */
(function(){
  try { var mode = localStorage.getItem('pyq-scheme'); if (mode && mode !== 'auto') return; } catch(e){}
  var h = new Date().getHours();
  if (h >= 19 || h < 7) document.documentElement.classList.add('darkmode');
  else document.documentElement.classList.remove('darkmode');
})();

})();
