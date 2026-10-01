<?php
function e($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function user(){ return $_SESSION['user'] ?? null; }
function logged_in(){ return !empty($_SESSION['user']); }
function is_role(...$roles){ return logged_in() && in_array($_SESSION['user']['role'], $roles, true); }
function require_login(){ if(!logged_in()){ header('Location: login.php'); exit; } }
function require_role(...$roles){ require_login(); if(!is_role(...$roles)){ header('Location: dashboard.php'); exit; } }
function flash($key,$value=null){ if($value!==null){$_SESSION['flash'][$key]=$value;return;} $v=$_SESSION['flash'][$key]??null; unset($_SESSION['flash'][$key]); return $v; }
function csrf_token(){ if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function verify_csrf(){ if($_SERVER['REQUEST_METHOD']==='POST' && (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf']??'',(string)$_POST['csrf']))){ http_response_code(419); die('Security token expired. Please go back and refresh the page.'); } }
function is_local_demo(){ $host=strtolower($_SERVER['HTTP_HOST']??''); $remote=$_SERVER['REMOTE_ADDR']??''; return str_contains($host,'localhost') || str_starts_with($host,'127.0.0.1') || $remote==='127.0.0.1' || $remote==='::1'; }
function blood_compatible_donors($recipient){
  $map=['O-'=>['O-'],'O+'=>['O-','O+'],'A-'=>['O-','A-'],'A+'=>['O-','O+','A-','A+'],'B-'=>['O-','B-'],'B+'=>['O-','O+','B-','B+'],'AB-'=>['O-','A-','B-','AB-'],'AB+'=>['O-','O+','A-','A+','B-','B+','AB-','AB+']];
  return $map[$recipient] ?? [$recipient];
}
function distance_km($lat1,$lon1,$lat2,$lon2){ $earth=6371; $dLat=deg2rad($lat2-$lat1); $dLon=deg2rad($lon2-$lon1); $a=sin($dLat/2)**2+cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2; return round($earth*2*atan2(sqrt($a),sqrt(1-$a)),1); }
function readiness_score($profile,$distance){ $score=45; if(($profile['verified']??0)==1)$score+=15; if(($profile['availability']??'')==='available')$score+=15; $score+=round(($profile['response_rate']??0)*0.15); $score+=max(0,10-min(10,(int)round($distance))); return min(100,(int)$score); }
function create_matches(mysqli $conn,int $requestId){
  $stmt=$conn->prepare('SELECT * FROM blood_requests WHERE id=?');$stmt->bind_param('i',$requestId);$stmt->execute();$req=$stmt->get_result()->fetch_assoc();if(!$req)return 0;
  $groups=blood_compatible_donors($req['blood_group']);$quoted="'".implode("','",array_map([$conn,'real_escape_string'],$groups))."'";
  $res=$conn->query("SELECT u.id,dp.* FROM donor_profiles dp JOIN users u ON u.id=dp.user_id WHERE dp.blood_group IN ($quoted) AND dp.emergency_opt_in=1 AND dp.availability='available'");$count=0;
  while($d=$res->fetch_assoc()){$dist=distance_km((float)$req['lat'],(float)$req['lng'],(float)$d['lat'],(float)$d['lng']);if($dist>max(3,(int)$req['broadcast_radius']))continue;$score=readiness_score($d,$dist);$ins=$conn->prepare("INSERT IGNORE INTO request_matches(request_id,donor_id,distance_km,readiness_score,match_status,notified_at) VALUES(?,?,?,?, 'notified', NOW())");$ins->bind_param('iidi',$requestId,$d['user_id'],$dist,$score);$ins->execute();if($ins->affected_rows){$n=$conn->prepare("INSERT INTO notifications(user_id,title,message,type,link) VALUES(?, 'Emergency blood request', ?, 'emergency', ?)");$msg=$req['blood_group'].' blood needed nearby for '.$req['patient_name'];$link='request-view.php?id='.$requestId;$n->bind_param('iss',$d['user_id'],$msg,$link);$n->execute();$count++;}}
  $st=$conn->prepare("UPDATE blood_requests SET status='matching' WHERE id=? AND status IN ('submitted','verified')");$st->bind_param('i',$requestId);$st->execute();return $count;
}
function status_label($s){ return ucwords(str_replace('_',' ',$s)); }
function priority_icon($p){ return ['critical'=>'⚡','urgent'=>'🚨','normal'=>'🩸'][$p]??'🩸'; }
function audit(mysqli $conn,$action,$type=null,$id=null,$details=null){$uid=user()['id']??null;$stmt=$conn->prepare('INSERT INTO audit_logs(user_id,action,entity_type,entity_id,details) VALUES(?,?,?,?,?)');$stmt->bind_param('issis',$uid,$action,$type,$id,$details);$stmt->execute();}
function case_event(mysqli $conn,int $requestId,string $event,string $note=''){ $uid=user()['id']??null; $st=$conn->prepare('INSERT INTO case_history(request_id,user_id,event_name,note) VALUES(?,?,?,?)');$st->bind_param('iiss',$requestId,$uid,$event,$note);$st->execute(); }
function setting(mysqli $conn,string $key,string $fallback=''){ $st=$conn->prepare('SELECT setting_value FROM site_settings WHERE setting_key=? LIMIT 1');$st->bind_param('s',$key);$st->execute();$r=$st->get_result()->fetch_assoc();return $r?$r['setting_value']:$fallback; }

function safe_https_url($url){ $url=trim((string)$url); if(!$url)return ''; $p=parse_url($url); if(!$p || ($p['scheme']??'')!=='https') return ''; return $url; }
