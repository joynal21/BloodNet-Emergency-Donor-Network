<?php
require 'config/db.php';
require 'includes/functions.php';
require_role('admin');
$ok='';
$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'';

    if($action==='save_settings'){
        foreach(['hero_title','hero_subtitle','video_url','announcement'] as $k){
            $v=trim($_POST[$k]??'');
            if($k==='video_url' && $v && !safe_https_url($v)){
                $error='Video URL must use HTTPS.';
                continue;
            }
            $st=$conn->prepare('INSERT INTO site_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
            $st->bind_param('ss',$k,$v);
            $st->execute();
        }
        audit($conn,'Updated website content','site_settings',null,'Homepage settings');
        $ok='Homepage content updated.';
    }

    if($action==='add_media'){
        $title=trim($_POST['title']??'');
        $url=trim($_POST['media_url']??'');
        $type=$_POST['media_type']??'image';
        $caption=trim($_POST['caption']??'');
        if($title&&filter_var($url,FILTER_VALIDATE_URL)&&safe_https_url($url)){
            $st=$conn->prepare('INSERT INTO site_media(title,media_type,media_url,caption,is_active) VALUES(?,?,?,?,1)');
            $st->bind_param('ssss',$title,$type,$url,$caption);
            $st->execute();
            $ok='Media item added.';
        }else{$error='Please enter a valid title and HTTPS URL.';}
    }

    if($action==='toggle_media'){
        $mid=(int)($_POST['media_id']??0);
        $st=$conn->prepare('UPDATE site_media SET is_active=1-is_active WHERE id=?');
        $st->bind_param('i',$mid);
        $st->execute();
        $ok='Media visibility updated.';
    }

    if($action==='add_event'){
        $title=trim($_POST['event_title']??'');
        $date=trim($_POST['event_date']??'');
        $location=trim($_POST['location']??'');
        $audience=trim($_POST['audience']??'');
        $image=trim($_POST['image_url']??'');
        $description=trim($_POST['description']??'');
        if($title && $date && $location && filter_var($image,FILTER_VALIDATE_URL) && safe_https_url($image)){
            $st=$conn->prepare('INSERT INTO community_events(title,event_date,location,audience,image_url,description,is_active) VALUES(?,?,?,?,?,?,1)');
            $st->bind_param('ssssss',$title,$date,$location,$audience,$image,$description);
            $st->execute();
            $ok='Community event added.';
        }else{$error='Please fill all event fields and use an HTTPS image URL.';}
    }

    if($action==='toggle_event'){
        $id=(int)($_POST['event_id']??0);
        $st=$conn->prepare('UPDATE community_events SET is_active=1-is_active WHERE id=?');
        $st->bind_param('i',$id);
        $st->execute();
        $ok='Event visibility updated.';
    }

    if($action==='add_story'){
        $name=trim($_POST['person_name']??'');
        $role=trim($_POST['role_label']??'');
        $image=trim($_POST['story_image_url']??'');
        $text=trim($_POST['story_text']??'');
        if($name && $role && $text && filter_var($image,FILTER_VALIDATE_URL) && safe_https_url($image)){
            $st=$conn->prepare('INSERT INTO impact_stories(person_name,role_label,image_url,story_text,is_active) VALUES(?,?,?,?,1)');
            $st->bind_param('ssss',$name,$role,$image,$text);
            $st->execute();
            $ok='Impact story added.';
        }else{$error='Please complete all story fields and use an HTTPS image URL.';}
    }

    if($action==='toggle_story'){
        $id=(int)($_POST['story_id']??0);
        $st=$conn->prepare('UPDATE impact_stories SET is_active=1-is_active WHERE id=?');
        $st->bind_param('i',$id);
        $st->execute();
        $ok='Story visibility updated.';
    }
}

$media=$conn->query('SELECT * FROM site_media ORDER BY id DESC');
$eventsList=[]; if($res=$conn->query('SELECT * FROM community_events ORDER BY event_date ASC, id DESC')){ while($row=$res->fetch_assoc()) $eventsList[]=$row; }
$storiesList=[]; if($res=$conn->query('SELECT * FROM impact_stories ORDER BY id DESC')){ while($row=$res->fetch_assoc()) $storiesList[]=$row; }
$pageTitle='Website Content';
include 'includes/header.php';
?>
<section class="dashboard"><div class="container"><div class="dashboard-head"><div><span class="kicker">Dynamic CMS</span><h1>Website content control</h1><p>Change hero text, photos, events, stories and homepage presentation without editing PHP.</p></div><a class="btn btn-ghost" href="admin.php">← Admin dashboard</a></div>
<?php if($ok):?><div class="alert alert-success"><?=e($ok)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error"><?=e($error)?></div><?php endif;?>

<div class="dash-grid">
    <div class="panel"><h3>Homepage settings</h3><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="save_settings"><div class="form-group"><label>Hero title</label><input class="form-control" name="hero_title" value="<?=e(setting($conn,'hero_title','Emergency blood, coordinated beautifully.'))?>"></div><div class="form-group"><label>Hero subtitle</label><textarea class="form-control" rows="3" name="hero_subtitle"><?=e(setting($conn,'hero_subtitle','Connect verified donors, hospitals and urgent patients through a trackable rescue workflow.'))?></textarea></div><div class="form-group"><label>Homepage video embed URL</label><input class="form-control" name="video_url" value="<?=e(setting($conn,'video_url','https://videos.pexels.com/video-files/13149047/13149047-uhd_2160_3840_30fps.mp4'))?>"></div><div class="form-group"><label>Top announcement</label><input class="form-control" name="announcement" value="<?=e(setting($conn,'announcement','Emergency network active — verified donors & hospitals'))?>"></div><button class="btn btn-primary">Save dynamic content</button></form></div>
    <div class="panel"><h3>Add photo or video</h3><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="add_media"><div class="form-group"><label>Title</label><input class="form-control" name="title" required></div><div class="form-group"><label>Type</label><select class="form-control" name="media_type"><option value="image">Image</option><option value="video">Video</option></select></div><div class="form-group"><label>HTTPS media URL</label><input class="form-control" name="media_url" type="url" required></div><div class="form-group"><label>Caption</label><textarea class="form-control" name="caption" rows="3"></textarea></div><button class="btn btn-dark">Add media</button></form></div>
</div>

<div class="dash-grid" style="margin-top:20px">
    <div class="panel"><h3>Add event / blood camp</h3><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="add_event"><div class="form-group"><label>Event title</label><input class="form-control" name="event_title" required></div><div class="form-row"><div class="form-group"><label>Event date</label><input class="form-control" name="event_date" type="date" required></div><div class="form-group"><label>Audience</label><input class="form-control" name="audience" placeholder="Students / Public / Families" required></div></div><div class="form-group"><label>Location</label><input class="form-control" name="location" required></div><div class="form-group"><label>HTTPS image URL</label><input class="form-control" name="image_url" type="url" required></div><div class="form-group"><label>Description</label><textarea class="form-control" rows="3" name="description" required></textarea></div><button class="btn btn-primary">Add event</button></form></div>
    <div class="panel"><h3>Add inspiring story</h3><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="add_story"><div class="form-group"><label>Person name</label><input class="form-control" name="person_name" required></div><div class="form-group"><label>Role label</label><input class="form-control" name="role_label" placeholder="Donor / Doctor / Patient family" required></div><div class="form-group"><label>HTTPS image URL</label><input class="form-control" name="story_image_url" type="url" required></div><div class="form-group"><label>Short story / quote</label><textarea class="form-control" rows="4" name="story_text" required></textarea></div><button class="btn btn-dark">Add story</button></form></div>
</div>

<div class="panel" style="margin-top:20px"><h3>Media library</h3><div class="media-admin-grid"><?php while($m=$media->fetch_assoc()):?><div class="media-admin-card"><?php if($m['media_type']==='image'):?><img src="<?=e($m['media_url'])?>" alt="<?=e($m['title'])?>"><?php else:?><div class="video-placeholder"><i class="fa-solid fa-circle-play"></i> Video</div><?php endif;?><div><b><?=e($m['title'])?></b><span><?=e($m['caption'])?></span><form method="post" style="margin-top:10px"><?=csrf_field()?><input type="hidden" name="action" value="toggle_media"><input type="hidden" name="media_id" value="<?=$m['id']?>"><button class="btn btn-sm <?=$m['is_active']?'btn-ghost':'btn-primary'?>"><?=$m['is_active']?'Hide':'Show'?></button></form></div></div><?php endwhile;?></div></div>

<div class="panel" style="margin-top:20px"><h3>Event manager</h3><div class="history-list"><?php if($eventsList): foreach($eventsList as $e):?><div class="history-card"><div class="history-blood"><i class="fa-solid fa-calendar-days"></i></div><div><h3><?=e($e['title'])?></h3><p><?=date('d M Y',strtotime($e['event_date']))?> • <?=e($e['location'])?> • <?=e($e['audience'])?></p></div><div class="history-meta"><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="toggle_event"><input type="hidden" name="event_id" value="<?=$e['id']?>"><button class="btn btn-sm <?=$e['is_active']?'btn-ghost':'btn-primary'?>"><?=$e['is_active']?'Hide':'Show'?></button></form></div></div><?php endforeach; else:?><div class="empty">Import <b>upgrade_v4.sql</b> or add your first event above.</div><?php endif;?></div></div>

<div class="panel" style="margin-top:20px"><h3>Impact stories</h3><div class="history-list"><?php if($storiesList): foreach($storiesList as $s):?><div class="history-card"><div class="history-blood"><i class="fa-solid fa-heart-pulse"></i></div><div><h3><?=e($s['person_name'])?></h3><p><?=e($s['role_label'])?> • <?=e($s['story_text'])?></p></div><div class="history-meta"><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="toggle_story"><input type="hidden" name="story_id" value="<?=$s['id']?>"><button class="btn btn-sm <?=$s['is_active']?'btn-ghost':'btn-primary'?>"><?=$s['is_active']?'Hide':'Show'?></button></form></div></div><?php endforeach; else:?><div class="empty">Import <b>upgrade_v4.sql</b> or add your first inspiring story above.</div><?php endif;?></div></div>

</div></section>
<?php include 'includes/footer.php'; ?>
