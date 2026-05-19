<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$adminUser  = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
$csrfToken  = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Rosali Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>
<!-- Admin uses fixed dark theme. shared.jsx is loaded only so its THEME_CSS block can be probed by the Colors tab. -->
<script>window.ROSALI = { theme:'rosa', lang:'en', images:{}, content:{}, layout:{}, colors:{}, pageVisibility:{}, splatEnabled:false };</script>
<script type="text/babel" src="../shared.jsx"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'DM Sans',sans-serif}
body{background:oklch(14% 0.018 250);color:oklch(92% 0.010 240);min-height:100vh}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:oklch(18% 0.018 250)}
::-webkit-scrollbar-thumb{background:oklch(42% 0.18 22);border-radius:3px}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:'DM Sans',sans-serif}
input,textarea,select{font-family:'DM Sans',sans-serif}
input[type=color]{padding:0;border:none;cursor:pointer;border-radius:4px;overflow:hidden}
</style>
</head>
<body>
<div id="root"></div>
<script type="text/babel">
const { useState, useEffect, useRef } = React;

const ADMIN_USER  = <?= json_encode($adminUser) ?>;
const CSRF_TOKEN  = <?= json_encode($csrfToken) ?>;

const T = {
  bg:'oklch(14% 0.018 250)', bg2:'oklch(18% 0.020 250)', bg3:'oklch(22% 0.022 250)',
  border:'oklch(30% 0.022 250)', fg:'oklch(92% 0.010 240)', muted:'oklch(58% 0.015 240)',
  accent:'oklch(62% 0.18 22)', accentDark:'oklch(42% 0.18 22)',
  green:'oklch(62% 0.16 148)', yellow:'oklch(78% 0.15 84)', red:'oklch(60% 0.20 25)',
};

/* ── API helpers ── */
async function apiGet(key) {
  try {
    const r = await fetch(`api/data.php?key=${encodeURIComponent(key)}`);
    const d = await r.json();
    return d.value || null;
  } catch { return null; }
}
async function apiSet(key, value) {
  try {
    await fetch('api/data.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({key, value}),
    });
  } catch {}
}

/* ── Static data ── */
const DEFAULT_PAGES = [
  {id:'home',    label:'Home',           file:'../index.php',   order:1},
  {id:'rooms',   label:'Rooms',          file:'../rooms.php',   order:2},
  {id:'events',  label:'Events',         file:'../events.php',  order:3},
  {id:'cafe',    label:'Rosa De 5 Café', file:'../cafe.php',    order:4},
  {id:'gallery', label:'Gallery',        file:'../gallery.php', order:5},
  {id:'tourism', label:'Tourism',        file:'../tourism.php', order:6},
  {id:'contact', label:'Contact',        file:'../contact.php', order:7},
];

const MEDIA_CATEGORIES = [
  {id:'general',   label:'General'},
  {id:'rooms',     label:'Rooms'},
  {id:'gallery',   label:'Gallery'},
  {id:'events',    label:'Events'},
  {id:'cafe',      label:'Café'},
  {id:'room_tour', label:'Room Tour (3D)'},
];

const THEME_VARS = [
  {key:'--bg',        label:'Background',       group:'Backgrounds'},
  {key:'--bg2',       label:'Background Alt',   group:'Backgrounds'},
  {key:'--fg',        label:'Text',             group:'Text'},
  {key:'--fg-muted',  label:'Muted Text',       group:'Text'},
  {key:'--accent',    label:'Accent',           group:'Accents'},
  {key:'--accent-lt', label:'Accent Light',     group:'Accents'},
  {key:'--border',    label:'Border',           group:'Other'},
  {key:'--nav-bg',    label:'Nav / Footer BG',  group:'Other'},
  {key:'--nav-fg',    label:'Nav / Footer Text',group:'Other'},
];

const THEMES       = ['garden','boutique','javanese','rosa','coastal','batik'];
const THEME_LABELS = {garden:'🌿 Garden',boutique:'🏛 Boutique',javanese:'✨ Javanese',rosa:'🌹 Rosa',coastal:'🌊 Coastal',batik:'🔷 Batik'};

const CONTENT_FIELDS = [
  {id:'general', label:'General', fields:[
    {key:'hotel_name',    label:'Hotel Name',         hint:'Shown in nav & page title'},
    {key:'wa_number',     label:'WhatsApp Number',    hint:'e.g. 6287851515500 — no + or spaces'},
    {key:'phone',         label:'Phone',              hint:'Front-desk reception number'},
    {key:'email',         label:'Email'},
    {key:'address',       label:'Address',            multi:true},
    {key:'instagram',     label:'Instagram Handle',   hint:'Without @'},
    {key:'facebook_url',  label:'Facebook URL'},
    {key:'cafe_hours_en', label:'Café Hours (EN)',    hint:'e.g. Sun–Thu 09:00–23:00 · Fri–Sat 09:00–24:00'},
    {key:'cafe_hours_id', label:'Café Hours (ID)'},
  ]},
  {id:'home', label:'Home', fields:[
    {key:'hero_title_en', label:'Hero Title (EN)',    multi:true, hint:'Use \\n for line breaks'},
    {key:'hero_title_id', label:'Hero Title (ID)',    multi:true},
    {key:'hero_sub_en',   label:'Hero Subtitle (EN)', multi:true},
    {key:'hero_sub_id',   label:'Hero Subtitle (ID)', multi:true},
    {key:'about_title_en',label:'About Title (EN)'},
    {key:'about_title_id',label:'About Title (ID)'},
    {key:'about_body_en', label:'About Body (EN)',    multi:true},
    {key:'about_body_id', label:'About Body (ID)',    multi:true},
  ]},
];

const LAYOUT_SECTIONS = [
  {key:'home_about_flip',  label:'Home — About Section',  options:['Image Right (default)','Image Left'], values:['normal','flip']},
  {key:'home_cafe_flip',   label:'Home — Café Section',   options:['Text Left (default)','Text Right'],   values:['normal','flip']},
  {key:'home_events_flip', label:'Home — Events Section', options:['Image Left (default)','Image Right'], values:['normal','flip']},
  {key:'home_rooms_cols',  label:'Home — Rooms Grid',     options:['3 Columns (default)','2 Columns'],    values:['3','2']},
];

const IS = {
  width:'100%', background:'oklch(12% 0.015 250)', border:'1px solid oklch(32% 0.022 250)',
  borderRadius:5, padding:'9px 12px', color:T.fg, fontSize:13, outline:'none',
};

/* ── Shared components ── */
function Btn({children, onClick, small, secondary, danger, disabled, type}) {
  const bg     = danger ? T.red : secondary ? 'transparent' : T.accent;
  const border = (secondary || danger) ? `1px solid ${danger ? T.red : T.border}` : 'none';
  const color  = secondary ? T.muted : 'white';
  return (
    <button onClick={onClick} disabled={disabled} type={type||'button'} style={{
      background:bg, border, color,
      padding: small ? '5px 10px' : '9px 16px',
      borderRadius:5, fontSize: small ? 11 : 13, fontWeight:500,
      whiteSpace:'nowrap', opacity: disabled ? .45 : 1, transition:'opacity .15s',
    }}
    onMouseEnter={e => !disabled && (e.currentTarget.style.opacity = '.8')}
    onMouseLeave={e => !disabled && (e.currentTarget.style.opacity = '1')}
    >{children}</button>
  );
}

function SaveBtn({onSave, saved}) {
  return (
    <button onClick={onSave} style={{
      background: saved ? T.green : T.accent, border:'none', color:'white',
      padding:'9px 20px', borderRadius:6, fontSize:13, fontWeight:600,
      transition:'background .3s', cursor:'pointer', flexShrink:0,
    }}>{saved ? '✓ Saved!' : 'Save'}</button>
  );
}

/* ── Sidebar ── */
function Sidebar({tab, setTab, pageCount}) {
  const items = [
    {id:'overview', icon:'◈', label:'Overview'},
    {id:'pages',    icon:'⊞', label:'Pages', badge: pageCount},
    {id:'media',    icon:'⬤', label:'Media'},
    {id:'colors',   icon:'◉', label:'Colors'},
    {id:'content',  icon:'≡', label:'Content'},
    {id:'layout',   icon:'⊟', label:'Layout'},
    {id:'settings', icon:'⚙', label:'Settings'},
  ];
  return (
    <aside style={{
      width:210, flexShrink:0, background:T.bg2, borderRight:`1px solid ${T.border}`,
      display:'flex', flexDirection:'column', minHeight:'100vh', position:'sticky', top:0,
    }}>
      <div style={{padding:'20px 18px 16px', borderBottom:`1px solid ${T.border}`}}>
        <div style={{display:'flex', alignItems:'center', gap:10}}>
          <div style={{width:30, height:30, borderRadius:6, background:T.accentDark,
            display:'flex', alignItems:'center', justifyContent:'center',
            fontSize:13, color:'white', fontWeight:700}}>R</div>
          <div>
            <div style={{fontSize:13, fontWeight:600, color:T.fg}}>Rosali Hotel</div>
            <div style={{fontSize:10, color:T.muted}}>Admin Panel</div>
          </div>
        </div>
      </div>

      <nav style={{flex:1, padding:'10px 8px', display:'flex', flexDirection:'column', gap:1}}>
        {items.map(it => (
          <button key={it.id} onClick={() => setTab(it.id)} style={{
            display:'flex', alignItems:'center', gap:9, padding:'9px 11px',
            borderRadius:6, border:'none', cursor:'pointer',
            background: tab === it.id ? T.bg3 : 'transparent',
            color:      tab === it.id ? T.fg  : T.muted,
            textAlign:'left', fontSize:13, fontWeight: tab === it.id ? 500 : 400,
            transition:'all .15s', width:'100%',
          }}>
            <span style={{fontSize:13, opacity:.75}}>{it.icon}</span>
            <span style={{flex:1}}>{it.label}</span>
            {it.badge != null && (
              <span style={{background:T.border, borderRadius:10, padding:'1px 7px',
                fontSize:10, color:T.muted}}>{it.badge}</span>
            )}
          </button>
        ))}
      </nav>

      <div style={{padding:'14px 18px', borderTop:`1px solid ${T.border}`,
        display:'flex', flexDirection:'column', gap:6}}>
        <a href="../index.php" target="_blank" style={{fontSize:11, color:T.muted,
          display:'flex', alignItems:'center', gap:6, transition:'color .15s'}}
          onMouseEnter={e => e.currentTarget.style.color = T.fg}
          onMouseLeave={e => e.currentTarget.style.color = T.muted}
        >← View Website</a>
        <a href="logout.php" style={{fontSize:11, color:T.muted, transition:'color .15s'}}
          onMouseEnter={e => e.currentTarget.style.color = T.red}
          onMouseLeave={e => e.currentTarget.style.color = T.muted}
        >Sign Out</a>
      </div>
    </aside>
  );
}

/* ── Overview ── */
function TabOverview({pages, visibility}) {
  const [stats, setStats] = useState(null);
  useEffect(() => {
    fetch('api/overview.php').then(r => r.json()).then(setStats).catch(() => {});
  }, []);

  const visibleCount = pages.filter(p => visibility[p.id] !== false).length;
  const hiddenCount  = pages.length - visibleCount;

  const cards = [
    {label:'Total Pages',     value: pages.length,        color:T.accent},
    {label:'Visible in Nav',  value: visibleCount,        color:T.green},
    {label:'Hidden Pages',    value: hiddenCount,         color:T.muted},
    {label:'Media Files',     value: stats ? stats.gallery : '…', color:T.yellow},
    {label:'Rooms',           value: stats ? stats.rooms : '…',    color:T.accent},
    {label:'Unread Messages', value: stats ? stats.messages : '…', color:T.yellow},
    {label:'Events',          value: stats ? stats.events : '…',   color:T.muted},
  ];

  return (
    <div>
      <h2 style={{fontFamily:'Playfair Display', fontSize:24, marginBottom:6, color:T.fg}}>Overview</h2>
      <p style={{color:T.muted, fontSize:13, marginBottom:28}}>
        Welcome back, {ADMIN_USER}. Here's your website at a glance.
      </p>

      <div style={{display:'grid', gridTemplateColumns:'repeat(auto-fit,minmax(150px,1fr))',
        gap:12, marginBottom:32}}>
        {cards.map(s => (
          <div key={s.label} style={{background:T.bg2, border:`1px solid ${T.border}`,
            borderRadius:8, padding:'18px 20px'}}>
            <div style={{fontSize:28, fontWeight:600, color:s.color, marginBottom:4}}>{s.value}</div>
            <div style={{fontSize:11, color:T.muted}}>{s.label}</div>
          </div>
        ))}
      </div>

      <h3 style={{fontSize:13, fontWeight:600, color:T.fg, marginBottom:12,
        letterSpacing:'0.06em', textTransform:'uppercase'}}>Quick Links</h3>
      <div style={{display:'flex', flexWrap:'wrap', gap:8}}>
        {pages.map(p => (
          <a key={p.id} href={p.file} target="_blank"
            style={{padding:'8px 14px', border:`1px solid ${T.border}`,
              borderRadius:6, fontSize:12, color:T.muted, transition:'all .15s'}}
            onMouseEnter={e => { e.currentTarget.style.borderColor = T.accent; e.currentTarget.style.color = T.accent; }}
            onMouseLeave={e => { e.currentTarget.style.borderColor = T.border; e.currentTarget.style.color = T.muted; }}
          >{p.label} ↗</a>
        ))}
      </div>
    </div>
  );
}

/* ── Pages ── */
function TabPages({pages, visibility, setVisibility, setPages, savePages}) {
  const [drag, setDrag] = useState(null);

  const toggle = id => {
    const u = {...visibility, [id]: !visibility[id]};
    setVisibility(u);
    apiSet('page_visibility', JSON.stringify(u));
  };
  const dragOver = (e, id) => {
    e.preventDefault();
    if (drag === id) return;
    const from = pages.findIndex(p => p.id === drag);
    const to   = pages.findIndex(p => p.id === id);
    if (from === -1 || to === -1) return;
    const arr  = [...pages];
    arr.splice(to, 0, arr.splice(from, 1)[0]);
    setPages(arr); savePages(arr);
  };

  return (
    <div>
      <div style={{marginBottom:24}}>
        <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Pages</h2>
        <p style={{color:T.muted, fontSize:13}}>Drag to reorder · toggle visibility to show/hide from navigation. Hidden pages still exist but disappear from the front-end menu.</p>
      </div>

      <div style={{display:'flex', flexDirection:'column', gap:3}}>
        {pages.map((p, i) => {
          const visible = visibility[p.id] !== false;
          return (
            <div key={p.id} draggable
              onDragStart={() => setDrag(p.id)}
              onDragOver={e => dragOver(e, p.id)}
              style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:7,
                padding:'12px 15px', display:'flex', alignItems:'center', gap:11,
                cursor:'grab', transition:'background .15s'}}
              onMouseEnter={e => e.currentTarget.style.background = T.bg3}
              onMouseLeave={e => e.currentTarget.style.background = T.bg2}
            >
              <span style={{color:T.muted, opacity:.35, fontSize:13, userSelect:'none'}}>⠿</span>
              <span style={{fontSize:11, color:T.muted, width:16, textAlign:'center'}}>{i + 1}</span>
              <div style={{flex:1}}>
                <span style={{fontSize:13, fontWeight:500, color:T.fg}}>{p.label}</span>
                <span style={{fontSize:11, color:T.muted, marginLeft:8, opacity:.6}}>{p.file}</span>
              </div>
              <span style={{fontSize:10, padding:'2px 8px', borderRadius:10,
                background: visible ? 'oklch(62% 0.16 148 / 0.15)' : 'oklch(60% 0.20 25 / 0.12)',
                color: visible ? T.green : T.red}}>{visible ? 'Visible' : 'Hidden'}</span>
              <div style={{display:'flex', gap:5}}>
                <Btn onClick={() => toggle(p.id)} small secondary>{visible ? 'Hide' : 'Show'}</Btn>
                <a href={p.file} target="_blank"><Btn small secondary>View ↗</Btn></a>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

/* ── Media ── */
function TabMedia({splatEnabled}) {
  const [items,     setItems]     = useState([]);
  const [filter,    setFilter]    = useState('all');
  const [typeFilter,setTypeFilter]= useState('all');
  const [uploading, setUploading] = useState(false);
  const [uploadCat, setUploadCat] = useState('general');
  const fileRef = useRef(null);

  const reload = () => {
    fetch('api/media.php').then(r => r.json()).then(d => setItems(d.items || [])).catch(() => {});
  };
  useEffect(reload, []);

  const accept = splatEnabled
    ? 'image/jpeg,image/png,image/webp,video/mp4,.splat,.ksplat'
    : 'image/jpeg,image/png,image/webp,video/mp4';

  const onUploadClick = () => fileRef.current?.click();

  const onFiles = async ev => {
    const files = Array.from(ev.target.files || []);
    if (!files.length) return;
    setUploading(true);
    for (const f of files) {
      const fd = new FormData();
      fd.append('file', f);
      fd.append('category', uploadCat);
      fd.append('csrf_token', CSRF_TOKEN);
      try {
        const r = await fetch('api/upload.php', {
          method:'POST',
          headers:{'X-CSRF-Token': CSRF_TOKEN},
          body: fd
        });
        const d = await r.json();
        if (!d.success) alert(`${f.name}: ${d.error || 'upload failed'}`);
      } catch { alert(`${f.name}: upload failed`); }
    }
    ev.target.value = '';
    setUploading(false);
    reload();
  };

  const togglePublish = async it => {
    await fetch('api/media.php', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({id: it.id, is_published: it.is_published ? 0 : 1}),
    });
    reload();
  };

  const changeCat = async (it, cat) => {
    await fetch('api/media.php', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({id: it.id, category: cat}),
    });
    reload();
  };

  const remove = async it => {
    if (!confirm('Delete this file? This removes it from disk and DB.')) return;
    await fetch('api/media.php', {
      method:'DELETE',
      headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({id: it.id}),
    });
    reload();
  };

  const filtered = items.filter(it => {
    if (filter !== 'all' && it.category !== filter) return false;
    if (typeFilter !== 'all' && it.file_type !== typeFilter) return false;
    return true;
  });

  const formatBytes = b => {
    if (b < 1024) return b + ' B';
    if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
    return (b/1024/1024).toFixed(1) + ' MB';
  };

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:24}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Media Library</h2>
          <p style={{color:T.muted, fontSize:13}}>
            Images (10 MB JPEG/PNG/WebP) · Videos (200 MB MP4){splatEnabled ? ' · 3D Splats (500 MB .splat/.ksplat)' : ''}
          </p>
        </div>
        <div style={{display:'flex', gap:8, alignItems:'center'}}>
          <select value={uploadCat} onChange={e => setUploadCat(e.target.value)} style={{...IS, width:140}}>
            {MEDIA_CATEGORIES.filter(c => splatEnabled || c.id !== 'room_tour').map(c => (
              <option key={c.id} value={c.id}>{c.label}</option>
            ))}
          </select>
          <Btn onClick={onUploadClick} disabled={uploading}>{uploading ? 'Uploading…' : '+ Upload'}</Btn>
          <input ref={fileRef} type="file" multiple accept={accept} onChange={onFiles} style={{display:'none'}}/>
        </div>
      </div>

      {/* Filters */}
      <div style={{display:'flex', gap:6, flexWrap:'wrap', marginBottom:10}}>
        <button onClick={() => setFilter('all')} style={chipStyle(filter==='all')}>All</button>
        {MEDIA_CATEGORIES.filter(c => splatEnabled || c.id !== 'room_tour').map(c => (
          <button key={c.id} onClick={() => setFilter(c.id)} style={chipStyle(filter===c.id)}>{c.label}</button>
        ))}
      </div>
      <div style={{display:'flex', gap:6, flexWrap:'wrap', marginBottom:24}}>
        {[['all','All Types'],['image','📷 Images'],['video','🎬 Videos'],...(splatEnabled?[['splat','🧊 3D Splats']]:[])].map(([k,l]) => (
          <button key={k} onClick={() => setTypeFilter(k)} style={chipStyle(typeFilter===k, true)}>{l}</button>
        ))}
      </div>

      {filtered.length === 0
        ? <div style={{padding:'40px 20px', textAlign:'center', color:T.muted, fontSize:13,
            background:T.bg2, border:`1px dashed ${T.border}`, borderRadius:8}}>
            No media yet. Click <strong>+ Upload</strong> to add files.
          </div>
        : <div style={{display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(200px,1fr))', gap:14}}>
            {filtered.map(it => (
              <div key={it.id} style={{background:T.bg2, border:`1px solid ${T.border}`,
                borderRadius:8, overflow:'hidden', opacity: it.is_published ? 1 : .55}}>
                <div style={{height:140, position:'relative', background:T.bg3}}>
                  {it.file_type === 'image'
                    ? <img src={it.url} alt={it.original_name} style={{width:'100%',height:'100%',objectFit:'cover'}}/>
                    : it.file_type === 'video'
                      ? <div style={{width:'100%',height:'100%',display:'flex',alignItems:'center',justifyContent:'center',fontSize:32,color:T.muted,background:'#000'}}>🎬</div>
                      : <div style={{width:'100%',height:'100%',display:'flex',alignItems:'center',justifyContent:'center',fontSize:36,background:'oklch(28% 0.06 250)'}}>🧊</div>
                  }
                  {!it.is_published && <span style={{position:'absolute',top:6,left:6,background:'rgba(0,0,0,0.7)',color:'white',fontSize:9,padding:'2px 6px',borderRadius:3}}>HIDDEN</span>}
                </div>
                <div style={{padding:'10px 12px'}}>
                  <div title={it.original_name} style={{fontSize:11, color:T.fg, marginBottom:3, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap'}}>{it.original_name}</div>
                  <div style={{fontSize:10, color:T.muted, marginBottom:8}}>{formatBytes(it.file_size_bytes)} · {it.file_type}</div>
                  <select value={it.category} onChange={e => changeCat(it, e.target.value)}
                    style={{...IS, padding:'4px 6px', fontSize:10, marginBottom:6}}>
                    {MEDIA_CATEGORIES.map(c => <option key={c.id} value={c.id}>{c.label}</option>)}
                  </select>
                  <div style={{display:'flex', gap:5}}>
                    <Btn onClick={() => togglePublish(it)} small secondary>{it.is_published ? 'Hide' : 'Show'}</Btn>
                    <Btn onClick={() => remove(it)} small danger>✕</Btn>
                  </div>
                </div>
              </div>
            ))}
          </div>
      }
    </div>
  );
}

function chipStyle(active, small) {
  return {
    padding: small ? '4px 11px' : '5px 13px', borderRadius:20, cursor:'pointer',
    border:`1px solid ${active ? T.accent : T.border}`,
    background: active ? 'oklch(62% 0.18 22 / 0.13)' : 'transparent',
    color: active ? T.accent : T.muted, fontSize: small ? 11 : 12, fontWeight:500,
    transition:'all .15s',
  };
}

/* ── Colors ── */
function cssVarToHex(themeId, varName) {
  try {
    const probe = document.createElement('div');
    probe.className = `theme-${themeId}`;
    probe.style.cssText = 'position:absolute;opacity:0;pointer-events:none;width:1px;height:1px';
    document.body.appendChild(probe);
    const raw = getComputedStyle(probe).getPropertyValue(varName).trim();
    document.body.removeChild(probe);
    if (!raw) return '#808080';
    const conv = document.createElement('div');
    conv.style.cssText = `position:absolute;opacity:0;pointer-events:none;background-color:${raw}`;
    document.body.appendChild(conv);
    const rgb = getComputedStyle(conv).backgroundColor;
    document.body.removeChild(conv);
    const m = rgb.match(/[\d.]+/g);
    if (!m || m.length < 3) return '#808080';
    return '#' + [0,1,2].map(i => Math.round(parseFloat(m[i])).toString(16).padStart(2,'0')).join('');
  } catch { return '#808080'; }
}

function TabColors({activeTheme, setActiveTheme}) {
  const [overrides,   setOverrides]   = useState({});
  const [saved,       setSaved]       = useState(false);
  const [loading,     setLoading]     = useState(true);

  useEffect(() => {
    apiGet('rosali_color_overrides').then(ov => {
      if (ov) try { setOverrides(JSON.parse(ov)); } catch {}
      setLoading(false);
    });
  }, []);

  const getVal = (themeId, varName) => {
    if (overrides[themeId]?.[varName]) return overrides[themeId][varName];
    return cssVarToHex(themeId, varName);
  };

  const setVal = (varName, hex) => {
    const updated = {...overrides, [activeTheme]: {...(overrides[activeTheme] || {}), [varName]: hex}};
    setOverrides(updated);
    /* Live preview within the admin (front-end picks up on next save+reload) */
    let el = document.getElementById('rosali-color-overrides');
    if (!el) { el = document.createElement('style'); el.id = 'rosali-color-overrides'; document.head.appendChild(el); }
    let css = '';
    Object.entries(updated).forEach(([th, vars]) => {
      const entries = Object.entries(vars || {}).filter(([, v]) => v);
      if (entries.length) css += `.theme-${th}{${entries.map(([k,v]) => `${k}:${v}`).join(';')}}`;
    });
    el.textContent = css;
  };

  const doSave = async () => {
    await Promise.all([
      apiSet('active_theme', activeTheme),
      apiSet('rosali_color_overrides', JSON.stringify(overrides)),
    ]);
    setSaved(true); setTimeout(() => setSaved(false), 2000);
  };

  const resetTheme = () => {
    if (!confirm(`Reset all color overrides for ${THEME_LABELS[activeTheme]}?`)) return;
    const updated = {...overrides, [activeTheme]: {}};
    setOverrides(updated);
    apiSet('rosali_color_overrides', JSON.stringify(updated));
    const el = document.getElementById('rosali-color-overrides');
    if (el) {
      let css = '';
      Object.entries(updated).forEach(([th, vars]) => {
        const e = Object.entries(vars || {}).filter(([, v]) => v);
        if (e.length) css += `.theme-${th}{${e.map(([k,v]) => `${k}:${v}`).join(';')}}`;
      });
      el.textContent = css;
    }
  };

  const groups = [...new Set(THEME_VARS.map(v => v.group))];

  return (
    <div>
      <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Colors & Theme</h2>
      <p style={{color:T.muted, fontSize:13, marginBottom:22}}>
        Select the active site theme, then customize its color palette. Saving updates the live website.
      </p>

      <div style={{display:'flex', gap:6, flexWrap:'wrap', marginBottom:28}}>
        {THEMES.map(th => (
          <button key={th} onClick={() => setActiveTheme(th)} style={{
            padding:'7px 14px', borderRadius:20, cursor:'pointer',
            border:`1px solid ${activeTheme === th ? T.accent : T.border}`,
            background: activeTheme === th ? 'oklch(62% 0.18 22 / 0.13)' : 'transparent',
            color: activeTheme === th ? T.accent : T.muted, fontSize:12, fontWeight:500,
            transition:'all .15s',
          }}>{THEME_LABELS[th]}{activeTheme === th && ' ●'}</button>
        ))}
      </div>

      {loading
        ? <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        : groups.map(grp => (
          <div key={grp} style={{marginBottom:24}}>
            <div style={{fontSize:10, textTransform:'uppercase', letterSpacing:'0.14em',
              color:T.muted, marginBottom:10}}>{grp}</div>
            <div style={{display:'flex', flexDirection:'column', gap:3}}>
              {THEME_VARS.filter(v => v.group === grp).map(v => {
                const hex         = getVal(activeTheme, v.key);
                const isOverridden = !!(overrides[activeTheme]?.[v.key]);
                return (
                  <div key={v.key} style={{display:'flex', alignItems:'center', gap:12,
                    background:T.bg2, border:`1px solid ${T.border}`, borderRadius:6, padding:'10px 14px'}}>
                    <input type="color" value={hex} onChange={e => setVal(v.key, e.target.value)}
                      style={{width:36, height:28, borderRadius:4, border:`1px solid ${T.border}`,
                        background:'none', cursor:'pointer'}}/>
                    <div style={{flex:1}}>
                      <span style={{fontSize:13, color:T.fg, fontWeight:500}}>{v.label}</span>
                      <span style={{fontSize:10, color:T.muted, marginLeft:8, fontFamily:'monospace'}}>{v.key}</span>
                    </div>
                    <span style={{fontSize:10, fontFamily:'monospace', color:T.muted}}>{hex}</span>
                    {isOverridden && (
                      <span style={{fontSize:9, padding:'2px 7px', borderRadius:8,
                        background:'oklch(62% 0.18 22 / 0.15)', color:T.accent}}>custom</span>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        ))
      }

      <div style={{display:'flex', gap:10, justifyContent:'space-between', alignItems:'center', marginTop:8}}>
        <Btn onClick={resetTheme} secondary>Reset {THEME_LABELS[activeTheme]}</Btn>
        <SaveBtn onSave={doSave} saved={saved}/>
      </div>
    </div>
  );
}

/* ── Content ── */
function TabContent() {
  const [activePage, setActivePage] = useState('general');
  const [vals,       setVals]       = useState({});
  const [saved,      setSaved]      = useState(false);
  const [loading,    setLoading]    = useState(true);

  useEffect(() => {
    const allKeys = CONTENT_FIELDS.flatMap(pg => pg.fields.map(f => f.key));
    Promise.all(allKeys.map(k => apiGet('rc_' + k).then(v => ({k, v})))).then(results => {
      const out = {};
      results.forEach(({k, v}) => { if (v) out[k] = v; });
      setVals(out);
      setLoading(false);
    });
  }, []);

  const doSave = async () => {
    const pg = CONTENT_FIELDS.find(p => p.id === activePage);
    if (!pg) return;
    for (const f of pg.fields) await apiSet('rc_' + f.key, vals[f.key] || '');
    setSaved(true); setTimeout(() => setSaved(false), 2000);
  };

  const pg = CONTENT_FIELDS.find(p => p.id === activePage);

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:22}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Content Editor</h2>
          <p style={{color:T.muted, fontSize:13}}>Edit text fields. Leave blank to use the page's built-in default. Saved values appear on every page that uses them.</p>
        </div>
        <SaveBtn onSave={doSave} saved={saved}/>
      </div>

      <div style={{display:'flex', gap:4, marginBottom:24, borderBottom:`1px solid ${T.border}`, paddingBottom:0}}>
        {CONTENT_FIELDS.map(p => (
          <button key={p.id} onClick={() => setActivePage(p.id)} style={{
            padding:'8px 16px', border:'none',
            borderBottom:`2px solid ${activePage === p.id ? T.accent : 'transparent'}`,
            background:'transparent', color: activePage === p.id ? T.fg : T.muted,
            fontSize:13, fontWeight: activePage === p.id ? 600 : 400,
            cursor:'pointer', transition:'all .15s', marginBottom:-1,
          }}>{p.label}</button>
        ))}
      </div>

      {loading
        ? <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        : <div style={{display:'flex', flexDirection:'column', gap:18}}>
            {pg && pg.fields.map(f => (
              <div key={f.key}>
                <label style={{display:'block', fontSize:12, fontWeight:500, color:T.fg, marginBottom:3}}>{f.label}</label>
                {f.hint && <div style={{fontSize:11, color:T.muted, marginBottom:5}}>{f.hint}</div>}
                {f.multi
                  ? <textarea value={vals[f.key] || ''} rows={3}
                      onChange={e => setVals(v => ({...v, [f.key]: e.target.value}))}
                      placeholder="Leave blank to use default" style={{...IS, resize:'vertical'}}/>
                  : <input value={vals[f.key] || ''}
                      onChange={e => setVals(v => ({...v, [f.key]: e.target.value}))}
                      placeholder="Leave blank to use default" style={IS}/>
                }
              </div>
            ))}
          </div>
      }
    </div>
  );
}

/* ── Layout ── */
function TabLayout() {
  const [prefs,   setPrefs]   = useState({});
  const [saved,   setSaved]   = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all(LAYOUT_SECTIONS.map(s => apiGet('layout_' + s.key).then(v => ({k:s.key, v})))).then(results => {
      const out = {};
      results.forEach(({k, v}) => { if (v) out[k] = v; });
      setPrefs(out);
      setLoading(false);
    });
  }, []);

  const doSave = async () => {
    for (const s of LAYOUT_SECTIONS) {
      await apiSet('layout_' + s.key, prefs[s.key] || s.values[0]);
    }
    setSaved(true); setTimeout(() => setSaved(false), 2000);
  };

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:22}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Layout</h2>
          <p style={{color:T.muted, fontSize:13}}>Choose how key sections are arranged on the home page.</p>
        </div>
        <SaveBtn onSave={doSave} saved={saved}/>
      </div>

      {loading
        ? <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        : <div style={{display:'flex', flexDirection:'column', gap:3}}>
            {LAYOUT_SECTIONS.map(s => (
              <div key={s.key} style={{background:T.bg2, border:`1px solid ${T.border}`,
                borderRadius:7, padding:'14px 16px', display:'flex',
                alignItems:'center', gap:16, flexWrap:'wrap'}}>
                <div style={{flex:1, minWidth:140}}>
                  <div style={{fontSize:13, fontWeight:500, color:T.fg}}>{s.label}</div>
                </div>
                <div style={{display:'flex', gap:4}}>
                  {s.options.map((opt, i) => {
                    const active = (prefs[s.key] || s.values[0]) === s.values[i];
                    return (
                      <button key={i} onClick={() => setPrefs(p => ({...p, [s.key]: s.values[i]}))} style={{
                        padding:'6px 14px', cursor:'pointer', fontSize:12, transition:'all .15s',
                        border:`1px solid ${active ? T.accent : T.border}`,
                        borderRadius:5,
                        background: active ? 'oklch(62% 0.18 22 / 0.13)' : 'transparent',
                        color: active ? T.accent : T.muted,
                      }}>{opt}</button>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
      }
    </div>
  );
}

/* ── Settings ── */
function TabSettings({splatEnabled, setSplatEnabled}) {
  const [confirmTxt, setConfirmTxt] = useState('');
  const [stats,      setStats]      = useState(null);
  const [busy,       setBusy]       = useState(false);

  const refreshStats = () => fetch('api/settings-stats.php').then(r => r.json()).then(setStats).catch(() => {});

  useEffect(refreshStats, []);

  const clearAll = async () => {
    if (confirmTxt !== 'RESET') { alert('Type RESET to confirm'); return; }
    setBusy(true);
    const r = await fetch('api/settings-clear.php', {
      method:'POST',
      headers:{'X-CSRF-Token': CSRF_TOKEN},
    });
    const d = await r.json();
    setBusy(false);
    if (d.success) {
      alert('All admin content cleared. Front-end will show defaults on next load.');
    } else {
      alert('Reset failed: ' + (d.error || 'unknown error'));
    }
    setConfirmTxt('');
    refreshStats();
  };

  const toggleSplat = async () => {
    const next = !splatEnabled;
    setSplatEnabled(next);
    await apiSet('splat_enabled', next ? '1' : '0');
  };

  const fmtBytes = b => {
    if (!b) return '0 B';
    if (b < 1024) return b + ' B';
    if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
    if (b < 1024*1024*1024) return (b/1024/1024).toFixed(1) + ' MB';
    return (b/1024/1024/1024).toFixed(2) + ' GB';
  };

  return (
    <div>
      <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Settings</h2>
      <p style={{color:T.muted, fontSize:13, marginBottom:28}}>Site features, storage info, and data management.</p>

      {/* Splat toggle */}
      <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20, marginBottom:16,
        display:'flex', justifyContent:'space-between', alignItems:'center', gap:16, flexWrap:'wrap'}}>
        <div>
          <div style={{fontSize:14, fontWeight:600, color:T.fg, marginBottom:4}}>🧊 Enable 3D Room Tours</div>
          <p style={{fontSize:12, color:T.muted, lineHeight:1.6, maxWidth:520}}>
            Adds support for Gaussian Splat (.splat / .ksplat) uploads. When off, splat files cannot be uploaded and the gsplat.js viewer is never loaded on any page (saves bandwidth).
          </p>
        </div>
        <button onClick={toggleSplat} style={{
          width:54, height:28, borderRadius:14, border:'none',
          background: splatEnabled ? T.accent : T.bg3,
          position:'relative', cursor:'pointer', transition:'background .2s'
        }}>
          <span style={{position:'absolute', top:3, left: splatEnabled ? 28 : 3,
            width:22, height:22, borderRadius:'50%', background:'white',
            transition:'left .2s'}}/>
        </button>
      </div>

      {/* Storage stats */}
      <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20, marginBottom:16}}>
        <div style={{fontSize:14, fontWeight:600, color:T.fg, marginBottom:12}}>Storage</div>
        {stats
          ? <>
              <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:10, marginBottom:12}}>
                <StatBox label="Media records" value={stats.media} unit="files in DB"/>
                <StatBox label="Content overrides" value={stats.content} unit="text fields"/>
                <StatBox label="Color overrides" value={stats.colors} unit="themes customized"/>
              </div>
              {stats.disk && (
                <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:10}}>
                  <StatBox label="Images on disk" value={stats.disk.images.files} unit={fmtBytes(stats.disk.images.bytes)}/>
                  <StatBox label="Videos on disk" value={stats.disk.videos.files} unit={fmtBytes(stats.disk.videos.bytes)}/>
                  <StatBox label="Splats on disk" value={stats.disk.splats.files} unit={fmtBytes(stats.disk.splats.bytes)}/>
                </div>
              )}
            </>
          : <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        }
      </div>

      <div style={{background:'oklch(60% 0.20 25 / 0.07)',
        border:'1px solid oklch(60% 0.20 25 / 0.3)', borderRadius:8, padding:20}}>
        <div style={{fontSize:14, fontWeight:600, color:T.red, marginBottom:8}}>⚠ Reset All Content & Media</div>
        <p style={{fontSize:12, color:T.muted, marginBottom:14, lineHeight:1.6}}>
          Removes every uploaded media file (disk + DB), all content overrides, layout preferences, page visibility, and color customizations. The active theme is preserved. Type <strong style={{color:T.fg}}>RESET</strong> to confirm.
        </p>
        <div style={{display:'flex', gap:10}}>
          <input value={confirmTxt} onChange={e => setConfirmTxt(e.target.value)}
            placeholder="Type RESET" style={{...IS, width:130}}/>
          <Btn onClick={clearAll} danger disabled={busy}>{busy ? 'Resetting…' : 'Reset All'}</Btn>
        </div>
      </div>
    </div>
  );
}

function StatBox({label, value, unit}) {
  return (
    <div style={{padding:'12px 14px', background:T.bg3, borderRadius:6}}>
      <div style={{fontSize:20, fontWeight:600, color:T.accent}}>{value}</div>
      <div style={{fontSize:11, color:T.muted}}>{label}</div>
      {unit && <div style={{fontSize:10, color:T.muted, opacity:.7, marginTop:2}}>{unit}</div>}
    </div>
  );
}

/* ── Root App ── */
function App() {
  const [tab,           setTab]           = useState('overview');
  const [pages,         setPages]         = useState(DEFAULT_PAGES);
  const [visibility,    setVisibility]    = useState({});
  const [activeTheme,   setActiveTheme]   = useState('rosa');
  const [splatEnabled,  setSplatEnabled]  = useState(false);

  useEffect(() => {
    if (window.initRosali) window.initRosali();
    apiGet('admin_pages').then(v => {
      if (v) try {
        const arr = JSON.parse(v);
        if (Array.isArray(arr) && arr.length) setPages(arr);
      } catch {}
    });
    apiGet('page_visibility').then(v => {
      if (v) try { setVisibility(JSON.parse(v) || {}); } catch {}
    });
    apiGet('active_theme').then(v => { if (v) setActiveTheme(v); });
    apiGet('splat_enabled').then(v => { setSplatEnabled(v === '1'); });
  }, []);

  const savePages = p => apiSet('admin_pages', JSON.stringify(p));

  return (
    <div style={{display:'flex', minHeight:'100vh'}}>
      <Sidebar tab={tab} setTab={setTab} pageCount={pages.length}/>
      <main style={{flex:1, padding:'32px 36px', overflowY:'auto', maxHeight:'100vh', background:T.bg}}>
        {tab === 'overview' && <TabOverview pages={pages} visibility={visibility}/>}
        {tab === 'pages'    && <TabPages pages={pages} visibility={visibility} setVisibility={setVisibility} setPages={setPages} savePages={savePages}/>}
        {tab === 'media'    && <TabMedia splatEnabled={splatEnabled}/>}
        {tab === 'colors'   && <TabColors activeTheme={activeTheme} setActiveTheme={setActiveTheme}/>}
        {tab === 'content'  && <TabContent/>}
        {tab === 'layout'   && <TabLayout/>}
        {tab === 'settings' && <TabSettings splatEnabled={splatEnabled} setSplatEnabled={setSplatEnabled}/>}
      </main>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body>
</html>
