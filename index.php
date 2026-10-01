<?php
require 'config/db.php';
require 'includes/functions.php';

$donors=(int)($conn->query("SELECT COUNT(*) c FROM donor_profiles WHERE verified=1")->fetch_assoc()['c']??0);
$available=(int)($conn->query("SELECT COUNT(*) c FROM donor_profiles WHERE availability='available'")->fetch_assoc()['c']??0);
$completed=(int)($conn->query("SELECT COUNT(*) c FROM blood_requests WHERE status='completed'")->fetch_assoc()['c']??0);
$hospitals=(int)($conn->query("SELECT COUNT(*) c FROM hospitals WHERE verified=1")->fetch_assoc()['c']??0);

$active=$conn->query("SELECT br.*,h.name hospital_name FROM blood_requests br LEFT JOIN hospitals h ON h.id=br.hospital_id WHERE br.status NOT IN ('completed','cancelled') ORDER BY FIELD(br.priority,'critical','urgent','normal'),br.created_at DESC LIMIT 3");
$hero=$conn->query("SELECT br.*,h.name hospital_name FROM blood_requests br LEFT JOIN hospitals h ON h.id=br.hospital_id WHERE br.status NOT IN ('completed','cancelled') ORDER BY FIELD(br.priority,'critical','urgent','normal'),br.created_at DESC LIMIT 1")->fetch_assoc();

$media=[];
$mr=$conn->query("SELECT * FROM site_media WHERE is_active=1 AND media_type='image' ORDER BY id DESC LIMIT 8");
while($m=$mr->fetch_assoc()) $media[]=$m;
if(!$media){
    $media=[
        ['media_url'=>'https://images.pexels.com/photos/37553387/pexels-photo-37553387.jpeg?auto=compress&cs=tinysrgb&w=1600','title'=>'Blood donation','caption'=>'Emergency coordination'],
        ['media_url'=>'https://images.pexels.com/photos/7088482/pexels-photo-7088482.jpeg?auto=compress&cs=tinysrgb&w=1600','title'=>'Doctors at work','caption'=>'Care team on standby'],
        ['media_url'=>'https://images.pexels.com/photos/6129688/pexels-photo-6129688.jpeg?auto=compress&cs=tinysrgb&w=1600','title'=>'Patient support','caption'=>'People first, always']
    ];
}

$events=[];
$er=$conn->query("SELECT * FROM community_events WHERE is_active=1 ORDER BY event_date ASC LIMIT 4");
if($er){ while($e=$er->fetch_assoc()) $events[]=$e; }
$stories=[];
$sr=$conn->query("SELECT * FROM impact_stories WHERE is_active=1 ORDER BY id DESC LIMIT 3");
if($sr){ while($s=$sr->fetch_assoc()) $stories[]=$s; }

$heroTitle=setting($conn,'hero_title','Blood reaches people faster when the network thinks together.');
$heroSubtitle=setting($conn,'hero_subtitle','Connect urgent patients, hospitals and ready donors.');
$video=setting($conn,'video_url','');
$pageTitle='Emergency Blood & Donor Network';
include 'includes/header.php';
?>
<section class="hero">
    <div class="slides">
        <?php foreach(array_slice($media,0,4) as $i=>$m):?>
            <div class="slide <?=$i===0?'active':''?>" style="background-image:linear-gradient(90deg,rgba(13,10,14,.92) 0%,rgba(13,10,14,.72) 45%,rgba(13,10,14,.28) 100%),url('<?=e(safe_https_url($m['media_url']))?>')"></div>
        <?php endforeach;?>
    </div>
    <div class="container hero-content">
        <div>
            <div class="eyebrow"><span class="pulse-dot"></span> Every second matters</div>
            <h1><?=e($heroTitle)?></h1>
            <p><?=e($heroSubtitle)?></p>
            <div class="hero-actions">
                <a class="btn btn-primary btn-lg" href="request-blood.php"><i class="fa-solid fa-bolt"></i> Request Blood Now</a>
                <a class="btn btn-ghost btn-lg" href="register.php"><i class="fa-solid fa-heart"></i> Become a Donor</a>
            </div>
            <div class="trust-row">
                <span><i class="fa-solid fa-shield-halved"></i> Privacy-first contact</span>
                <span><i class="fa-solid fa-circle-check"></i> Verified network</span>
                <span><i class="fa-solid fa-clock-rotate-left"></i> Full case history</span>
            </div>
        </div>
        <div class="hero-panel">
            <div class="live-head"><h3>Live emergency desk</h3><span class="live-badge"><span class="pulse-dot"></span> Live</span></div>
            <?php if($hero):?>
                <div class="emergency-card">
                    <div class="row"><div class="blood-ball"><?=e($hero['blood_group'])?></div><span class="priority <?=e($hero['priority'])?>"><?=priority_icon($hero['priority'])?> <?=e($hero['priority'])?></span></div>
                    <h3><?=e($hero['patient_name'])?></h3>
                    <div class="subline"><i class="fa-solid fa-hospital"></i> <?=e($hero['hospital_name']?:$hero['address'])?></div>
                    <div class="mini-meta">
                        <div><span>Units</span><b><?=$hero['units']?> needed</b></div>
                        <div><span>Radius</span><b><?=$hero['broadcast_radius']?> km</b></div>
                        <div><span>Status</span><b><?=e(status_label($hero['status']))?></b></div>
                    </div>
                    <a href="request-view.php?id=<?=$hero['id']?>" class="btn btn-primary" style="width:100%;margin-top:14px">Open live case <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            <?php else:?>
                <div class="empty">No active emergency at this moment.</div>
            <?php endif;?>
            <div class="hero-live-foot"><span>Compatibility engine online</span><b><?=$available?> donors ready</b></div>
        </div>
    </div>
    <div class="hero-dots">
        <?php foreach(array_slice($media,0,4) as $i=>$m):?><button class="<?=$i===0?'active':''?>" aria-label="Slide <?=$i+1?>"></button><?php endforeach;?>
    </div>
</section>

<section class="stats-strip"><div class="container"><div class="stats-card"><div class="stat"><strong data-counter="<?=$donors?>">0</strong><span>Verified donors</span></div><div class="stat"><strong data-counter="<?=$available?>">0</strong><span>Available now</span></div><div class="stat"><strong data-counter="<?=$completed?>">0</strong><span>Cases completed</span></div><div class="stat"><strong data-counter="<?=$hospitals?>">0</strong><span>Network centers</span></div></div></div></section>

<section class="section event-strip-section">
  <div class="container">
    <div class="section-title left"><span class="kicker">Community action</span><h2>Events, camps and awareness moments that inspire people to join.</h2><p>These blocks are dynamic and can be changed from the admin panel.</p></div>
    <div class="event-strip">
      <?php if($events): foreach($events as $event): ?>
        <article class="event-card">
          <div class="event-photo"><img src="<?=e(safe_https_url($event['image_url']))?>" alt="<?=e($event['title'])?>"></div>
          <div class="event-body">
            <div class="event-date"><span><?=date('d', strtotime($event['event_date']))?></span><small><?=date('M Y', strtotime($event['event_date']))?></small></div>
            <div class="event-copy">
              <h3><?=e($event['title'])?></h3>
              <p><?=e($event['description'])?></p>
              <div class="event-meta"><span><i class="fa-solid fa-location-dot"></i> <?=e($event['location'])?></span><span><i class="fa-solid fa-users"></i> <?=e($event['audience'])?></span></div>
            </div>
          </div>
        </article>
      <?php endforeach; else: ?>
        <div class="panel empty">Add community events from the admin panel to show camps and awareness drives here.</div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section" id="how"><div class="container"><div class="section-title"><span class="kicker">Beyond a normal donor list</span><h2>A complete emergency journey from alert to verified completion.</h2><p>Every request creates a traceable case with matching, notifications, status history, donation records and admin oversight.</p></div><div class="feature-grid"><div class="feature"><div class="feature-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div><h3>Smart compatibility engine</h3><p>Prioritizes compatible, available and verified donors using location and response history.</p></div><div class="feature"><div class="feature-icon"><i class="fa-solid fa-tower-broadcast"></i></div><h3>Expanding radius</h3><p>Emergency teams can widen the donor search as a case becomes more urgent.</p></div><div class="feature"><div class="feature-icon"><i class="fa-solid fa-eye-slash"></i></div><h3>Privacy-safe contact</h3><p>Public pages never expose donor phone numbers or sensitive account data.</p></div><div class="feature"><div class="feature-icon"><i class="fa-solid fa-timeline"></i></div><h3>Permanent history</h3><p>Status changes and case milestones remain visible after the request is completed.</p></div><div class="feature"><div class="feature-icon"><i class="fa-solid fa-photo-film"></i></div><h3>Dynamic media CMS</h3><p>Admins can change public photos, video, events and homepage messaging without editing code.</p></div><div class="feature"><div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div><h3>Safer access</h3><p>Protected sessions, CSRF checks and localhost-only demo access reduce common demo security mistakes.</p></div></div></div></section>

<section class="section dark-section"><div class="container command-grid"><div class="command-copy"><span class="kicker">Emergency command center</span><h2>See the rescue move—not another static CRUD table.</h2><p>The system turns every request into a live operational flow that hospitals, donors and admins can follow.</p><div class="workflow"><div class="workflow-step"><span class="step-no">1</span><div><b>Request verified</b><span>Blood group, units, hospital and deadline are recorded.</span></div></div><div class="workflow-step"><span class="step-no">2</span><div><b>Donors ranked & alerted</b><span>Compatible nearby donors receive relevant notifications.</span></div></div><div class="workflow-step"><span class="step-no">3</span><div><b>Donation recorded</b><span>Arrival, collection, completion and certificate history stay attached to the case.</span></div></div></div></div><div class="map-card"><div class="map-grid"></div><div class="road r1"></div><div class="road r2"></div><div class="road r3"></div><div class="radius"></div><div class="map-pin hospital p1"><i class="fa-solid fa-hospital"></i></div><div class="map-pin p2">O+</div><div class="map-pin p3">O−</div><div class="map-pin p4">A+</div><div class="map-overlay"><b><span class="pulse-dot"></span> Matching visualization</b><span>Donor distance ranked</span><span>Emergency radius expandable</span></div></div></div></section>

<section class="section media-story"><div class="container"><div class="section-title left"><span class="kicker">Visual story</span><h2>Doctors, patients and donors—more human, more inspiring.</h2><p>These homepage visuals are controlled by the admin media library and can be replaced any time.</p></div><div class="story-grid"><?php foreach(array_slice($media,0,5) as $i=>$m):?><figure class="story-card s<?=$i+1?>"><img src="<?=e(safe_https_url($m['media_url']))?>" alt="<?=e($m['title'])?>" loading="lazy"><figcaption><b><?=e($m['title'])?></b><span><?=e($m['caption'])?></span></figcaption></figure><?php endforeach;?></div></div></section>

<section class="section impact-section">
  <div class="container">
    <div class="section-title"><span class="kicker">Inspiring voices</span><h2>Real human stories make the platform feel alive.</h2><p>Use donor, doctor and patient stories to create trust and motivate more people to participate.</p></div>
    <div class="impact-grid">
      <?php if($stories): foreach($stories as $story): ?>
        <article class="impact-card">
          <div class="impact-head">
            <img src="<?=e(safe_https_url($story['image_url']))?>" alt="<?=e($story['person_name'])?>">
            <div>
              <h3><?=e($story['person_name'])?></h3>
              <span><?=e($story['role_label'])?></span>
            </div>
          </div>
          <p>“<?=e($story['story_text'])?>”</p>
        </article>
      <?php endforeach; else: ?>
        <div class="panel empty">Add impact stories from the admin panel.</div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if($video):?><section class="section video-section"><div class="container video-grid"><div><span class="kicker">Awareness media</span><h2>Use video to make the mission clear.</h2><p>The embedded video URL is editable from the Admin → Website Content screen, so you can replace it with your university demo, awareness video or presentation recording.</p><div class="video-points"><span><i class="fa-solid fa-circle-check"></i> Admin-editable video URL</span><span><i class="fa-solid fa-circle-check"></i> Responsive widescreen layout</span><span><i class="fa-solid fa-circle-check"></i> No autoplay or hidden tracking scripts added by BloodNet</span></div></div><div class="video-frame"><?php $sv=safe_https_url($video);if($sv&&preg_match('/\.mp4($|\?)/i',$sv)):?><video controls playsinline preload="metadata" poster="<?=e(safe_https_url($media[0]['media_url']??''))?>"><source src="<?=e($sv)?>" type="video/mp4">Your browser does not support HTML5 video.</video><?php elseif($sv):?><iframe src="<?=e($sv)?>" title="Blood donation awareness video" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe><?php endif;?></div></div></section><?php endif;?>

<section class="section"><div class="container"><div class="section-title left"><span class="kicker">Urgent right now</span><h2>Active blood requests</h2><p>Essential emergency information is public; donor contact details remain private.</p></div><div class="requests-grid"><?php while($r=$active->fetch_assoc()):?><article class="request-card"><div class="request-top"><div class="blood-ball"><?=e($r['blood_group'])?></div><span class="priority <?=e($r['priority'])?>"><?=priority_icon($r['priority'])?> <?=e($r['priority'])?></span></div><h3><?=e($r['patient_name'])?></h3><p><?=e($r['reason'])?></p><div class="request-meta"><span><i class="fa-solid fa-hospital"></i> <?=e($r['hospital_name']?:$r['address'])?></span><span><i class="fa-solid fa-tint"></i> <?=$r['units']?> unit(s)</span><span><i class="fa-solid fa-hourglass-half"></i> <?=date('d M, h:i A',strtotime($r['needed_by']))?></span></div><a class="btn btn-ghost" href="request-view.php?id=<?=$r['id']?>">View case</a></article><?php endwhile;?></div></div></section>

<section class="section cta-section"><div class="container"><div class="cta"><div><span class="kicker">Ready to contribute?</span><h2>Join the network and help turn urgency into hope.</h2><p>Become a donor, register your hospital, or submit and track emergency requests through a professional dashboard.</p></div><div class="cta-actions"><a class="btn btn-primary btn-lg" href="register.php">Create account</a><a class="btn btn-dark btn-lg" href="login.php">Sign in dashboard</a></div></div></div></section>
<?php include 'includes/footer.php'; ?>
