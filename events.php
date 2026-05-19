<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$theme = getActiveTheme();
$lang  = getActiveLang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Events & Meetings — Rosali Hotel Situbondo</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=Outfit:wght@300;400;500;600&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>
</head>
<body class="theme-<?= $theme ?>">
<?php require __DIR__ . '/includes/front_init.php'; ?>
<div id="root"></div>
<script type="text/babel" src="shared.jsx"></script>
<script type="text/babel">
const { useState, useEffect } = React;
const { RosaliImg, RosaliNav, RosaliFooter, RosaliBtn, RosaliLabel, RosaliPageHero, RosaliWaFab, initRosali, useResponsive, getUrlTheme, setUrlTheme, RC } = window;

const HALLS = {
  en:[
    {name:'Jasmine Meeting Room',cap:'20–40 pax',feat:['Projector & Screen','Whiteboard','AC','Sound System','Free WiFi']},
    {name:'Tulip Meeting Room',cap:'30–60 pax',feat:['LED Projector','Podium','AC','Microphone','Free WiFi']},
    {name:'Lavender Meeting Room',cap:'40–80 pax',feat:['Full AV Setup','Stage','AC','Catering Area','Free WiFi']},
    {name:'The Dream Garden',cap:'100–300 pax',feat:['Open Air','Garden Setting','Stage','Catering','Lighting Rig']},
    {name:'Main Restaurant Hall',cap:'50–150 pax',feat:['Indoor','AV System','Stage','Full Catering','AC']},
  ],
  id:[
    {name:'Ruang Rapat Jasmine',cap:'20–40 orang',feat:['Proyektor & Layar','Papan Tulis','AC','Sound System','WiFi Gratis']},
    {name:'Ruang Rapat Tulip',cap:'30–60 orang',feat:['Proyektor LED','Podium','AC','Mikrofon','WiFi Gratis']},
    {name:'Ruang Rapat Lavender',cap:'40–80 orang',feat:['AV Lengkap','Panggung','AC','Area Katering','WiFi Gratis']},
    {name:'The Dream Garden',cap:'100–300 orang',feat:['Terbuka','Suasana Taman','Panggung','Katering','Tata Cahaya']},
    {name:'Aula Restoran Utama',cap:'50–150 orang',feat:['Indoor','Sistem AV','Panggung','Katering Penuh','AC']},
  ]
};

function App(){
  const [theme]=useState('<?= $theme ?>');
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||'id');
  const [tab,setTab]=useState('meeting');
  const { isMobile } = useResponsive();
  initRosali(theme,lang);
  useEffect(()=>{ localStorage.setItem('rosali_lang',lang); },[lang]);
  const en=lang==='en';
  const HALL_KEYS = ['jasmine','tulip','lavender','garden','restaurant'];
  const halls = HALLS[lang].map((h,i)=>({
    ...h,
    name: RC('hall_'+HALL_KEYS[i]+'_name_'+lang, h.name),
    cap:  RC('hall_'+HALL_KEYS[i]+'_cap_'+lang,  h.cap),
  }));

  const msgMeeting = RC('events_msg_meeting_'+lang,
    en?'Hello, I would like to inquire about meeting room packages at Rosali Hotel.'
      :'Halo, saya ingin menanyakan paket ruang rapat di Rosali Hotel.');
  const msgWedding = RC('events_msg_wedding_'+lang,
    en?'Hello, I would like to inquire about wedding packages at Rosali Hotel.'
      :'Halo, saya ingin menanyakan paket pernikahan di Rosali Hotel.');

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={l=>{setLang(l);localStorage.setItem('rosali_lang',l)}} current="events" theme={theme}/>

      <RosaliPageHero
        imgLabel="events hero — dream garden wedding / meeting setup aerial"
        sup={RC('events_hero_sup_'+lang, en?'Events & Venues':'Acara & Venue')}
        title={RC('events_hero_title_'+lang, en?'Host Your\nEvent Here':'Gelar Acara\nAnda di Sini')}
        sub={RC('events_hero_sub_'+lang, en?'From corporate meetings to dream weddings — beautifully hosted with modern AV and full catering.'
          :'Dari rapat bisnis hingga pernikahan impian — digelar dengan indah, AV modern, dan katering lengkap.')}
      />

      {/* TYPE TABS */}
      <section style={{background:'var(--bg)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)'}}>
        <div style={{display:'flex',gap:4,marginBottom:48,justifyContent:'center'}}>
          {[['meeting',RC('events_tab_meeting_'+lang,en?'Meetings & Seminars':'Rapat & Seminar')],['wedding',RC('events_tab_wedding_'+lang,en?'Weddings & Parties':'Pernikahan & Pesta')]].map(([k,l])=>(
            <button key={k} onClick={()=>setTab(k)} style={{
              background:tab===k?'var(--accent)':'var(--bg2)',
              color:tab===k?'var(--bg)':'var(--fg)',border:'none',
              padding:'12px 28px',borderRadius:2,cursor:'pointer',
              fontFamily:'var(--font-b)',fontSize:14,fontWeight:tab===k?600:400,transition:'all .2s'}}>
              {l}
            </button>
          ))}
        </div>

        {tab==='meeting'?(
          <>
            <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(32px,5vw,72px)',alignItems:'center',marginBottom:56}}>
              <div>
                <RosaliLabel>{en?'Corporate Events':'Acara Korporat'}</RosaliLabel>
                <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,42px)',lineHeight:1.15,color:'var(--fg)',marginBottom:18}}>
                  {en?'Professional Meetings,\nBeautifully Set':'Rapat Profesional,\nDigelar Indah'}
                </h2>
                <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',lineHeight:1.85,marginBottom:24}}>
                  {en?'Rosali Hotel offers 5 meeting rooms for seminars, government meetings, corporate trainings, and product launches. All rooms feature modern AV equipment, catering service, and free WiFi.'
                    :'Rosali Hotel menawarkan 5 ruang rapat untuk seminar, rapat dinas, pelatihan korporat, dan peluncuran produk. Semua ruangan dilengkapi AV modern, layanan katering, dan WiFi gratis.'}
                </p>
                {['AC & free WiFi','Modern AV in all rooms','Catering & coffee breaks','Large parking for buses','Overnight stay packages'].map((f,i)=>(
                  <div key={i} style={{display:'flex',alignItems:'center',gap:10,marginBottom:8,
                    fontFamily:'var(--font-b)',fontSize:14,color:'var(--fg)'}}>
                    <span style={{width:5,height:5,borderRadius:'50%',background:'var(--accent)',flexShrink:0}}/>
                    {f}
                  </div>
                ))}
                <div style={{marginTop:28}}>
                  <RosaliBtn text={en?'Request Meeting Package':'Minta Paket Rapat'}
                    href={`https://wa.me/6287851515500?text=${encodeURIComponent(msgMeeting)}`}/>
                </div>
              </div>
              <RosaliImg label="meeting — jasmine room setup / seminar arrangement" h={420} style={{borderRadius:2}}/>
            </div>

            {/* Hall cards */}
            <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'repeat(3,1fr)',gap:3}}>
              {halls.slice(0,3).map((h,i)=>(
                <div key={i} style={{background:'var(--bg2)',borderRadius:2,overflow:'hidden'}}>
                  <RosaliImg label={`hall — ${h.name.toLowerCase()}`} h={180}/>
                  <div style={{padding:'18px 16px'}}>
                    <h4 style={{fontFamily:'var(--font-d)',fontSize:18,color:'var(--fg)',marginBottom:8}}>{h.name}</h4>
                    <p style={{fontFamily:'var(--font-b)',fontSize:12,color:'var(--accent)',
                      fontWeight:500,marginBottom:12,letterSpacing:'0.05em'}}>{h.cap}</p>
                    <div style={{display:'flex',flexWrap:'wrap',gap:5}}>
                      {h.feat.map(f=>(
                        <span key={f} style={{background:'var(--bg)',border:'1px solid var(--border)',
                          padding:'3px 8px',fontSize:10,borderRadius:2,fontFamily:'var(--font-b)',color:'var(--fg-muted)'}}>{f}</span>
                      ))}
                    </div>
                  </div>
                </div>
              ))}
            </div>
            <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'repeat(2,1fr)',gap:3,marginTop:3}}>
              {halls.slice(3).map((h,i)=>(
                <div key={i} style={{background:'var(--bg2)',borderRadius:2,overflow:'hidden',display:'grid',gridTemplateColumns:isMobile?'1fr':'200px 1fr'}}>
                  <RosaliImg label={`hall — ${h.name.toLowerCase()}`} h="100%" style={{height:'100%'}}/>
                  <div style={{padding:'20px 18px'}}>
                    <h4 style={{fontFamily:'var(--font-d)',fontSize:18,color:'var(--fg)',marginBottom:8}}>{h.name}</h4>
                    <p style={{fontFamily:'var(--font-b)',fontSize:12,color:'var(--accent)',fontWeight:500,marginBottom:12}}>{h.cap}</p>
                    <div style={{display:'flex',flexWrap:'wrap',gap:5}}>
                      {h.feat.map(f=>(
                        <span key={f} style={{background:'var(--bg)',border:'1px solid var(--border)',
                          padding:'3px 8px',fontSize:10,borderRadius:2,fontFamily:'var(--font-b)',color:'var(--fg-muted)'}}>{f}</span>
                      ))}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </>
        ):(
          /* WEDDING TAB */
          <>
            <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(32px,5vw,72px)',alignItems:'center',marginBottom:48}}>
              <RosaliImg label="wedding — dream garden ceremony / floral setup / night lighting" h={460} style={{borderRadius:2}}/>
              <div>
                <RosaliLabel>{en?'Weddings & Celebrations':'Pernikahan & Perayaan'}</RosaliLabel>
                <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,42px)',lineHeight:1.15,color:'var(--fg)',marginBottom:18}}>
                  {en?'Your Dream Day,\nIn Our Garden':'Hari Istimewa Anda,\ndi Taman Kami'}
                </h2>
                <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',lineHeight:1.85,marginBottom:24}}>
                  {en?'Celebrate life\'s most precious moments at Rosali Hotel. Our tropical garden and elegant halls make the perfect backdrop for weddings, engagements, birthdays, and reunions.'
                    :'Rayakan momen paling berharga dalam hidup Anda di Rosali Hotel. Taman tropis dan aula elegan kami menjadi latar sempurna untuk pernikahan, pertunangan, ulang tahun, dan reuni.'}
                </p>
                {(en?['The Dream Garden (up to 300 guests)','Indoor & outdoor options','Full catering & decoration','Professional AV & lighting','Accommodation packages for guests']
                  :['The Dream Garden (hingga 300 tamu)','Pilihan indoor & outdoor','Katering & dekorasi penuh','AV & tata cahaya profesional','Paket menginap untuk tamu']).map((f,i)=>(
                  <div key={i} style={{display:'flex',alignItems:'center',gap:10,marginBottom:8,
                    fontFamily:'var(--font-b)',fontSize:14,color:'var(--fg)'}}>
                    <span style={{width:5,height:5,borderRadius:'50%',background:'var(--accent)',flexShrink:0}}/>
                    {f}
                  </div>
                ))}
                <div style={{marginTop:28}}>
                  <RosaliBtn text={en?'Plan Your Wedding':'Rencanakan Pernikahan'}
                    href={`https://wa.me/6287851515500?text=${encodeURIComponent(msgWedding)}`}/>
                </div>
              </div>
            </div>
            <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'repeat(3,1fr)',gap:3}}>
              {['wedding — dream garden night / fairy lights','wedding — reception table setup','wedding — ceremony aisle garden'].map((l,i)=>(
                <RosaliImg key={i} label={l} h={240} style={{borderRadius:2}}/>
              ))}
            </div>
          </>
        )}
      </section>

      {/* CTA STRIP */}
      <section style={{background:'var(--accent)',padding:'52px clamp(20px,6vw,96px)',
        display:'flex',justifyContent:'space-between',alignItems:'center',flexWrap:'wrap',gap:20}}>
        <div>
          <h3 style={{fontFamily:'var(--font-d)',fontSize:'clamp(20px,2.5vw,32px)',color:'var(--bg)',marginBottom:8}}>
            {en?'Let\'s Plan Your Event':'Mari Rencanakan Acara Anda'}
          </h3>
          <p style={{fontFamily:'var(--font-b)',fontSize:14,color:'var(--bg)',opacity:.8}}>
            {en?'Contact us for a customized proposal and site visit.':'Hubungi kami untuk proposal khusus dan kunjungan lokasi.'}
          </p>
        </div>
        <RosaliBtn text={en?'Contact Us Now':'Hubungi Kami Sekarang'}
          style={{background:'var(--bg)',color:'var(--accent)'}}/>
      </section>

      <RosaliFooter lang={lang}/>
      <RosaliWaFab/>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body></html>
